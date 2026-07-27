<?php
if(!empty($_SERVER['HTTP_USER_AGENT'])){
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
require_once('../api/Okay.php');
define('IS_CLIENT', true);
$okay = new Okay();
if(isset($_POST['id']) && is_numeric($_POST['rating'])) {
    $post_id = intval(str_replace('post_', '', $_POST['id']));
    $rating = floatval($_POST['rating']);

    /*Записываем в сессию изменение рейтинга*/
    if(!isset($_SESSION['rating_post_ids'])) $_SESSION['rating_post_ids'] = array();
    if(!in_array($post_id, $_SESSION['rating_post_ids'])) {
        $query = $okay->db->placehold('SELECT rating, votes FROM __blog WHERE id = ? LIMIT 1',  $post_id);
        $okay->db->query($query);
        $post = $okay->db->result();
        /*Обновляем рейтинг товара*/
        if(!empty($post)) {
            $test = [$post->rating, $post->votes, $rating];
            print_r();
            $rate = ($post->rating * $post->votes + $rating) / ($post->votes + 1);
            $query = $okay->db->placehold("UPDATE __blog SET rating = ?, votes = votes + 1 WHERE id = ?", $rate, $post_id);
            $okay->db->query($query);
            $_SESSION['rating_post_ids'][] = $post_id; // вносим в список который уже проголосовали

            echo $rate;
        }
        else echo -1; //товар не найден
    }
    else echo 0; //уже голосовали
}
else echo -1; //неверные параметры