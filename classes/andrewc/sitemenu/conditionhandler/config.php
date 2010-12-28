<?php
defined('SYSPATH') or die('No direct script access.');

class AndrewC_SiteMenu_ConditionHandler_Config {

    public static function set($action, $path) {
        return Kohana::config($path);
    }

}