<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 09.02.2022
 * Time: 09:29:56
 */

require_once('api/Okay.php');

class ReferralsAdmin extends Okay {
    
    public function fetch() {
        $filter = array();
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 20;

        // Обработка действий
        if ($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            if (is_array($ids) && count($ids)>0) {
                switch ($this->request->post('action')) {
                    case 'delete': {
                        foreach ($ids as $id) {
                            $this->ref->delete_item($id);
                        }
                        break;
                    }
                }
            }

            /*Создание купона*/
            if ($this->request->post("new_code")){
                $new_expire = $this->request->post('new_expire');
                $new_item = new stdClass();
                $new_item->id = $this->request->post('new_id', 'integer');
                $new_item->name = $this->request->post('new_name', 'string');
                $new_item->code = $this->request->post('new_code', 'string');
                if (!empty($new_expire)) {
                    $new_item->expire = date('Y-m-d', strtotime($new_expire));
                } else {
                    $new_item->expire = null;
                }
                $new_item->value = $this->request->post('new_value', 'float');
                $new_item->type = $this->request->post('new_type', 'string');
                $new_item->single = $this->request->post('new_single', 'float');

                // Не допустить одинаковые URL разделов.
                if (($a = $this->ref->get_item((string)$new_item->code)) && $a->id != $new_item->id) {
                    $this->design->assign('message_error', 'code_exists');
                } elseif (empty($new_item->code)) {
                    $this->design->assign('message_error', 'empty_code');
                } else {
                    $new_item->id = $this->ref->add_item($new_item);
                    $new_item = $this->ref->get_item($new_item->id);
                    $this->design->assign('message_success', 'added');
                }
            }
        }

        // Поиск
        $keyword = $this->request->get('keyword', 'string');
        if (!empty($keyword)) {
            $filter['keyword'] = $keyword;
            $this->design->assign('keyword', $keyword);
        }
        
        $items_count = $this->ref->count_items($filter);
        
        $pages_count = ceil($items_count/$filter['limit']);
        $filter['page'] = min($filter['page'], $pages_count);
        $this->design->assign('items_count', $items_count);
        $this->design->assign('pages_count', $pages_count);
        $this->design->assign('current_page', $filter['page']);

        $items = $this->ref->get_items($filter);
        $this->design->assign('items', $items);

        return $this->design->fetch('referrals.tpl');
    }
    
}
