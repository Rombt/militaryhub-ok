<?php

require_once('api/Okay.php');

class MetaChangeAdmin extends Okay {
    
    public function fetch() {
        $meta_change = new stdClass;
        /*Прием информации о страницу*/
        if($this->request->method('POST')) {
            $meta_change->id = $this->request->post('id', 'integer');
            $meta_change->url = trim($this->request->post('url'));
            $meta_change->meta_title = $this->request->post('meta_title');
            $meta_change->meta_description = $this->request->post('meta_description');
			$meta_change->meta_h1 = $this->request->post('meta_h1');
            
            /*Не допустить одинаковые URL разделов*/
            if(($mc = $this->metachanges->get_metachange($meta_change->url)) && $mc->id!=$meta_change->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif(empty($meta_change->meta_title)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif(substr($meta_change->url, -1) == '-' || substr($meta_change->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /*Добавление/Обновление мета*/
                if(empty($meta_change->id)) {
                    $meta_change->id = $this->metachanges->add_metachange($meta_change);
                    $meta_change = $this->metachanges->get_metachange($meta_change->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->metachanges->update_metachange($meta_change->id, $meta_change);
                    $meta_change = $this->metachanges->get_metachange($meta_change->id);
                    $this->design->assign('message_success', 'updated');
                }
            }
        }
		else {
            $id = $this->request->get('id', 'integer');
			
            if(!empty($id)) {
                $meta_change = $this->metachanges->get_metachange(intval($id));
            }
        }
        
        $this->design->assign('meta_change', $meta_change);
        return $this->design->fetch('meta_change.tpl');
    }
    
}
