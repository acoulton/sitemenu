<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * The SiteMenu Controller provides interactive actions for viewing, developing,
 * compiling and testing SiteMenus. It should likely be disabled in a public facing
 * application!
 * @package SiteMenu
 * @category Tools
 */
class AndrewC_Controller_SiteMenu extends Controller {
    
    /**
     * @sitemenu Test>Home>About
     * @sitemenu:route test
     * @sitemenu:auth driver=auth&query=logged_in     
     */
    public function action_test() {
        SiteMenu::instance()
          ->compile();

        echo "<PRE>";
        print_r(SiteMenu::instance());
        print_r(SiteMenu::instance()
                ->get_active_path());
        echo "</pre>";
        echo SiteMenu::instance()
        ->render();
    }
}