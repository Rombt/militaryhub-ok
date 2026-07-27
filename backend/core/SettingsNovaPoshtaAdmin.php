<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 24.04.2018
 * Time: 2:30
 */

require_once('api/Okay.php');

class SettingsNovaPoshtaAdmin extends Okay {
    public function fetch()
    {
        if ($this->request->method('POST')) {
            $this->settings->np_api_key = $this->request->post('np_api_key');
            if (!empty($novaposhta_city = $this->request->post('novaposhta_city')))
                $this->settings->novaposhta_city = $novaposhta_city;
            if (!empty($novaposhta_ware = $this->request->post('novaposhta_ware')))
                $this->settings->novaposhta_ware = $novaposhta_ware;
            $this->settings->novaposhta_deliveryType = $this->request->post('novaposhta_deliveryType');
            $this->settings->novaposhta_cargoType = $this->request->post('novaposhta_cargoType');
            $this->settings->novaposhta_TypeOfPayer = $this->request->post('novaposhta_TypeOfPayer');
            $this->settings->novaposhta_PaymentForm = $this->request->post('novaposhta_PaymentForm');

            if ($this->request->post('novaposhta_sender_update')) {

                $contragent = explode(' ', $this->settings->requisites_name);
                if ($this->settings->novaposhta_contragent == 'PrivatePerson') {
                    if(empty($contragent[0]) || empty($contragent[1]) || empty($contragent[2])) {
                        $this->design->assign('message_error', 'invalid_requisites_name');
                    } elseif (!$this->settings->novaposhta_city) {
                        $this->design->assign('message_error', 'empty_novaposhta_city');
                    } elseif (!$this->settings->requisites_phone) {
                        $this->design->assign('message_error', 'empty_requisites_phone');
                    } elseif (!$this->settings->requisites_email) {
                        $this->design->assign('message_error', 'empty_requisites_email');
                    } else {
                        // $novaposhta_sender = $this->settings->novaposhta_sender;
                        // $ContactPersonRef = '';
                        // if (!empty($novaposhta_sender)) {
                        //     $action = 'update';
                        //     // $json_sender = json_decode($novaposhta_sender, true);
                        //     $ContactPersonRef = $this->settings->novaposhta_sender_ref;
                        // }
                        // else
                            $action = 'save';
                        $json_sender = $this->novaposhta->contragent($action, [
                            'CityRef' => $this->settings->novaposhta_city,
                            'FirstName' => $contragent[1],
                            'MiddleName' => $contragent[2],
                            'LastName' => $contragent[0],
                            'Phone' => $this->settings->requisites_phone,
                            'Email' => $this->settings->requisites_email,
                            'CounterpartyType' => $this->settings->novaposhta_contragent,
                            'CounterpartyProperty' => 'Sender',
                        ]);
                        $json_sender = json_decode($json_sender, true);

                        $this->settings->novaposhta_sender = json_encode($json_sender['data']);
                        if (!empty($json_sender['data'])) {
                            $this->settings->novaposhta_sender_ref = $json_sender['data'][0]['Ref'];
                            $this->settings->novaposhta_contact = json_encode($json_sender['data'][0]['ContactPerson']['data']);
                            $this->settings->novaposhta_contact_ref = $json_sender['data'][0]['ContactPerson']['data'][0]['Ref'];

                      //       if (!empty($novaposhta_contact)) {
                      //           $action = 'update';
                      //           // $json_sender = json_decode($novaposhta_sender, true);
                      //           $ContactPersonRef = $this->settings->novaposhta_contact_ref;
                      //       }
                      //       else
                      //           $action = 'save';
                      //   	$json_contact = $this->novaposhta->contact_contragent($action, [
                      //           'Ref' => $ContactPersonRef,
		                    //     'CounterpartyRef' => $json_sender['data'][0]['Ref'],
		                    //     'FirstName' => $contragent[1],
	                     //        'MiddleName' => $contragent[2],
	                     //        'LastName' => $contragent[0],
		                    //     'Phone' => $this->settings->requisites_phone,
		                    // ]);
		                    // $json_contact = json_decode($json_contact, true);

		                    // $this->settings->novaposhta_contact = json_encode($json_contact['data'][0]);
		                    // if (!empty($json_contact['data']))
		                    // 	$this->settings->novaposhta_contact_ref = $json_contact['data'][0]['Ref'];
                        }
                    }
                }
            }

            if ($this->request->post('update')) {
                $this->settings->last_novaposhta_update_page = 0;
                $this->settings->last_novaposhta_update_date = '';
            }

            $this->design->assign('message_success', 'saved');
        }
        
        $novaposhta_address = $this->np->get_address($this->settings->novaposhta_city, $this->settings->novaposhta_ware, true);
        $this->design->assign('novaposhta_address', $novaposhta_address);
        $this->design->assign('novaposhta_city', $novaposhta_address->CityDescription);
        $this->design->assign('novaposhta_ware', $novaposhta_address->Description);

        $btr_languages = array();
        foreach ($this->languages->lang_list() as $label=>$l) {
            if (file_exists("backend/lang/".$label.".php")) {
                $btr_languages[$l->name] = $l->label;
            }
        }

        $currencies = $this->money->get_currencies();
        $this->design->assign('currencies', $currencies);

        $this->design->assign('btr_languages', $btr_languages);
        return $this->design->fetch('settings_novaposhta.tpl');
    }
}