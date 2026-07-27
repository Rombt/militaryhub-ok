<?php

require_once('api/Okay.php');

class ProductsTypeAdmin extends Okay {
    
    public function fetch() {
        $item = new stdClass;
        /*Принимаем инофмрацию о бренде*/
        if ($this->request->method('post')) {
            $item->id = $this->request->post('id', 'integer');
            $item->name = $this->request->post('name');
            $item->visible = $this->request->post('visible', 'boolean');            
            $item->url = trim($this->request->post('url', 'string'));
            $item->meta_title = $this->request->post('meta_title');
            $item->meta_keywords = $this->request->post('meta_keywords');
            $item->meta_description = $this->request->post('meta_description');
            
            $item->url = preg_replace("/[\s]+/ui", '', $item->url);
            $item->url = strtolower(preg_replace("/[^0-9a-z]+/ui", '', $item->url));
            if (empty($item->url)) {
                $item->url = $this->translit_alpha($item->name);
            }
            
            // Не допустить одинаковые URL разделов.
            if(empty($item->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif(empty($item->url)) {
                $this->design->assign('message_error', 'empty_url');
            } else {
                if (empty($item->id)) {
                    $item->id = $this->pt->add_item($item);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->pt->update_item($item->id, $item);
                    $this->design->assign('message_success', 'updated');
                }
                
                // // Удаление изображения
                // if ($this->request->post('delete_image')) {
                //     $this->image->delete_image($item->id, 'image', 'pt', $this->config->original_pt_dir, $this->config->resized_pt_dir);
                // }
                // // Загрузка изображения
                // $image = $this->request->files('image');
                // if (!empty($image['name']) && ($filename = $this->image->upload_image($image['tmp_name'], $image['name'], $this->config->original_pt_dir))) {
                //     $this->image->delete_image($item->id, 'image', 'pt', $this->config->original_pt_dir, $this->config->resized_pt_dir);
                //     $this->pt->update_item($item->id, array('image'=>$filename));
                // }
                $item = $this->pt->get_item($item->id);
            }
        } else {
            $item->id = $this->request->get('id', 'integer');
            $item = $this->pt->get_item($item->id);
        }
        
        $this->design->assign('item', $item);
        return  $this->design->fetch('products_type.tpl');
    }
    
}
