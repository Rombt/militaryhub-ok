<?php

require_once('api/Okay.php');

class StoresAdmin extends Okay {

	public function fetch() {
        // Обработка действий
        if($this->request->method('post')) {
            // Действия с выбранными
            $ids = $this->request->post('check');
            
            if(is_array($ids)) {
                switch($this->request->post('action')) {
                    case 'disable': {
                        /*Выключить способ доставки*/
                        $this->stores->update_store($ids, array('enabled'=>0));
                        break;
                    }
                    case 'enable': {
                        /*Включить сопсоб доставки*/
                        $this->stores->update_store($ids, array('enabled'=>1));
                        break;
                    }
                }
            }
            
            // Сортировка
            $positions = $this->request->post('positions');
            $ids = array_keys($positions);
            sort($positions);
            foreach($positions as $i=>$position) {
                $this->stores->update_store($ids[$i], array('position'=>$position));
            }
        }
        
        // Отображение
        $stores = $this->stores->get_stores();
        $this->design->assign('stores', $stores);
        return $this->design->fetch('stores.tpl');
    }
}
