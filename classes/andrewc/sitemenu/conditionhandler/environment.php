<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * Base class for implementing Environment based menu conditions
 * @package SiteMenu
 * @category ConditionHandler
 */
class AndrewC_SiteMenu_ConditionHandler_Environment {
    
    public static function is ($action, $target_environment) {
        return Kohana::$environment == $target_environment;
    }
}