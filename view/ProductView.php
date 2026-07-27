<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 09.02.2022
 * Time: 12:42:28
 */

require_once('View.php');

class ProductView extends View {

    /*Отображение товара*/
    public function fetch() {
        $product_url = $this->request->get('product_url', 'string');
        $variant_id = $this->request->get('variant', 'integer');
        
        if(empty($product_url)) {
            return false;
        }
        
        // Выбираем товар из базы
        $product = $this->products->get_product((string)$product_url);
        if(empty($product) || (!$product->visible && empty($_SESSION['admin']))) {
            return false;
        }
        
        //lastModify
        $this->setHeaderLastModify($product->last_modify);
        $product->images = $this->products->get_images(array('product_id'=>$product->id));
        $product->image = reset($product->images);
        foreach ($product->images as $image) {
            if (preg_match("~^https?://~", $image->filename)) {
                if($filename = $this->image->download_image($image->filename)) {
                    $image->filename = $filename;
                }
            }
        }

        $product->all_img = $this->products->get_images(array('product_id'=>$product->id));

        $variants = array();
        $product->sizes = array();
        $product->colors = array();
        $variants_positions = [];
        $colors_ids = [];
        $sizes_ids = [];
        foreach ($this->variants->get_variants(array('product_id'=>$product->id/*, 'sort' => 'position'*/)) as $v) {
            $variants[$v->id] = $v;
            $variants_positions[$v->position] = $v->id;
            
            $color_translit = $this->translit_alpha($v->color);
            $colors_ids[$color_translit] = $v->color;
            if (!in_array($color_translit, array_keys($product->colors))) {
                $v->color_translit = $color_translit;
                $product->colors[$color_translit] = $v;
            }
            
            $size_translit = $this->translit_alpha($v->size);
            $sizes_ids[$size_translit] = $v->size;
            if (!in_array($size_translit, array_keys($product->sizes[$color_translit]))) {
                $v->size_translit = $size_translit;
                $product->sizes[$color_translit][$size_translit] = $v;
            }
        }
        
        $images_ids = [];
        if ($variants_images = $this->variants->get_images(array('product_id' => $product->id, 'color' => $colors_ids, 'group_by' => 'variant'))) {
            foreach ($variants_images as $image) {
                foreach ($variants as &$variant) {
                    if (($variant->color == $variants[$image->variant_id]->color)/* && empty($variant->images)*/) {
                        if (!isset($variants[$image->variant_id]->images)) {
                            $variants[$image->variant_id]->images = [];
                        }
                        if (!in_array($image->id, $images_ids)) {
                            $images_ids[] = $image->id;
                            $variants[$image->variant_id]->images[] = $image;
                        }
                        if (empty($variant->image)) {
                            $variants[$image->variant_id]->image = $image;
                        }
                    }
                }
            }
        }

        $first_variant_id = $variants_positions[min(array_keys($variants_positions))];
        $variants[$first_variant_id]->images = $product->images;
        $variants[$first_variant_id]->image = reset($product->images);

        foreach ($variants as &$variant) {
            $color_id = array_search($variant->color, $colors_ids);
            if (!empty($variant->images) && $variant->color == $product->colors[$color_id]->color) {
                $product->colors[$color_id]->images = $variant->images;
                $product->colors[$color_id]->image = reset($product->colors[$color_id]->images);
            }
        }

        if (!empty($product->sizes)) {
            $i = 0;
            foreach ($product->sizes as $color => $sizes) {
                if (!isset($product->colors[$color]->count_in_stock)) {
                    $product->colors[$color]->count_in_stock = 0;
                }
                foreach ($sizes as $variant) {
                    if ($variant->stock != 0) {
                        if ($i === 0 && !isset($product->variant)) {
                            $product->variant = $variant;
                        }
                        $product->colors[$color]->count_in_stock++;
                    }
                }
                $i++;
            }
        }

        $product->variants = $variants;

        if (!empty($product->variant) && !empty($product->variant->images)) {
            $product->variant->image = reset($product->variant->images);
        }

        // Вариант по умолчанию
        if (($v_id = $this->request->get('variant', 'integer'))>0 && isset($variants[$v_id])) {
            $product->variant = $variants[$v_id];
        } elseif (!isset($product->variant)) {
            $product->variant = reset($variants);
        }    

        // Заказ в 1 клик
        if ($this->request->method('post') && $this->request->post('fastorder')) {
            $order              = new stdClass;
            $order->name        = $this->request->post('name');
            $order->email       = '';
            $order->phone       = preg_replace("~[^\d]~", "", $this->request->post('phone'));
            $order->ip          = $_SERVER['REMOTE_ADDR'];
            $order->status_id   = $this->settings->orders_status['fast'];
            $order->lang_id     = $this->languages->lang_id();

            $this->design->assign('order', $order);

            $captcha_code = $this->request->post('captcha_code', 'string');
            $variant_id = $this->request->post('variant_id', 'integer');

            if (!empty($variant_id)) {
                $variant = $this->variants->get_variant(intval($variant_id));
            } else {
                $variant = $product->variant;
            }

            if ($variant->stock == 0) {
                $order->status_id   = $this->settings->orders_status['preorder'];
            } else {
                $order->status_id   = $this->settings->orders_status['fast'];
            }

            if (!empty($this->user->id)) {
                $order->discount = $this->user->discount;
                $order->user_id = $this->user->id;
            }

            if ($this->settings->captcha_fastorder && !$this->validate->verify_captcha('captcha_fastorder', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif (!$this->validate->is_name($order->name, true)){
                $this->design->assign('error', 'empty_name');
            } elseif (!$this->validate->is_phone($order->phone, true)) {
                $this->design->assign('error', 'empty_phone');
            } elseif (empty($variant)) {
                $this->design->assign('error', 'empty_variant');
            } elseif ($order->id = $this->orders->add_order($order)) {
                // Добавляем заказ в базу
                $_SESSION['order_id'] = $order->id;

                // Добавляем товары к заказу
                $this->orders->add_purchase(array(
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $product->name,
                    'variant_name' => $variant->name,
                    'price' => $variant->price,
                    'amount' => 1,
                    'sku' => $variant->sku,
                    'units' => $variant->units,
                ));

                // Если использовали реферал, увеличим количество его использований
                if (!empty($_COOKIE['userRefererId'])) {
                    $referral_id = base64_decode($_COOKIE['userRefererId']);
                    if ($referral = $this->ref->get_item(intval($referral_id), array('valid' => 1))) {
                        $this->orders->update_order($order->id, array('referral_id' => intval($referral_id)));
                        if (($label_id = $this->settings->orders_labels['referral']) && $label = $this->orderlabels->get_label(intval($label_id))) {
                            $this->orderlabels->add_order_labels($order->id, (array)$label->id);
                        }
                        $this->ref->update_item($referral->id, array('usages' => ++$referral->usages));
                        if ($referral->single) {
                            setcookie('userRefererId', '', -1, '/', '', false, false);
                        }
                    } else {
                        setcookie('userRefererId', '', -1, '/', '', false, false);
                    }
                }

                // Отправляем письмо пользователю
                $this->notify->email_order_user($order->id);

                // Отправляем письмо администратору
                $this->notify->email_order_admin($order->id);

                // Отправляем sms пользователю
                $this->sms->order_user($order->id);

                // Перенаправляем на страницу заказа
                header('Location: ' . $this->config->root_url . '/' . $this->lang_link . 'order/' . $order->url);
                exit();
            } else {
                $this->design->assign('error', 'unknown error');
            }

            $this->design->assign('order_form_sent', 'fastorder');
        }

        $product_values_aliases = [];
        if ($product_values = $this->features_values->get_features_values(array('product_id'=>$product->id))) {
            foreach ($product_values as $pv) {
                if ($pv->feature_id == $this->settings->feature_id['category_rozetka']
                    || $pv->feature_id == $this->settings->feature_id['category_epicentrk']
                ) {
                    continue;
                }
                if (!isset($product->features[$pv->feature_id])) {
                    $product->features[$pv->feature_id] = $pv;
                }
                $product->features[$pv->feature_id]->values[] = $pv;

                if (in_array($pv->feature_id, $this->settings->feature_id)) {
                    $product_values_aliases[$pv->translit] = $pv->value;
                }
            }
        }
        $this->design->assign('pv_aliases', $product_values_aliases);
        
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
                $comment->object_id = $product->id;
                $comment->type      = 'product';
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
        
        // Связанные товары
        $related_ids = array();
        $related_products = array();
        foreach($this->products->get_related_products($product->id) as $p) {
            $related_ids[] = $p->related_id;
            $related_products[$p->related_id] = null;
        }
        if(!empty($related_ids)) {
            foreach($this->products->get_products(array('id'=>$related_ids,'limit' => count($related_ids),'visible'=>1, 'in_stock'=>1)) as $p) {
                $related_products[$p->id] = $p;
            }

            if (!empty($related_products)) {
                $this->products->tiny_products($related_products);
            }
            
            $this->design->assign('related_products', $related_products);
        }

        //Связянные статьи для товара
        $related_post = array();
        $related_post = $this->blog->get_related_products(array('product_id'=>$product->id));
        if(!empty($related_post)) {
            $filter_post['visible'] = 1;
            foreach ($related_post as $r_post) {
                $filter_post['id'][] = $r_post->post_id;
            }
            $posts = $this->blog->get_posts($filter_post);
            $this->design->assign('related_posts', $posts);
        }
        
        // Отзывы о товаре
        $comments = $this->comments->get_comments(array('has_parent'=>false, 'type'=>'product', 'object_id'=>$product->id, 'approved'=>1, 'ip'=>$_SERVER['REMOTE_ADDR']));
        $children = array();
        foreach ($this->comments->get_comments(array('has_parent'=>true, 'type'=>'product', 'object_id'=>$product->id, 'approved'=>1, 'ip'=>$_SERVER['REMOTE_ADDR'])) as $c) {
            $children[$c->parent_id][] = $c;
        }

        // И передаем его в шаблон
        $this->design->assign('product', $product);
        $this->design->assign('comments', $comments);
        $this->design->assign('children', $children);
        
        // Категория и бренд товара
        $brand = $this->brands->get_brand(intval($product->brand_id));
        if ($brand->visible) {
            $this->design->assign('brand', $brand);
        }
        $category = $this->categories->get_category((int)$product->main_category_id);
        if (!empty($category->attach_brand_id)) {
            $brands_attaches = $this->pt->get_items(array('url' => $category->attach_brand_id, 'brand_id' => $brand->id));
            $category->attach_brand = reset($brands_attaches);
        }
        $this->design->assign('category', $category);

        // Соседние товары
        if (!empty($category)) {
            $neighbors_products = $this->products->get_neighbors_products($category->id, $product->position);
            $this->design->assign('next_product', $neighbors_products['next']);
            $this->design->assign('prev_product', $neighbors_products['prev']);
        }
        
        // Добавление в историю просмотров товаров
        $max_visited_products = 100; // Максимальное число хранимых товаров в истории
        $expire = time()+60*60*24*30; // Время жизни - 30 дней
        if(!empty($_COOKIE['browsed_products'])) {
            $browsed_products = explode(',', $_COOKIE['browsed_products']);
            // Удалим текущий товар, если он был
            if(($exists = array_search($product->id, $browsed_products)) !== false) {
                unset($browsed_products[$exists]);
            }
        }
        // Добавим текущий товар
        $browsed_products[] = $product->id;
        $cookie_val = implode(',', array_slice($browsed_products, -$max_visited_products, $max_visited_products));
        setcookie("browsed_products", $cookie_val, $expire, "/");

        $default_products_seo_pattern = (object)$this->settings->default_products_seo_pattern;
        $parts = array(
            '{$brand}'    => ($this->design->get_var('brand') ? $this->design->get_var('brand')->name : ''),
            '{$product}'  => ($product->name ? $product->name : ''),
            '{$price}'    => ($product->variant->price != null ? $this->money->convert($product->variant->price, $this->currency->id, false).' '.$this->currency->sign : ''),
            '{$sitename}' => ($this->settings->site_name ? $this->settings->site_name : '')
        );
        
        //Автоматичекска генерация мета тегов и описания товара
        if (!empty($category)) {
            $parts['{$category}']    = ($category->name ? $category->name : '');
            $parts['{$category_h1}'] = ($category->name_h1 ? $category->name_h1 : '');
            foreach ($product->features as $feature) {
                if ($feature->auto_name_id) {
                    $parts['{$'.$feature->auto_name_id.'}'] = $feature->name;
                }
                if ($feature->auto_value_id) {
                    
                    if (count($feature->values) > 1) {
                        $value = array();
                        foreach ($feature->values as $fv) {
                            $value[] = $fv->value;
                        }
                        $value = implode(', ', $value);
                    } else {
                        $value = $feature->value;
                    }
                    
                    $parts['{$'.$feature->auto_value_id.'}'] = $value;
                }
            }

            if ($category->auto_meta_title) {
                $auto_meta_title = $category->auto_meta_title;
            } elseif ($default_products_seo_pattern->auto_meta_title) {
                $auto_meta_title = $default_products_seo_pattern->auto_meta_title;
            } else {
                $auto_meta_title = $product->meta_title;
            }

            if ($category->auto_meta_keywords) {
                $auto_meta_keywords = $category->auto_meta_keywords;
            } elseif ($default_products_seo_pattern->auto_meta_keywords) {
                $auto_meta_keywords = $default_products_seo_pattern->auto_meta_keywords;
            } else {
                $auto_meta_keywords = $product->meta_keywords;
            }

            if ($category->auto_meta_desc) {
                $auto_meta_description = $category->auto_meta_desc;
            } elseif ($default_products_seo_pattern->auto_meta_desc) {
                $auto_meta_description = $default_products_seo_pattern->auto_meta_desc;
            } else {
                $auto_meta_description = $product->meta_description;
            }

            if (!empty($category->auto_description) && empty($product->description)) {
                $product->description = strtr($category->auto_description, $parts);
                $product->description = preg_replace('/\{\$[^\$]*\}/', '', $product->description);
            } elseif (!empty($default_products_seo_pattern->auto_description) && empty($product->description)) {
                $product->description = strtr($default_products_seo_pattern->auto_description, $parts);
                $product->description = preg_replace('/\{\$[^\$]*\}/', '', $product->description);
            }
        } else {

            if ($default_products_seo_pattern->auto_meta_title) {
                $auto_meta_title = $default_products_seo_pattern->auto_meta_title;
            } else {
                $auto_meta_title = $product->meta_title;
            }

            if ($default_products_seo_pattern->auto_meta_keywords) {
                $auto_meta_keywords = $default_products_seo_pattern->auto_meta_keywords;
            } else {
                $auto_meta_keywords = $product->meta_keywords;
            }

            if ($default_products_seo_pattern->auto_meta_desc) {
                $auto_meta_description = $default_products_seo_pattern->auto_meta_desc;
            } else {
                $auto_meta_description = $product->meta_description;
            }

            if (!empty($default_products_seo_pattern->auto_description) && empty($product->description)) {
                $product->description = strtr($default_products_seo_pattern->auto_description, $parts);
                $product->description = preg_replace('/\{\$[^\$]*\}/', '', $product->description);
            }
        }

        $auto_meta_title = strtr($auto_meta_title, $parts);
        $auto_meta_keywords = strtr($auto_meta_keywords, $parts);
        $auto_meta_description = strtr($auto_meta_description, $parts);
        
        $auto_meta_title = preg_replace('/\{\$[^\$]*\}/', '', $auto_meta_title);
        $auto_meta_keywords = preg_replace('/\{\$[^\$]*\}/', '', $auto_meta_keywords);
        $auto_meta_description = preg_replace('/\{\$[^\$]*\}/', '', $auto_meta_description);
        
        $this->design->assign('meta_title', $auto_meta_title);
        $this->design->assign('meta_keywords', $auto_meta_keywords);
        $this->design->assign('meta_description', $auto_meta_description);
        
        return $this->design->fetch('product.tpl');
    }
}
