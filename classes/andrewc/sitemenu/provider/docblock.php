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
 * @package SiteMenu.Provider
 */
abstract class AndrewC_SiteMenu_Provider_DocBlock extends SiteMenu_Provider {

    /**
     * Builds a set of navigation items, filtering for authentication as required
     * based on the docblock tags of the controller actions.
     */
    public function compile() {
        // Attempt to get an index from cache
        // @todo: the actual caching!
        if (!($controller_index = false)) {
            $controller_index = $this->_index_controllers();
            // Cache the index
        }

        // Get default tags
        $default_tags = Kohana::config('sitemenu.provider.docblock.default_tags');
        foreach ($controller_index as $action) {
            // Merge the default tags
            $tags = Arr::merge($default_tags, $action['sitemenu_tags']);

            // If there is an auth tag, check with the auth provider whether to include
            if (isset($tags['auth']) AND !$this->_check_action_auth($action)) {
                continue;
            }

            // Map the action to a navigation item
            $item = $this->_menu->get_item($tags[null], true);

            // Split the controller name to a directory and controller
            $controller = substr(strrchr($action['controller'], '_'),1);
            $directory = str_replace('_', DIRECTORY_SEPARATOR,
                            substr($action['controller'],0,0-strlen($controller)));

            //@todo: Parsing of the params tag - should it be a querystring?

            // Set the item route
            $item->route(Arr::get($tags,'route','default'), $directory,
                            $controller, $action['action'],
                         Arr::get($tags,'params',array()));
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
        $controllers = SiteMenu::classes('classes/controller');
        // @todo: use configuration to get controllers to ignore?
        foreach ($controllers as $controller) {
            // Get a new Reflection of the class
            $controller = new ReflectionClass($controller);

            // Only process non-abstract classes
            if ($controller->isAbstract()) {
                continue;
            }

            // Get all the methods
            foreach ($controller->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // Ignore non-action methods
                if (substr($method->name, 7) !== 'action_') {
                    continue;
                }

                // Find a method docblock, in the current class or an ancestor
                $defining_class = $controller;
                do {
                    if ($defining_class->hasMethod($method)
                            AND $comment = $defining_class->getMethod($method)->getDocComment()) {
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
                    if (preg_match('/^@sitemenu(:\S+)?(?:\s*(.+))?$/', $line, $matches)) {
                        $tags[$matches[1]] = isset($matches[2]) ? $matches[2] : '';
                    }
                }

                // Store in our index
                $controller_index[] = array('controller' => strtolower($controller->name),
                    'action' => strtolower(substr($method->name, 7, strlen($method->name))),
                    'sitemenu_tags' => $tags);
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