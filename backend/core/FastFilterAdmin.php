<?php

require_once('api/Okay.php');

class FastFilterAdmin extends Okay {
    
    public function fetch() {
        $fast_filter = new stdClass;
        /*Прием информации о страницу*/
        if($this->request->method('POST')) {
            $fast_filter->id = $this->request->post('id', 'integer');
            $fast_filter->url = trim($this->request->post('url'));
            $fast_filter->body = $this->request->post('body');
            
            /*Не допустить одинаковые URL разделов*/
            if(($sc = $this->fastfilters->get_fastfilter($fast_filter->url)) && $sc->id!=$fast_filter->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif(empty($fast_filter->body)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif(substr($fast_filter->url, -1) == '-' || substr($fast_filter->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /*Добавление/Обновление коментариев*/
                if(empty($fast_filter->id)) {
                    $fast_filter->id = $this->fastfilters->add_fastfilter($fast_filter);
                    $fast_filter = $this->fastfilters->get_fastfilter($fast_filter->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->fastfilters->update_fastfilter($fast_filter->id, $fast_filter);
                    $fast_filter = $this->fastfilters->get_fastfilter($fast_filter->id);
                    $this->design->assign('message_success', 'updated');
                }
            }
        }
		else {
            $id = $this->request->get('id', 'integer');
			
            if(!empty($id)) {
                $fast_filter = $this->fastfilters->get_fastfilter(intval($id));
            }
        }
        
        $this->design->assign('fast_filter', $fast_filter);
        return $this->design->fetch('fast_filter.tpl');
    }
    
}
