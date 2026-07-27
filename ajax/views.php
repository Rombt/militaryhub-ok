<?php
if (!empty($_SERVER['HTTP_USER_AGENT'])) {
    session_name(md5($_SERVER['HTTP_USER_AGENT']));
}
session_start();
require_once('../api/Okay.php');
define('IS_CLIENT', true);
$okay = new Okay();

$views = $_POST['views'];
$url = $_POST['link'];

/*Записываем в сессию изменение просмотров*/
if(!isset($_SESSION['views_ids'])) $_SESSION['views_ids'] = array();
if(!in_array($url, $_SESSION['views_ids'])) {
    if ($post = $okay->blog->get_post($url)) {
        $query = $okay->db->placehold("UPDATE __blog SET views = views + 1 WHERE url = ?", $post->url);
        $okay->db->query($query);
        $_SESSION['views_ids'][] = $post->url;
    }
    else echo -1; //пост не найден
}
else echo 0; //уже смотрели


