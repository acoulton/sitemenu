<?php
defined('SYSPATH') or die('No direct script access.');

class AndrewC_SiteMenu_Condition_Handler_HTTP {

    public static function ip_class($action, $class) {
        /*
         * The Request IP must be valid, so if validation fails it's because it's
         * a private range address.
         */
        $is_public = Validate::ip(Request::$client_ip, false);
        switch ($class) {
            case 'public':
                return $is_public;
            case 'private':
                return ! $is_public;
        }
    }
}