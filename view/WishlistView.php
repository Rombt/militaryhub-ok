<?php

require_once('View.php');

class WishlistView extends View {
    
    public function __construct() {
        parent::__construct();
    }

    /*Отображение списка избранного*/
    public function fetch() {
        $limit = 500;
        $id = $this->request->get('id', 'integer');
        
        if (!empty($_COOKIE['wished_products'])) {
            $products_ids = explode(',', $_COOKIE['wished_products']);
            $products_ids = array_reverse($products_ids);
        } else {
            $products_ids = array();
        }
        
        if ($this->request->get('action', 'string') == 'delete') {
            $key = array_search($id, $products_ids);
            if ($key !== false) {
                unset($products_ids[$key]);
            }

            if ($this->user->id) {
                $wp_ids = implode(',', $products_ids);
                if (!empty($wp_ids)) {
                    $this->wishlist->update($this->user->id, $wp_ids);
                } else {
                    $this->wishlist->clear($this->user->id);
                }
            }
        } elseif($id > 0) {
            array_push($products_ids, $id);
            $products_ids = array_unique($products_ids);
        }
        
        $products_ids = array_slice($products_ids, 0, $limit);
        $products_ids = array_reverse($products_ids);
        
        if (!count($products_ids)) {
            unset($_COOKIE['wished_products']);
            setcookie('wished_products', '', time()-3600, '/', $this->config->root_host, false, true);
        } else {
            setcookie('wished_products', implode(',', $products_ids), time()+30*24*3600, '/', $this->config->root_host, false, true);
        }
        
        $products = array();

        if (count($products_ids)) {
            foreach ($this->products->get_products(array('id'=>$products_ids, 'visible'=>1)) as $p) {
                $products[$p->id] = $p;
            }
            if (!empty($products)) {
                $this->products->tiny_products($products);
            }
        }
        
        // Содержимое списка избранного
        $this->design->assign('wished_products', $products);
        
        // Выводим шаблон
        return $this->design->fetch('wishlist.tpl');
    }
    
}
