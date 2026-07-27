<?php

require_once('api/Okay.php');

class BrandAdmin extends Okay {
    
    public function fetch() {
        $brand = new stdClass;
        $brands_types = array();
        
        /*Принимаем инофмрацию о бренде*/
        if($this->request->method('post')) {
            $brand->id = $this->request->post('id', 'integer');
            $brand->name = $this->request->post('name');
            $brand->annotation = $this->request->post('annotation');
            $brand->description = $this->request->post('description');
            $brand->visible = $this->request->post('visible', 'boolean');
            $brand->rozetka_ban_check = $this->request->post('rozetka_ban_check');
            $brand->rozetka_exclude = $this->request->post('rozetka_exclude');

            $brand->url = trim($this->request->post('url', 'string'));
            $brand->meta_title = $this->request->post('meta_title');
            $brand->meta_keywords = $this->request->post('meta_keywords');
            $brand->meta_description = $this->request->post('meta_description');
            
            $brand->url = preg_replace("/[\s]+/ui", '', $brand->url);
            $brand->url = strtolower(preg_replace("/[^0-9a-z]+/ui", '', $brand->url));
            if (empty($brand->url)) {
                $brand->url = $this->translit_alpha($brand->name);
            }
            
            if ($this->request->post('brands_types')) {
                foreach ($this->request->post('brands_types') as $n => $bt) {
                    foreach ($bt as $i => $v) {
                        if (empty($brands_types[$i])) {
                            $brands_types[$i] = new stdClass;
                        }
                        $brands_types[$i]->$n = $v;
                    }
                }
            }

            // Не допустить одинаковые URL разделов.
            if (($c = $this->brands->get_brand($brand->url)) && $c->id!=$brand->id) {
                $this->design->assign('message_error', 'url_exists');
            } elseif(empty($brand->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif(empty($brand->url)) {
                $this->design->assign('message_error', 'empty_url');
            } else {
                /*Добавляем/обновляем бренд*/
                if(empty($brand->id)) {
                    $brand->id = $this->brands->add_brand($brand);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->brands->update_brand($brand->id, $brand);
                    $this->design->assign('message_success', 'updated');
                }

                if (is_array($brands_types)) {
                    $brands_types_ids = array();

                    foreach ($brands_types as $index => &$type) {
                        // Удалить изображение
                        if (!empty($_POST['delete_brands_types_image'][$index])) {
                            $this->db->query("SELECT image FROM __products_types WHERE id=?", $type->id);
                            $filename = $this->db->result('image');
                            if (!empty($filename)) {
                                @unlink($this->config->root_dir.'/'.$this->config->original_types_dir.$filename);
                                $this->pt->update_item($type->id, array('image' => null));
                            }
                        }
                        
                        // Загрузить изображение
                        if (!empty($_FILES['brands_types_image']['tmp_name'][$index]) && !empty($_FILES['brands_types_image']['name'][$index])) {
                            $attachment_tmp_name = $_FILES['brands_types_image']['tmp_name'][$index];
                            $attachment_name = $_FILES['brands_types_image']['name'][$index];
                            $type_url = $this->translit_alpha($type->name);
                            $type_image_name = $product->url . (!empty($type_url) ? ('_' . $type_url) : '') . '_' . $this->translit($attachment_name);
                            move_uploaded_file($attachment_tmp_name, $this->config->root_dir.'/'.$this->config->original_types_dir.$type_image_name);
                            $type->image = $type_image_name;
                        }
                        
                        if (!empty($type->id)) {
                            $this->pt->update_item($type->id, $type);
                        } else {
                            $type->brand_id = $brand->id;
                            $type->id = $this->pt->add_item($type);
                        }
                        $type = $this->pt->get_item(intval($type->id));
                        if (!empty($type->id)) {
                            $brands_types_ids[] = $type->id;
                        }
                    }
                    
                    // Удалить непереданные варианты
                    $current_brands_types = $this->pt->get_items(array('brand_id' => $brand->id));
                    foreach ($current_brands_types as $current_brands_type) {
                        if (!in_array($current_brands_type->id, $brands_types_ids)) {
                            $this->pt->delete_item($current_brands_type->id);
                        }
                    }
                    
                    // Отсортировать  варианты
                    asort($brands_types_ids);
                    $i = 0;
                    foreach ($brands_types_ids as $type_id) {
                        $this->pt->update_item($brands_types_ids[$i], array('position' => $type_id));
                        $i++;
                    }
                }
                
                // Удаление изображения
                if ($this->request->post('delete_image')) {
                    $this->image->delete_image($brand->id, 'image', 'brands', $this->config->original_brands_dir, $this->config->resized_brands_dir);
                }
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name']) && ($filename = $this->image->upload_image($image['tmp_name'], $image['name'], $this->config->original_brands_dir))) {
                    $this->image->delete_image($brand->id, 'image', 'brands', $this->config->original_brands_dir, $this->config->resized_brands_dir);
                    $this->brands->update_brand($brand->id, array('image'=>$filename));
                }
                $brand = $this->brands->get_brand($brand->id);
            }
        } else {
            $brand->id = $this->request->get('id', 'integer');
            $brand = $this->brands->get_brand($brand->id);

            if ($brand->id) {
                // Варианты товара
                $brands_types = $this->pt->get_items(array('brand_id' => $brand->id));
            }
        }

        // if (empty($brands_types)) {
        //     $brands_types = array(1);
        // }
        
        $this->design->assign('brands_types', $brands_types);

        $this->design->assign('brand', $brand);
        return  $this->design->fetch('brand.tpl');
    }
    
}
