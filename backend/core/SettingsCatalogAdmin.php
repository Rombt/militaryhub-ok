<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 17.02.2021
 * Time: 12:55:37
 */

require_once('api/Okay.php');

class SettingsCatalogAdmin extends Okay {

    private $allowed_image_extentions = array('png', 'gif', 'jpg', 'jpeg', 'ico');

    /*Настройки каталога*/
    public function fetch() {
        $managers = $this->managers->get_managers();
        $this->design->assign('managers', $managers);

        if ($this->request->method('POST')) {
            $this->settings->decimals_point = $this->request->post('decimals_point');
            $this->settings->thousands_separator = $this->request->post('thousands_separator');
            $this->settings->products_num = $this->request->post('products_num');
            $this->settings->max_order_amount = $this->request->post('max_order_amount');
            $this->settings->comparison_count = $this->request->post('comparison_count');
            $this->settings->update('units', $this->request->post('units'));
            $this->settings->posts_num = $this->request->post('posts_num');

            $this->settings->products_bonuses = min(max(0, $this->request->post('products_bonuses', 'integer')), 100);
            $this->settings->orders_total_price_bonuses = $this->request->post('orders_total_price_bonuses', 'float');
            $this->settings->orders_first_bonuses = $this->request->post('orders_first_bonuses', 'float');
            $this->settings->birthday_bonuses = $this->request->post('birthday_bonuses', 'float');
            $this->settings->birthday_bonuses_on = $this->request->post('birthday_bonuses_on', 'integer');
            $this->settings->birthday_bonuses_off = min($this->settings->birthday_bonuses_on + 1, $this->request->post('birthday_bonuses_off', 'integer'));
            $this->settings->users_bonuses_delete = max(1, $this->request->post('users_bonuses_delete', 'integer'));
            $this->settings->users_bonuses_delete_notify = max(1, $this->request->post('users_bonuses_delete_notify', 'integer'));
            $this->settings->max_bonuses_orders = min(max(0, $this->request->post('max_bonuses_orders', 'integer')), 100);
            $this->settings->restricted_bonuses_category = $this->request->post('restricted_bonuses_category', 'integer');
            if ($this->request->post('users_bonuses_on', 'integer')) {
                $this->settings->users_bonuses_on = $this->request->post('users_bonuses_on', 'integer');
            } else {
                $this->settings->users_bonuses_on = 0;
            }
            if ($this->request->post('cart_bonuses_on', 'integer')) {
                $this->settings->cart_bonuses_on = $this->request->post('cart_bonuses_on', 'integer');
            } else {
                $this->settings->cart_bonuses_on = 0;
            }

            $this->settings->user_referral_value = min(max(0, $this->request->post('user_referral_value', 'integer')), 100);
            $this->settings->user_referral_value_referrer = min(max(0, $this->request->post('user_referral_value_referrer', 'integer')), 100);
            $this->settings->user_referral_expire = max(0, $this->request->post('user_referral_expire', 'integer'));

            $this->settings->orders_status = $this->request->post('orders_status');
            $this->settings->orders_labels = $this->request->post('orders_labels');

            $this->settings->feature_id = $this->request->post('feature_id');
            $this->settings->registration_group = $this->request->post('registration_group', 'integer');
            $this->settings->registration_promo_group = $this->request->post('registration_promo_group', 'integer');
                        
            if ($this->request->post('is_preorder', 'integer')){
                $this->settings->is_preorder = $this->request->post('is_preorder', 'integer');
            } else {
                $this->settings->is_preorder = 0;
            }

            $this->settings->global_categories = $this->request->post('global_categories');

            // Водяной знак
            $clear_image_cache = false;

            if ($this->request->post('delete_watermark')) {
                $clear_image_cache = true;
                unlink($this->config->root_dir.$this->config->watermark_file);
                $this->config->watermark_file = '';
            }

            $watermark = $this->request->files('watermark_file', 'tmp_name');
            if (!empty($watermark) && in_array(pathinfo($this->request->files('watermark_file', 'name'), PATHINFO_EXTENSION), $this->allowed_image_extentions)) {
                $this->config->watermark_file = 'backend/files/watermark/watermark.png';
                if (@move_uploaded_file($watermark, $this->config->root_dir.$this->config->watermark_file)) {
                    $clear_image_cache = true;
                } else {
                    $this->design->assign('message_error', 'watermark_is_not_writable');
                }
            }

            if ($this->settings->watermark_offset_x != $this->request->post('watermark_offset_x')) {
                $this->settings->watermark_offset_x = $this->request->post('watermark_offset_x');
                $clear_image_cache = true;
            }
            if ($this->settings->watermark_offset_y != $this->request->post('watermark_offset_y')) {
                $this->settings->watermark_offset_y = $this->request->post('watermark_offset_y');
                $clear_image_cache = true;
            }

            if ($this->settings->image_quality != $this->request->post('image_quality')) {
                $this->settings->image_quality = $this->request->post('image_quality');
                $clear_image_cache = true;
            }


            // Удаление заресайзеных изображений
            if ($clear_image_cache) {
                $this->clear_files_dirs($this->config->resized_images_dir);

                $this->clear_files_dirs($this->config->resized_blog_dir);
                $this->clear_files_dirs($this->config->resized_brands_dir);
                $this->clear_files_dirs($this->config->resized_categories_dir);
            }
            $this->design->assign('message_success', 'saved');
            
        }

        $statuses = $this->orderstatus->get_status();
        $this->design->assign('statuses', $statuses);

        $labels = $this->orderlabels->get_labels();
        $this->design->assign('labels', $labels);

        $features = $this->features->get_features();
        $this->design->assign('features', $features);

        $groups = $this->users->get_groups();
        $this->design->assign('groups', $groups);

        $categories = $this->categories->get_categories_tree();
        $this->design->assign('categories', $categories);

        return $this->design->fetch('settings_catalog.tpl');
    }

    private function clear_files_dirs($dir = '') {
        if (empty($dir)) {
            return false;
        }
        if ($handle = opendir($dir)) {
            while(false !== ($file = readdir($handle))) {
                if ($file != "." && $file != ".." && $file != '.keep_folder' && $file != '.htaccess') {
                    @unlink($dir."/".$file);
                }
            }
            closedir($handle);
        }
    }

}
