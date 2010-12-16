<?php
defined('SYSPATH') or die('No direct script access.');
/* @var $menu SiteMenu_Item */
/* @var $is_root boolean */
?>
<?php if (!$is_root): ?>
<li <?php echo HTML::attributes($menu->get_attribute('item'));?>>
    <a <?php echo HTML::attributes($menu->get_attribute('link'))?>><?php echo HTML::chars($menu->caption);?></a>    
<?php endif; ?>
<ul <?php echo HTML::attributes($menu->get_attribute('list'))?>>
    <?php foreach ($menu->sub_items() as $item): 
        /* @var $item SiteMenu_Item */?>
        <?php echo View::factory('sitemenu/topmenubar',
                                    array('menu'=>$item,
                                          'is_root'=>false));?>
    <?php endforeach; ?>
</ul>
<?php if ( ! $is_root): ?>
    </li>
<?php endif;?>