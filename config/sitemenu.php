<?php
defined('SYSPATH') or die('No direct script access.');

return array(
    /*
     * An array of SiteMenu_Provider classes to ignore for navigation compilation -
     * keyed by lowercase class name with a boolean true to ignore
     */
    'ignore_providers' => array(
        //'sitemenu_provider_docblock' => true,
    ),
    /*
     * Settings for SiteMenu_Item
     */
    'item' => array(
        // Default setting of the item attributes
        'default_attributes' => array(
            'list' => array(
                'class'=>'sitemenu',
            ),
            'item' => array(
                
            ),
            'link' => array(
                'href' => '#',
            ),
        ),
    ),
    /*
     * Settings for specific SiteMenu_Provider classes
     */
    'provider' => array(
        /*
         * Settings for the docblock provider
         */
        'docblock' => array(
            // Default values for docblock tags when not provided
            'default_tags' => array(
                'route' => 'default',                
            ),
            // Index caching parameters
            'index_caching' => array(
                'key' => 'sitemenu_docblock_index',


            )

        ),
    )
);