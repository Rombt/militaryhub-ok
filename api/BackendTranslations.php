<?php

require_once 'Okay.php';

#[\AllowDynamicProperties]
class BackendTranslations
{
    
    public function get_translation($var)
    {
        return $this->$var;
    }
}
