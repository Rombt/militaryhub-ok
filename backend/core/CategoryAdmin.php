<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 12.08.2021
 * Time: 13:02:57
 */

require_once 'api/Okay.php';

class CategoryAdmin extends Okay
{
    public function fetch()
    {
        $category = new stdClass;

        /* Принимаем данные о категории */
        if ($this->request->method('post')) {

            $category->id = $this->request->post('id', 'integer');
            $category->parent_id = $this->request->post('parent_id', 'integer');
            $category->parent_menu_name = trim($this->request->post('parent_menu_name'));
            $category->name = trim($this->request->post('name'));
            $category->name_h1 = trim($this->request->post('name_h1'));
            $category->yandex_name = trim($this->request->post('yandex_name'));
            
            /* //!! rmbt 
             * FIX STRICT MODE
             * tinyint(1) fields must always be int
             */
            $category->visible = (int)$this->request->post('visible', 'boolean');
            $category->featured = (int)$this->request->post('featured', 'boolean');
            $category->attach_brand_id = $this->request->post('attach_brand_id', 'string');
            $category->url = trim($this->request->post('url', 'string'));
            $category->meta_title = trim($this->request->post('meta_title'));
            $category->meta_keywords = trim($this->request->post('meta_keywords'));
            $category->meta_description = trim($this->request->post('meta_description'));
            $category->rozetka_name = trim($this->request->post('rozetka_name'));

            /* //!! rmbt 
             * nullable int
             */
            $category->rozetka_category_value_id = $this->request->post('rozetka_category_value_id', 'integer');

            $category->rozetka_category_value_id = $category->rozetka_category_value_id !== null ? (int)$category->rozetka_category_value_id : null;

            /* //!! rmbt 
             * FIX STRICT MODE
             * tinyint(1)
             */
            $category->rozetka_exclude = (int)$this->request->post('rozetka_exclude', 'boolean');
            $category->google_name = trim($this->request->post('google_name'));
            $category->prom_category = serialize($this->request->post('prom'));
            $category->epicentrk_name = trim($this->request->post('epicentrk_name'));
            $category->annotation = $this->request->post('annotation');
            $category->description = $this->request->post('description');


            // Не допустить одинаковые URL разделов.
            if (($c = $this->categories->get_category($category->url))
                && $c->id != $category->id
            ) {
                $this->design->assign('message_error', 'url_exists');
            } elseif (empty($category->name)) {
                $this->design->assign('message_error', 'empty_name');
            } elseif (empty($category->url)) {
                $this->design->assign('message_error', 'empty_url');
            } elseif (substr($category->url, -1) == '-'
                || substr($category->url, 0, 1) == '-'
            ) {
                $this->design->assign('message_error', 'url_wrong');
            } else {
                /* Добавление/обновление категории */
                if (empty($category->id)) {
                    $category->id = $this->categories->add_category($category);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->categories->update_category($category->id, $category);
                    $this->design->assign('message_success', 'updated');
                }
                // Удаление изображения
                if ($this->request->post('delete_image')) {
                    $this->image->delete_image($category->id, 'image', 'categories', $this->config->original_categories_dir, $this->config->resized_categories_dir);
                }
                // Загрузка изображения
                $image = $this->request->files('image');
                if (!empty($image['name'])
                    && ($filename = $this->image->upload_image($image['tmp_name'], $image['name'], $this->config->original_categories_dir))
                ) {
                    $this->image->delete_image($category->id, 'image', 'categories', $this->config->original_categories_dir, $this->config->resized_categories_dir);
                    $this->categories->update_category($category->id, ['image' => $filename]);
                }
                $category = $this->categories->get_category(intval($category->id));
                $category->prom_category = unserialize($category->prom_category);
            }

        } else {
            $category->id = $this->request->get('id', 'integer');
            $category = $this->categories->get_category($category->id);
            $category->prom_category = unserialize($category->prom_category);
        }

        $rozetka_feature_value_valid = false;

        if (!empty($category->rozetka_category_value_id)) {
            $rozetka_feature_value = $this->features_values->get_feature_value(intval($category->rozetka_category_value_id));
            $rozetka_feature_value_valid = !empty($rozetka_feature_value) && !empty($rozetka_feature_value->rozetka_name);
        }

        $this->design->assign('rozetka_feature_value_valid', $rozetka_feature_value_valid);
        $products_types = $this->pt->get_items(['group_by' => 'url']);
        $this->design->assign('products_types', $products_types);

        /* Выборка дерева категорий */
        $categories = $this->categories->get_categories_tree();

        $this->design->assign('category', $category);
        $this->design->assign('categories', $categories);

        return $this->design->fetch('category.tpl');
    }
}
