<?php
/**
 * Created by PhpStorm.
 * User: AndriiK
 * Date: 14.11.2019
 * Time: 14:25
 */

if (!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
require_once('../api/Okay.php');
define('IS_CLIENT', true);

class JustInAjax extends Okay {
	public function fetch() {
		if (!$this->request->method('post')) die;
		$suggestions = [];
		$type = $this->request->post('type');
		$city = $this->request->post('city');
		$ware = $this->request->post('ware');
		$keyword = $this->request->post('query');
		
		if ($type == 1) {
			$ta = 'city';
			$res = $this->ji->get_cities(array('keyword' => $keyword));
		} else if ($type == 2) {
			$ta = 'ware';
			$res = $this->ji->get_wares(array('city' => $city, 'keyword' => $keyword));
		}
		if (!empty($res)) {
			foreach ($res as $i => $r) {
				$suggestions[$i] = [];
				$suggestions[$i][$ta]['Ref'] = $r->Ref;
				$suggestions[$i][$ta]['Description'] = $r->Description;
			}
		}

		return $suggestions;
	}
}

$results = new JustInAjax();
if ($result = $results->fetch()) {
	header("Content-Type: application/json; charset=UTF-8");
	header("Cache-Control: must-revalidate");
	header("Pragma: no-cache");
	header("Expires: -1");
	print json_encode($result);
}
exit();