<?php
defined('SYSPATH') or die('No direct script access.');

abstract class AndrewC_SiteMenu_Item {

    public $caption = null;
    public $route = null;
    public $directory = null;
    public $controller = null;
    public $action = null;
    public $params = null;
    public $item_attributes = array();
    protected $_sub_items = array();
    protected $_parent = array();
    protected $_site_menu = null;

    public function __construct($caption, $parent, $menu) {

    }

    public function __sleep() {
        // Set $route back to the route name
        // What about $_parent?
    }

    public function __wakeup() {
        // Set $route to the route object reference
    }

    public function route($route, $directory, $controller, $action, $params) {

    }

    public function get_attribute($tag) {
        
    }

    public function set_attribute($tag, $value) {

    }
    
}