<?php

require_once('api/Okay.php');

class ReportStatsAdmin extends Okay {
    
    public function fetch() {
        $filter = array();
        $date_filter = $this->request->get('date_filter');
        if (!empty($date_filter)) {
            $filter['date_filter'] = $date_filter;
            $this->design->assign('date_filter', $date_filter);
        }

        /*Фильтр по датам*/
        $date_from = $this->request->get('date_from');
        $date_to = $this->request->get('date_to');
        $filter_check = $this->request->get('filter_check');
        
        if (!empty($date_from)) {
            $filter['date_from'] = date("Y-m-d 00:00:01",strtotime($date_from));
            $this->design->assign('date_from', $date_from);
        }
        
        if (!empty($date_to)) {
            $filter['date_to'] = date("Y-m-d 23:59:00",strtotime($date_to));
            $this->design->assign('date_to', $date_to);
        }
        $this->design->assign('filter_check', $filter_check);
        
        $status = $this->request->get('status', 'integer');
        if (!empty($status)) {
            $filter['status'] = $status;
            $this->design->assign('status', $status);
        }

        $group = $this->request->get('group', 'integer');
        if (!empty($group)) {
            $filter['group'] = $group;
            $this->design->assign('group', $group);
        }

        $user_filter = $this->request->get('user_filter');
        if (!empty($user_filter)) {
            $filter['user_filter'] = $user_filter;
            $this->design->assign('user_filter', $user_filter);
        }

        $referral_filter = $this->request->get('referral');
        if (!empty($referral_filter)) {
            $filter['referral_filter'] = $referral_filter;
            $this->design->assign('referral_filter', $referral_filter);
        }

        $paid_filter = $this->request->get('paid_filter');
        if (!empty($paid_filter)) {
            $filter['paid_filter'] = $paid_filter;
            $this->design->assign('paid_filter', $paid_filter);
        }

        $sort_prod = $this->request->get('sort_prod');
        if (!empty($sort_prod)) {
            $filter['sort_prod'] = $sort_prod;
            $this->design->assign('sort_prod',$sort_prod);
        } else {
            $sort_prod = 'price';
            $this->design->assign('sort_prod',$sort_prod);
        }
        
        $filter['page'] = max(1, $this->request->get('page', 'integer'));
        $filter['limit'] = 40;
        $cat_filter = $this->request->get('category_id','integer');
        $this->design->assign('category',$cat_filter );
        $temp_filter = $filter;
        unset($temp_filter['limit']);
        unset($temp_filter['page']);

        $stat_count = $this->reportstat->get_report_purchases_count($temp_filter);
        $this->design->assign('posts_count',$stat_count );
        
        if($this->request->get('page') == 'all') {
            $filter['limit'] = $stat_count;
        }
        
        $this->design->assign('pages_count', ceil($stat_count/$filter['limit']));
        $this->design->assign('current_page', $filter['page']);

        /*Выборка товаров для статистики*/
        $report_stat_purchases = $this->reportstat->get_report_purchases($filter);
        $cat = $this->categories->get_category($cat_filter);
        foreach ($report_stat_purchases as $id=>$r) {
            if (!empty($r->product_id)) {
                $tmp_cat = $this->categories->get_categories(array('product_id' => $r->product_id));
                $tmp_cat = reset($tmp_cat);

                if (!empty($cat_filter) && !in_array($cat_filter,(array)$tmp_cat->path[$cat->level_depth-1]->children)) {
                    unset($report_stat_purchases[$id]);
                } else {
                    $report_stat_purchases[$id]->category = $tmp_cat;
                }

            }
        }

        $all_status = $this->orderstatus->get_status();
        $this->design->assign("all_status", $all_status);
        $this->design->assign('report_stat_purchases', $report_stat_purchases);
        $this->design->assign('categories', $this->categories->get_categories_tree());
        $this->design->assign('groups', $this->users->get_groups());
        $this->design->assign('referrals', $this->ref->get_items());
        
        return $this->design->fetch('reportstats.tpl');
    }

}
