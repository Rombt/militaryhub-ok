<?php

require_once('api/Okay.php');

########################################
class MetaChangesAdmin extends Okay {
    
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
                            if (!$this->metachanges->delete_metachange($id)) {
                                $this->design->assign('message_error', 'url_system');
                            }
                        }
                        break;
                    }
                }
            }
        }
		
        // Отображение
        $meta_changes = $this->metachanges->get_metachanges();
      
        $this->design->assign('meta_changes', $meta_changes);
        return $this->design->fetch('meta_changes.tpl');
    }
    
}
