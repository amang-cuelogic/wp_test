<?php
/**
 * Creates a boostrap menu
 *
 * @package Omega
 * @subpackage Admin
 * @since 0.1
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license **LICENSE**
 * @version 1.2
 */
?>
<div id="masthead" class="navbar navbar-static-top oxy-mega-menu <?php echo implode( ' ', $classes ); ?>" role="banner">
    <div class="<?php echo $container_class; ?>">

        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".main-navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <?php oxy_create_logo(); ?>
        </div>

        <nav class="collapse navbar-collapse main-navbar" role="navigation">

            <div class="menu-sidebar pull-right">
                <?php dynamic_sidebar( 'menu-bar' ); ?>
            </div>

            <?php
                wp_nav_menu( array(
                    'menu' => $slug,
                    'menu_class' => 'nav navbar-nav navbar-right',
                    'depth' => 4,
                    'walker' => new oxy_bootstrap_navwalker()
                ));
            ?>

        </nav>
    </div>
</div>