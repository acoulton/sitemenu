<?php
defined('SYSPATH') or die('No direct script access.');

class AndrewC_SiteMenu_Condition_Handler_Environment {
    
    public static function is ($action, $target_environment) {
        return Kohana::$environment == $target_environment;
    }
}