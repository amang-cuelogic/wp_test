<?php
/**
 * Shows a woocommerce account page
 *
 * @package Omega
 * @subpackage Frontend
 * @since 1.0
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license http://wiki.envato.com/support/legal-terms/licensing-terms/
 * @version 1.2
 */

global $woocommerce; ?>

<section class="section section-commerce <?php echo apply_filters( 'oxy_woocommerce_shop_classes', 10 );?>">
    <div class="container">
        <?php wc_print_notices(); ?>
        <div class="row element-normal-top element-normal-bottom">
            <div class="col-md-3">
                <?php
                if ( has_nav_menu( 'account' ) ) {
                   wp_nav_menu( array( 'theme_location' => 'account', 'menu_class' => 'nav nav-pills nav-stacked', 'depth' => 0 ) );
                }
                else {
                    _e( 'create an account menu in the admin options', 'omega-td' );
                } ?>
            </div>
            <div class="col-md-9">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
</section>
