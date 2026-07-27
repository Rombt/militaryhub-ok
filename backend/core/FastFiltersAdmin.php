<?php

require_once('api/Okay.php');

########################################
class FastFiltersAdmin extends Okay {
    
    public function fetch() {
        // Обработка действий
        if($this->request->method('post')) {
			
            // Действия с выбранными
            $ids = $this->request->post('check');
            if(is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'delete': {
                        /*Удалить страницу*/
                        foreach($ids as $id) {
                            if (!$this->fastfilters->delete_fastfilter($id)) {
                                $this->design->assign('message_error', 'url_system');
                            }
                        }
                        break;
                    }
                }
            }
        }
		
        // Отображение
        $fast_filters = $this->fastfilters->get_fastfilters();
      
        $this->design->assign('fast_filters', $fast_filters);
        return $this->design->fetch('fast_filters.tpl');
    }
    
}
