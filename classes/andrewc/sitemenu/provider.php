<?php
defined('SYSPATH') or die('No direct script access.');

/**
 * SiteMenu_Provider classes generate navigation structure based on some data.
 * As standard, providers include:
 * 
 * Provider                     | Purpose                                                                
 * -----------------------------|------------------------------------------------------------------------
 * [SiteMenu_Provider_DocBlock] | Generates navigation based on @sitemenu PHPdoc comments in controllers
 *
 * [!!] All non-abstract SiteMenu_Provider classes will be compiled when the
 * menu is compiled unless excluded by the sitemenu.ignore_providers config setting
 * @package SiteMenu
 */
abstract class AndrewC_SiteMenu_Provider {
    /**
     * The SiteMenu we're providing for
     * @var SiteMenu
     */
    protected $_menu = null;

    /**
     * Instances a new Provider
     * @param SiteMenu $menu
     */
    public function __construct($menu) {
        $this->_menu = $menu;
    }
    
}