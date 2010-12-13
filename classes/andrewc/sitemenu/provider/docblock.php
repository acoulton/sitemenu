<?php
defined('SYSPATH') or die('No direct script access.');

abstract class AndrewC_SiteMenu_Provider_DocBlock extends SiteMenu_Provider {
    
    public function compile() {
        // Get all controller classes
        // Get a new Reflection of the class
        // Only process non-abstract classes
        // Get all the actions
        // Get and parse the docblock comments
        // If there are @sitemenu tags, process and attach a menu item
    }
}