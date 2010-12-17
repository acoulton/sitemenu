<?php

defined('SYSPATH') or die('No direct script access.');

/**
 * SiteMenu_Provider_DocBlock generates navigation based on @sitemenu tags on the
 * controller actions.
 *
 *     class Controller_Account {
 *         /**
 *          * @sitemenu My Account>Change Password
 *          * @sitemenu:route default
 *          * @sitemenu:auth logged_in
 *          *\/
 *         public function action_password() {
 *         }
 *
 * @package SiteMenu
 * @category Provider
 */
abstract class AndrewC_SiteMenu_Provider_DocBlock extends SiteMenu_Provider {

    /**
     * Builds a set of navigation items, filtering for authentication as required
     * based on the docblock tags of the controller actions.
     */
    public function compile() {
        $config = Kohana::config('sitemenu.provider.docblock');
        // Attempt to get an index from cache
        // @todo: the actual caching!
        if ( ! ($controller_index = false)) {
            $controller_index = $this->_index_controllers();
            // Cache the index
        }
        
        // Get default tags
        $default_tags = Arr::get($config, 'default_tags',array());
        foreach ($controller_index as $action) {
            // Merge the default tags
            $tags = Arr::merge($default_tags, $action['sitemenu_tags']);

            // If there is an auth tag, check with the auth provider whether to include
            if (isset($tags['condition']) AND !$this->_check_action_auth($action)) {
                continue;
            }

            // Map the action to a navigation item
            $item = $this->_menu->get_item($tags[null], true);

            // Split the controller name to a directory and controller
            $controller = substr(strrchr($action['controller'], '_'),1);
            $directory = str_replace('_', DIRECTORY_SEPARATOR,
                            substr($action['controller'],11,0-strlen($controller)));            

            // Set the item route
            $item->route(Arr::get($tags,'route','default'), $directory,
                            $controller, $action['action'], array());

            // @todo: attribute tags - parse_str(string, array)
        }
        return true;
    }

    /**
     * Builds an index of controllers and actions with their respective @sitemenu tags -
     * the result of this is an array of items which can be cached according to the caching
     * strategy set in the SiteMenu_Provider_DocBlock configuration.
     * A number of functions from the userguide module are largely duplicated here as the userguide
     * may not be available.
     * @todo is there another way to provide userguide functions directly to this?
     * @see [Kohana_KoDoc_Method::__construct()]
     * @see [Kohana_KoDoc::parse()]
     */
    protected function _index_controllers() {
        $controller_index = array();
        // Get all controller classes
        // @todo: use configuration to ignore modules
        $controllers = SiteMenu::classes('classes/controller');

        $excluded_by_config = Kohana::config('sitemenu.provider.docblock.exclude_actions');
        
        foreach ($controllers as $controller_name) {
            $controller_name = strtolower($controller_name);

            // Ignore excluded controllers
            if (isset($excluded_by_config[$controller_name.'/*'])) {
                continue;
            }
            
            // Get a new Reflection of the class
            $controller = new ReflectionClass($controller_name);

            // Only process non-abstract classes
            if ($controller->isAbstract()) {
                continue;
            }

            // Get all the methods
            foreach ($controller->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $comment = "";
                $method_name = $method->name;
                
                // Ignore non-action methods
                if (substr($method_name, 0, 7) !== 'action_') {
                    continue;
                }
                
                $action_name = strtolower(substr($method->name, 7, strlen($method->name)));

                //Ignore actions excluded by config
                if (isset($excluded_by_config[$controller_name.'/'.$action_name])) {
                    continue;
                }

                // Find a method docblock, in the current class or an ancestor
                $defining_class = $controller;
                do {
                    if ($defining_class->hasMethod($method_name)
                            AND $comment = $defining_class->getMethod($method_name)->getDocComment()) {
                        // Found a description for this method
                        break;
                    }
                } while ($defining_class = $defining_class->getParentClass());
                
                // Parse the comment
                if (!$comment) {
                    continue;
                }

                // Normalize all new lines to \n
                $comment = str_replace(array("\r\n", "\n"), "\n", $comment);

                // Remove the phpdoc open/close tags and split
                $comment = array_slice(explode("\n", $comment), 1, -1);

                // Tag content
                $tags = array();

                foreach ($comment as $i => $line) {
                    // Remove all leading whitespace
                    $line = preg_replace('/^\s*\* ?/m', '', $line);

                    // Search this line for a tag
                    if (preg_match('/^@sitemenu:?(\S+)?(?:\s*(.+))?$/', $line, $matches)) {
                        $tags[$matches[1]] = isset($matches[2]) ? $matches[2] : '';
                    }
                }

                if (count($tags)) {
                    // Store in our index
                    $controller_index[] = array(
                        'controller' => $controller_name,
                        'action' => $action_name,
                        'sitemenu_tags' => $tags);
                }
            }
        }
        return $controller_index;
    }

    /**
     * Processes an authentication instruction using one of the auth drivers to see whether
     * this navigation item should be presented to the current user.
     * 
     * @param array $action_data The data for the action built by [SiteMenu::_index_controllers()]
     * @return boolean;
     */
    protected function _check_action_auth($action_data) {        
        return true;
    }

}