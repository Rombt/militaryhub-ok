<?php

require_once('api/Okay.php');

class ReferralsAdmin extends Okay {
    
    public function fetch() {

        $filter = array();
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 25;
        $this->design->assign('current_limit', $filter['limit']);

        // Обработка действий
        if ($this->request->method('post')) {

            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position) {
                $this->ref->update_item($ids[$i], array('position' => $position));
            }
            
            // Действия с выбранными
            $ids = $this->request->post('check');
            
            if (is_array($ids)) {
                switch ($this->request->post('action')) {
                    case 'disable': {
                        foreach ($ids as $id) {
                            $this->ref->update_item($id, array('visible' => 0));
                        }
                        break;
                    }
                    case 'enable': {
                        foreach ($ids as $id) {
                            $this->ref->update_item($id, array('visible' => 1));
                        }
                        break;
                    }
                    case 'delete': {
                        foreach($ids as $id) {
                            $this->ref->delete_item($id);
                        }
                        break;
                    }
                    case 'move_to_page': {
                        /*Переместить на страницу*/
                        $target_page = $this->request->post('target_page', 'integer');

                        // Сразу потом откроем эту страницу
                        $filter['page'] = $target_page;

                        // До какого бренда перемещать
                        $limit = $filter['limit']*($target_page-1);
                        if ($target_page > $this->request->get('page', 'integer')) {
                            $limit += count($ids)-1;
                        } else {
                            $ids = array_reverse($ids, true);
                        }
                        
                        $temp_filter = $filter;
                        $temp_filter['page'] = $limit+1;
                        $temp_filter['limit'] = 1;
                        $tmp = $this->ref->get_items($temp_filter);
                        $target_item = array_pop($tmp);
                        $target_position = $target_item->position;

                        if ($target_page > $this->request->get('page', 'integer') && !$target_position) {
                            $query = $this->db->placehold("SELECT distinct position AS target FROM __referrals ORDER BY position DESC LIMIT 1");
                            $this->db->query($query);
                            $target_position = $this->db->result('target');
                        }
                        
                        foreach ($ids as $id) {
                            $query = $this->db->placehold("SELECT position FROM __referrals WHERE id=? LIMIT 1", $id);
                            $this->db->query($query);
                            $initial_position = $this->db->result('position');

                            if ($target_position > $initial_position) {
                                $query = $this->db->placehold("	UPDATE __referrals set position=position-1 WHERE position>? AND position<=?", $initial_position, $target_position);
                            } else {
                                $query = $this->db->placehold("	UPDATE __referrals set position=position+1 WHERE position<? AND position>=?", $initial_position, $target_position);
                            }

                            $this->db->query($query);
                            $query = $this->db->placehold("UPDATE __referrals SET position = ? WHERE id = ?", $target_position, $id);
                            $this->db->query($query);
                        }
                        break;
                    }
                }
            }
        }

        $items_count = $this->ref->count_items($filter);
        // Показать все страницы сразу
        if ($this->request->get('page') == 'all') {
            $filter['limit'] = $items_count;
        }

        if ($filter['limit']>0) {
            $pages_count = ceil($items_count/$filter['limit']);
        } else {
            $pages_count = 0;
        }
        $filter['page'] = min($filter['page'], $pages_count);
        $this->design->assign('ref_count', $items_count);
        $this->design->assign('pages_count', $pages_count);
        $this->design->assign('current_page', $filter['page']);

        $items = $this->ref->get_items($filter);
        
        $this->design->assign('items', $items);
        return $this->body = $this->design->fetch('referrals.tpl');
    }
    
}
