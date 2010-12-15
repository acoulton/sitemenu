<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * SiteMenu is the primary, singleton, class involved in providing the menu functionality
 * across the site. It is responsible for compiling a menu from SiteMenu_Provider classes,
 * holding an array of SiteMenu_Item classes (and providing path-based access to them), and 
 * reverse mapping request objects to a menu navigation path.
 * 
 * @package SiteMenu
 */
abstract class AndrewC_SiteMenu {

    /**
     * The root element of the SiteMenu tree - not usually rendered
     * @var SiteMenu_Item
     */
    protected $_root = null;
    protected $_reverse_lookup_map = array();

    /**
     * Returns a singleton of the SiteMenu classs
     * @staticvar string $instance The static instance
     * @return SiteMenu
     */
    public static function instance() {
        static $instance = null;
        if (!$instance) {
            $instance = new SiteMenu();
        }
        return $instance;
    }

    /**
     * Returns a list of classes under the given path, an almost direct implementation
     * of Kodoc::classes
     * @todo This should be a core Kohana::list_classes() method?
     * @param string $path The path to search
     * @param array $list An existing list_files array used when recursing into subdirectories
     * @return array The classes that were found
     */
    public static function classes($path = 'classes', $list = null) {

        if ($list === NULL) {
            if (DIRECTORY_SEPARATOR != "/") {
                $path = str_replace('/', DIRECTORY_SEPARATOR, $path);
            }
            $list = Kohana::list_files($path);
        }
        
        $classes = array();

        foreach ($list as $name => $path) {
            if (is_array($path)) {
                $classes += SiteMenu::classes($path);
            } else {
                // Ignore files that do not have the correct php extension
                if ( "." .strtolower(pathinfo($name, PATHINFO_EXTENSION)) != EXT) {
                    continue;
                }
                
                // Remove "classes/" and the extension
                $class = substr($name, 8, -(strlen(EXT)));

                // Convert slashes to underscores
                $class = str_replace(DIRECTORY_SEPARATOR, '_', strtolower($class));

                $classes[$class] = $class;
            }
        }

        return $classes;
    }

    /**
     * Creates the root node
     */
    public function __construct() {
        $this->_root = new SiteMenu_Item('Root', null, null, $this);
    }

    /**
     * Finds all the non-abstract SiteMenu_Provider classes and for each in turn
     * gets an instance and then calls its compile method.
     *
     *     $menu = SiteMenu::instance()
     *             ->compile();
     *
     * [!!] All non-abstract SiteMenu_Provider_* classes will be compiled, unless
     * excluded by the sitemenu.ignore_providers configuration setting
     * 
     * @return AndrewC_SiteMenu 
     */
    public function compile() {
        // Get all the available providers
        $providers = SiteMenu::classes('classes/sitemenu/provider');
        
        // Get providers to ignore
        $ignore_providers = Kohana::config('sitemenu.ignore_providers');

        foreach ($providers as $class) {
            // Disregard if blacklisted by configuration
            if (Arr::get($ignore_providers, $class)) {
                continue;
            }
            $provider = new ReflectionClass($class);

            // Disregard abstract classes
            if ($provider->isAbstract()) {
                continue;
            }

            // Execute the provider's compile method
            $provider = $provider->newInstance($this);
            $provider->compile();
        }

        // Build a reverse lookup array
        $this->_build_reverse_lookup();

        // Return self for chaining
        return $this;
    }

    /**
     * Iterates through all SiteMenu items and builds a reverse mapping table
     * used to identify the current active path from a given request object.
     *
     *     array(
     *       // First level is the route name
     *       'default' => array(
     *           // Second level is the directory
     *           null => array(
     *               // Third level is the controller name
     *               'welcome' => array(
     *                   // If the action is mapped to a path regardless of parameters
     *                   // the action level is a simple string result holding the
     *                   // navigation path.
     *                   'index' => "Home>About",
     *                   // If there is further mapping by parameter (eg navigation
     *                   // includes an item per page in a CMS) then the action level
     *                   // is an array.
     *                   'static' => array(
     *                       // The null key has special status, holding the names of the
     *                       // request parameters that are considered in the mapping
     *                       null => array('page', 'context'),
     *                       // Subsequent items contain the parameters built as a querystring
     *                       'page=help&context=view' => "Help>View")))));
     *
     */
    protected function _build_reverse_lookup() {
         $this->_root->build_reverse_lookup($this->_reverse_lookup_map);
    }

    /**
     * Uses the reverse path map to get the current path based on the main request
     * instance.
     *
     * If the SiteMenu map is as in the example of SiteMenu::_build_reverse_lookup and the
     * default route is used to route the uri /welcome/index this function will give:
     *
     *     class Controller_Welcome {
     *
     *         public function action_index() {
     *             $path = SiteMenu::instance()
     *                       ->get_active_path();
     *             print_r($path);
     *             // Home>About
     *         }
     *     }
     * @return string
     */
    public function get_active_path() {
        $request = Request::instance();

        $path_info = Arr::path($this->_reverse_lookup_map,
                        Route::name($request->route) . "."
                        . $request->directory . "."
                        . $request->controller . "."
                        . $request->action);

        if (!$path_info) {
            // No reverse nav path for this URI
            return false;
        } elseif (is_array($path_info)) {
            // The array stores the params we're interested in under the null key
            $params = Arr::get($path_info, null, array());
            // And the reverse nav path is then under this URI as a querystring
            $key = http_build_query(Arr::extract($request->param(null), $params));
            // If there's no key, it means we have a missing parameter
            if ( ! $key) {
                return false;
            }
            return Arr::get($path_info, $key, null);
        } else {
            // The nav path is the value
            return $path_info;
        }
    }

    /**
     * Renders the menu tree as a view, in a provided template layout
     */
    public function render() {
        
    }

    /**
     * Creates a new SiteMenu_Item, and attaches it to the tree - creating intermediate
     * items as required.
     *
     *     $menu = SiteMenu::instance();
     *     // This will create the Home and About items if required, then create
     *     // the "Contact Us" item and return it.
     *     $item = $menu->attach_item('Home>About>Contact Us');
     *
     * @param string $path The path to create
     * @return SiteMenu_Item
     */
    public function attach_item($path) {
        return $this->get_item($path, true);
    }

    /**
     * Retrieves a SiteMenu_Item by path, optionally creating the item and any
     * intermediate nodes if required.
     *
     * @param string $path The path to the item
     * @param boolean $force_create Whether to create the item if it doesn't exist
     * @return SiteMenu_Item
     */
    public function get_item($path, $force_create = false) {        
        // Split the path into components
        $components = SiteMenu::split_path($path);
        
        // Start from our root node
        $item = $this->_root;

        // Loop down the path, storing the node at each level
        foreach ($components as $path_component) {
            $item = $item->sub_item($path_component, $force_create);
            if ( ! $item) {
                break;
            }
        }

        return $item;
    }

    /**
     * Splits a navigation path into its individual components
     *
     *     $components = SiteMenu::split_path("Home>About>Us");
     *     print_r($components);
     *
     *     //array(
     *     //   [0] => Home,
     *     //   [1] => About,
     *     //   [2] => Us)
     * 
     * @param string $path
     * @return array
     */
    protected static function split_path($path) {
        // Return a path as an array of keys
        $components = explode('>', $path);
        return $components;
    }

}