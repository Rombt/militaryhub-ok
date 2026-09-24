<?php

require_once('api/Okay.php');

class RozetkaCategoriesAdmin extends Okay {
    
    public function fetch() {

        $feature_id = $this->settings->feature_id['category_rozetka'];

        if ($feature_id) {
            if ($this->request->method('post')) {
                foreach ($this->request->post('categories') as $n => $ca) {
                    foreach ($ca as $i => $v) {
                        if (empty($categories[$i])) {
                            $categories[$i] = new stdClass;
                        }
                        $categories[$i]->$n = $v;
                    }
                }

                $categories_ids = array();
                foreach ($categories as $category) {
                    if ($category->id) {
                        $this->features_values->update_feature_value($category->id, $category);
                    // } else {
                    //     unset($category->id);
                    //     $category->id = $this->features_values->add_features_value($category);
                    }
                    $categories_ids[] = $category->id;
                }
            }

            $features_values = array();
            $features_values_filter = array('feature_id' => $feature_id);
            $feature_value_id = $this->request->get('feature_value_id', 'integer');
            if (!empty($feature_value_id)) {
                $features_values_filter['id'] = $feature_value_id;
            }
    
            if ($features_values_filter['limit'] = $this->request->get('limit', 'integer')) {
                $features_values_filter['limit'] = max(5, $features_values_filter['limit']);
                $features_values_filter['limit'] = min(100, $features_values_filter['limit']);
                $_SESSION['features_values_num_admin'] = $features_values_filter['limit'];
            } elseif (!empty($_SESSION['features_values_num_admin'])) {
                $features_values_filter['limit'] = $_SESSION['features_values_num_admin'];
            } else {
                $features_values_filter['limit'] = 25;
            }
            $this->design->assign('current_limit', $features_values_filter['limit']);
    
            $features_values_filter['page'] = max(1, $this->request->get('page', 'integer'));
    
            $feature_values_count = $this->features_values->get_features_values($features_values_filter, true);
    
            // Показать все страницы сразу
            if ($this->request->get('page') == 'all') {
                $features_values_filter['limit'] = $feature_values_count;
            }
    
            if ($features_values_filter['limit'] > 0) {
                $pages_count = ceil($feature_values_count/$features_values_filter['limit']);
            } else {
                $pages_count = 0;
            }

            $features_values_filter['page'] = min($features_values_filter['page'], $pages_count);
            $this->design->assign('feature_values_count', $feature_values_count);
            $this->design->assign('pages_count', $pages_count);
            $this->design->assign('current_page', $features_values_filter['page']);

            $features_values = $this->features_values->get_features_values($features_values_filter);
            $this->design->assign('features_values', $features_values);
        }

        return $this->design->fetch('rozetka_categories.tpl');
    }
    
}
