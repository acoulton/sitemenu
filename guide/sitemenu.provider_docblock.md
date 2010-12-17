# SiteMenu_Provider_DocBlock
[SiteMenu_Provider_DocBlock] is a navigation provider that generates items based
on the docblock comments of actions in any controllers available to the application.

This is a fast way to build navigation for simple applications, and means that
site navigation can be maintained at the same time as controllers - a new action
can be immediately propagated into a relevant place within the navigation structure.
It also allows supporting modules to define their own controllers and easily include
in site navigation - for example a common module handling user account management,
registration and password change can be fully integrated into site navigation simply
by activating the module.

Docblock tags can include conditions that must be evaluated before including an item,
allowing for rapid differentiation of the menu structure for authenticated users, role
based access control etc.

[!!]The DocBlock provider is ideal for rapidly prototyping navigation schemes.
However, because the menu item structure is distributed throughout the application
and its modules, careful initial design of the site navigation structure and - for
module developers - [careful planning to avoid conflicts will be required](#planning-module-navigation).

## Defining Navigation Items

A simple navigation definition might look like this:

    class Controller_User extends Controller {

        /**
         * Allows the active user to change their password
         * @sitemenu My Account>Change Password
         * @sitemenu:condition auth|logged_in
         */
        public function action_change_password() {
          // Implementation
        }

        /**
         * Allows a user to register for an account
         * @sitemenu Register
         * @sitemenu:condition auth|not_logged_in
         */
        public function action_register() {
          // Implementation
        }
    }

### Tag values

#### @sitemenu
The [navigation path](basic_concepts#navigation-paths). The existence of this
tag will include the action in the navigation, with the route and other elements
configurable with further tags.

#### @sitemenu:route
The route that will be used to reverse-route the action and controller
to determine a URI for this navigation link

#### @sitemenu:condition
Defines a condition to be evaluated before including this menu item. This can
be used to customise menus based on environment, user authentication or authorisation
or other criteria. The tag value defines the [handler](sitemenu.condition_handlers) to use, method to call and an
optional string parameter to pass to the method.

~~~
    /**
     * @sitemenu:condition auth|logged_in
     */
    SiteMenu_Condition_Handler_Auth::logged_in($action, null);

    /**
     * @sitemenu:condition environment|is|development
     */
    SiteMenu_Condition_Handler_Environment::is($action,"development");
~~~

#### @sitemenu:list-attributes, @sitemenu::item-attributes, @sitemenu::link-attributes

Defines attributes to be set on any of the list, item or link elements when rendering
the navigation. The value should be a valid querystring.

    /**
     * @sitemenu:link-attributes title=My%Navigation&class=red
     */

### Inheritance and navigation

In the same way as the [userguide], this provider will work its way up the inheritance
tree for each method until it finds a docblock comment. As a result, if @sitemenu tags
are defined on an abstract parent controller, the action in the final controller
will inherit these tags, unless the final controller method has a docblock comment
of its own. There is no inheritance of individual tags - as soon as a child action
has a docblock it must define the @sitemenu tags in full to behave as expected.

## Configuration

Configuration information is stored within the main sitemenu config file :

    'provider' => array (
        'docblock' => array (

### Default tags

            /**
             * Default tags will be merged with the @sitemenu tags found on
             * individual action docblocks.
             */            
            'default_tags' => array(
                'route' => 'default',
            ),

### Excluding actions

            /**
             * Modules, controllers and actions can be excluded from processing
             * altogether by listing them here, ideal to suppress inclusion of
             * items from a third-party module or to allow a custom or config
             * file based provider to include them under a different navigation
             * structure.
             * !NB! This will only take effect once any cache is invalidated
             */
             'exclude_modules' => array(
                // Exclude everything in classes within the auth module
                'auth' => true,
                ),
             'exclude_actions' => array(
                // Exclude the whole Auth controller
                'auth/*' => true,
                // Exclude Controller_Admin::action_restart()
                'admin/restart' => true,
                ),

### Caching
To avoid recursing through all controllers on every request, the parsed docblock
structure can be cached. For performance and security reasons, the default is to
use a sitemenu-provider-docblock-cache config file under application/config. This
will allow all parsing to take place in the development environment and the production
environment then only reads from this file. Caching can be disabled for testing and to
allow changes to show up instantly in the development environment if required.

            'cached_parsing' => true,

## Planning module navigation

Module developers must take extra care to ensure that any navigation defined in
controllers within modules is carefully designed to fit with user applications.
In particular:

* Navigation paths should be consistently and sensibly defined
* Avoid making assumptions about application routes - in particular the presence
  of the default route. It may be appropriate to define a custom route or routes 
  - perhaps configurable at the application level - to reach module controllers.