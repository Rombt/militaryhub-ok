<?php

$startTime = microtime(true);
chdir(dirname(__DIR__));
require_once('api/Okay.php');

class AutomaticContentTranslation extends Okay
{


    public function fetch($startTime)
    {
//        ini_set('display_errors', 'on');

        /*перевод значений свойств*/
        $this->googleTranslationApi->translation('FeaturesValues', $startTime);

        /*перевод свойств*/
        $this->googleTranslationApi->translation('Features', $startTime);

        /*перевод продуктов*/
        $this->googleTranslationApi->translationProducts($startTime);

    }
}

$results = new AutomaticContentTranslation();
$results->fetch($startTime);
exit();
