<?php
defined('SYSPATH') or die('No direct script access.');

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