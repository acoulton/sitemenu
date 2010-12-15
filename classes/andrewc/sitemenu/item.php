<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * SiteMenu_Item objects hold details of individual elements of the site navigation
 * tree, including the mapping to routes and parameters and display-related attributes
 * on the navigation item HTML itself.
 * @package SiteMenu
 */
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
    protected $_path = null;

    /**
     * Sets up object references to parent and etc, and sets the caption
     * @param string $caption
     * @param SiteMenu_Item $parent
     * @param SiteMenu $menu
     */
    public function __construct($caption, $path, $parent, $menu) {
        $this->caption = $caption;
        $this->_parent = $parent;
        $this->_path = $path;
        $this->_site_menu = $menu;
    }

    public function __sleep() {
        // Set $route back to the route name
        // What about $_parent?
    }

    public function __wakeup() {
        // Set $route to the route object reference
    }


    /**
     * Sets the details of the route that will be used to generate a URI for this
     * menu item. Either a string or an existing route object can be passed.
     *
     * @param mixed $route
     * @param string $directory
     * @param string $controller
     * @param string $action
     * @param array $params
     * @return AndrewC_SiteMenu_Item
     */
    public function route($route, $directory, $controller, $action, $params) {
        if ($route instanceof Route) {
            $this->route = $route;
        } else {
            $this->route = Route::get($route);
        }
        $this->directory = $directory;
        $this->controller = $controller;
        $this->action = $action;
        $this->params = $params;
        return $this;
    }

    /**
     * Returns the value of a given HMTL attribute, or if called with a null returns
     * all attributes as an array.
     *
     * @param string $tag
     * @return mixed
     */
    public function get_attribute($tag = null) {
        if ( $tag === null) {
            return $this->item_attributes;
        } else {
            return Arr::get($this->item_attributes, $tag, null);
        }
    }

    /**
     * Sets an HTML attribute for this item, and returns the object for chaining.
     *
     *     SiteMenu::instance()
     *         ->get_item('Home')
     *         ->set_attribute('title','Our home page');
     *
     * @param string $tag
     * @param mixed $value
     * @return AndrewC_SiteMenu_Item
     */
    public function set_attribute($tag, $value) {
        $this->item_attributes[$tag] = $value;
        return $this;
    }

    /**
     * Queries for a sub-item within this node and, optionally, creates it if it doesn't exist already.
     *
     * @param string $caption
     * @param boolean $force_create
     * @return SiteMenu_Item
     */
    public function sub_item($caption, $force_create = false) {
        // Get an existing item if present
        $item = Arr::get($this->_sub_items, $caption, false);

        // Create a new item if required
        if (($item === false) AND $force_create) {
            if ($this->_path) {
                $path=$this->_path . ">" . $caption;
            } else {
                $path = $caption;
            }
            $item = new SiteMenu_Item($caption, $path, $this, $this->_site_menu);
            $this->_sub_items[$caption] = $item;
        }

        // Returns item or false if not found
        return $item;
    }

    /**
     * Fills an array (passed by reference) with information on how to reverse route
     * a particular navigation element. See [SiteMenu::_build_reverse_lookup()] for further
     * information on the structure of the reverse lookup map.
     * @todo Improve tests of existing mapping to detect conflicts
     * @param array $map
     */
    public function build_reverse_lookup(&$map) {
        // Build our own reverse lookup if we have a route
        if ($this->route) {
            // Get a reference to the place we need to store our info
            $lookup_map = &$map[Route::name($this->route)][$this->directory][$this->controller][$this->action];

            if (count($this->params)) {
                // Test to see if already exists
                if ($lookup_map && !is_array($lookup_map)) {
                    throw new InvalidArgumentException("Already mapped!");
                }

                // Map the keys and the params
                $lookup_map[null] = array_keys($this->params);
                $lookup_map[http_build_query($this->params)] = $this->_path;

            } else {
                // It's a straightforward storage of the path
                $lookup_map = $this->_path;
            }
        }

        // Recurse into children
        foreach ($this->_sub_items as $item) {
            $item->build_reverse_lookup($map);
        }
    }
    
}