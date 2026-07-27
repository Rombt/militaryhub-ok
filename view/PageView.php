<?php

require_once('View.php');

class PageView extends View {

    /*Отображение страниц сайта*/
    public function fetch() {
        $url = $this->request->get('page_url', 'string');
        $page = $this->pages->get_page($url);

        // Автозаполнение имени для формы комментария
        if(!empty($this->user)) {
            $this->design->assign('comment_name', $this->user->name);
            $this->design->assign('user_id', $this->user->id);
            $this->design->assign('comment_email', $this->user->email);
        }

        // Принимаем комментарий
        if ($this->request->method('post') && $this->request->post('comment')) {
            $comment = new stdClass;
            $comment->user_id = $this->request->post('user_id');
            $comment->name = $this->request->post('name');
            $comment->email = $this->request->post('email');
            $comment->text = $this->request->post('text');
            $captcha_code =  $this->request->post('captcha_code', 'string');

            // Передадим комментарий обратно в шаблон - при ошибке нужно будет заполнить форму
            $this->design->assign('comment_text', $comment->text);
            $this->design->assign('comment_name', $comment->name);
            $this->design->assign('comment_email', $comment->email);

            // Проверяем капчу и заполнение формы
            if ($this->settings->captcha_product && !$this->validate->verify_captcha('captcha_product', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif (!$this->validate->is_name($comment->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$this->validate->is_comment($comment->text, true)) {
                $this->design->assign('error', 'empty_comment');
            } elseif (!$this->validate->is_email($comment->email)) {
                $this->design->assign('error', 'empty_email');
            } else {
                // Создаем комментарий
                $comment->object_id = 20;
                $comment->type      = 'page';
                $comment->ip        = $_SERVER['REMOTE_ADDR'];
                $comment->lang_id   = $_SESSION['lang_id'];


                // Добавляем комментарий в базу
                $comment_id = $this->comments->add_comment($comment);
                $this->users->update_user_comm($comment->user_id);

                // Отправляем email
                $this->notify->email_comment_admin($comment_id);

                header('location: '.$_SERVER['REQUEST_URI'].'#comment_'.$comment_id);
            }
        }

        // Отзывы
        $comments = $this->comments->get_comments(array('has_parent'=>false, 'type'=>'page', 'object_id'=>20, 'approved'=>1, 'ip'=>$_SERVER['REMOTE_ADDR']));
        $children = array();
        foreach ($this->comments->get_comments(array('has_parent'=>true, 'type'=>'page', 'object_id'=>20, 'approved'=>1, 'ip'=>$_SERVER['REMOTE_ADDR'])) as $c) {
            $children[$c->parent_id][] = $c;
        }

        $this->design->assign('comments', $comments);
        $this->design->assign('children', $children);

        // Отображать скрытые страницы только админу
        if((empty($page) || (!$page->visible && empty($_SESSION['admin']))) && $url != '404') {
            return false;
        }
        
        //lastModify
        if ($page->url != '404') {
            $this->setHeaderLastModify($page->last_modify);
        }

        /*Выборка истории заказов клиента*/
        $orders = $this->orders->get_orders(array('user_id'=>$this->user->id));
        $all_status = $this->orderstatus->get_status();
        if($all_status) {
            $orders_status = array();
            foreach ($all_status as $status_item) {
                $orders_status[$status_item->id] = $status_item;
            }
        }
        $this->design->assign('orders_status', $orders_status);
        $this->design->assign('orders', $orders);
        
        $this->design->assign('page', $page);
        $this->design->assign('meta_title', $page->meta_title);
        $this->design->assign('meta_keywords', $page->meta_keywords);
        $this->design->assign('meta_description', $page->meta_description);
        
        return $this->design->fetch('page.tpl');
    }
    
}
