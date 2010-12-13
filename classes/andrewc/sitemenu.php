<?php
defined('SYSPATH') or die('No direct script access.');

abstract class AndrewC_SiteMenu {

    protected $_items = array();

    public static function instance() {
        
    }
    
    public function compile() {
        // Get all non-abstract providers
        // Instance each and call its compile() method
        // Build a reverse-lookup array
        /*
         * array(
         *    //route name
         *    'default' => array(
         *        //directory
         *        null => array(
         *          //controller
         *          'welcome' => array(
         *              //action - no params
         *              index => "Home>About",
         *              //or with params
         *              static => array(
         *                  //null key is param map
         *                  null => array('page','context'),
         *                  //now it's a keymap of imploded params
         *                  'help:view' => "Help>View"
         */
        $route = Request::instance()
            ->route;
    /* @var $route Route */
        Route::name(Request::instance()->route);
    }

    public function get_active_path() {
        $request = Request::instance();

        $path_info = Arr::path($this->_route_map,
                      Route::name($request->route)   $path, $default)
        $search_tree = $this->_route_map;
        $search_path = array(
            Route::name($request->route),
            $request->directory,
            $request->controller,
            $request->action
        );

        foreach ($search_path as $key) {
            $search_tree = Arr::get($search_tree,$key);
            if ( ! $search_tree) {
                break;
            }
        }

        if ( ! $search_tree) {
            return false;
        } elseif (is_array($search_tree)) {

        } else {
            return $search_tree;
        }
    }

    public function render() {        
        // Render the view with the menu tree
    }

    public function render_for_cache() {
        // Set each item to use its reverse lookup key for active status
        // Render the view
    }

    public function render_from_cache() {
        // Get the reverse lookup key from the current URI
        // Replace the active reverse lookup key
        // Replace all other reverse lookup keys
    }

    public function attach_item($path) {
        // Split the path into components
        // Work down the path, creating missing items as required
        // Return the newly created item
    }

    public function get_item($path, $force_create = false) {
        // Split the path into components
        // Check whether the item exists
        // Return item or false
    }

    protected function split_path($path) {
        // Return a path as an array of keys
    }
     
}