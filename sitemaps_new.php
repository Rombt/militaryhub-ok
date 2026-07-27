<?php

chdir(__DIR__);
require_once('api/Okay.php');
$ok = new Okay();
$ok->sitemaps->getSitemapMenu();
header("Content-type: text/xml; charset=UTF-8");

echo pack('CCC', 0xef, 0xbb, 0xbf);
echo $ok->sitemaps->getXML();
