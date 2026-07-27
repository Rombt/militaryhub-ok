<?php

require_once('api/Okay.php');

class StoreAdmin extends Okay {

	public function fetch() {
        $store = new stdClass;
        /*Принимаем данные о способе доставки*/
        if($this->request->method('post')) {
            $store->id               = $this->request->post('id', 'intgeger');
            $store->enabled          = $this->request->post('enabled', 'boolean');
            $store->name             = $this->request->post('name');
            $store->address          = $this->request->post('address');
            
            if(empty($store->name)) {
                $this->design->assign('message_error', 'empty_name');
            } else {
                /*Добавление/Обновление способа доставки*/
                if(empty($store->id)) {
                    $store->id = $this->stores->add_store($store);
                    $this->design->assign('message_success', 'added');
                } else {
                    $this->stores->update_store($store->id, $store);
                    $this->design->assign('message_success', 'updated');
                }

                $store = $this->stores->get_store($store->id);
            }
            
        } else {
            $store->id = $this->request->get('id', 'integer');
            if(!empty($store->id)) {
                $store = $this->stores->get_store($store->id);
            }
        }
    
        // Все способы оплаты
        $payment_methods = $this->payment->get_payment_methods();
        $this->design->assign('payment_methods', $payment_methods);
    
        $this->design->assign('store', $store);
    
          return $this->design->fetch('store.tpl');
    }
}