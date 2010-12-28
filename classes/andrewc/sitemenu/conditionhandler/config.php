<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * Base class for implementing Config based SiteMenu conditions
 * @package SiteMenu
 * @category ConditionHandler
 */
class AndrewC_SiteMenu_ConditionHandler_Config {

    public static function set($action, $path) {
        return Kohana::config($path);
    }

}