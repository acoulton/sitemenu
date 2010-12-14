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
         *                  //now the key is a querystring of params
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
                      Route::name($request->route) . "."
                      . $request->directory . "."
                      . $request->controller . "."
                      . $request->action);

        if ( ! $path_info) {
            // No reverse nav path for this URI
            return false;
        } elseif (is_array($path_info)) {
            // The array stores the params we're interested in under the null key
            $params = Arr::get($path_info, null, array());
            // And the reverse nav path is then under this URI as a querystring
            $key = http_build_query(Arr::extract($request->param(null), $params));
            return Arr::get($path_info, $key, null);
        } else {
            // The nav path is the value
            return $path_info;
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