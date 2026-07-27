<?php

require_once('api/Okay.php');

class SeoCommentAdmin extends Okay {
    
    public function fetch() {
        $seo_comment = new stdClass;
        /*Прием информации о страницу*/
        if($this->request->method('POST')) {
            $seo_comment->id = $this->request->post('id', 'integer');
            $seo_comment->url = trim($this->request->post('url'));
            $seo_comment->body = $this->request->post('body');
            
            /*Не допустить одинаковые URL разделов*/
            if(($sc = $this->seocomments->get_seocomment($seo_comment->url)) && $sc->id!=$seo_comment->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif(empty($seo_comment->body)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif(substr($seo_comment->url, -1) == '-' || substr($seo_comment->url, 0, 1) == '-') {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /*Добавление/Обновление коментариев*/
                if(empty($seo_comment->id)) {
                    $seo_comment->id = $this->seocomments->add_seocomment($seo_comment);
                    $seo_comment = $this->seocomments->get_seocomment($seo_comment->id);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->seocomments->update_seocomment($seo_comment->id, $seo_comment);
                    $seo_comment = $this->seocomments->get_seocomment($seo_comment->id);
                    $this->design->assign('message_success', 'updated');
                }
            }
        }
		else {
            $id = $this->request->get('id', 'integer');
			
            if(!empty($id)) {
                $seo_comment = $this->seocomments->get_seocomment(intval($id));
            }
        }
        
        $this->design->assign('seo_comment', $seo_comment);
        return $this->design->fetch('seo_comment.tpl');
    }
    
}
