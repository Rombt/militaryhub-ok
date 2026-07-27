<?php

require_once('api/Okay.php');

########################################
class SeoCommentsAdmin extends Okay {
    
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
                            if (!$this->seocomments->delete_seocomment($id)) {
                                $this->design->assign('message_error', 'url_system');
                            }
                        }
                        break;
                    }
                }
            }
        }
		
        // Отображение
        $seo_comments = $this->seocomments->get_seocomments();
      
        $this->design->assign('seo_comments', $seo_comments);
        return $this->design->fetch('seo_comments.tpl');
    }
    
}
