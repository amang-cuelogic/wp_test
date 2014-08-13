<?php
/**
 * Social Links for posts
 *
 * @package Omega
 * @subpackage Frontend
 * @since 1.01
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license **LICENSE**
 * @version 1.2
 */

if( !empty( $social_networks )  ) : ?>
    <div class="<?php echo implode(' ', $container_classes); ?>" data-os-animation="<?php echo $scroll_animation; ?>" data-os-animation-delay="<?php echo $scroll_animation_delay; ?>s">
        <?php if( !empty( $title ) ) : ?>
            <label>
                <?php echo $title; ?>
            </label>
        <?php endif; ?>
        <ul class="<?php echo implode(' ', $classes); ?>">
            <?php if( in_array( 'twitter', $social_networks ) ) : ?>
                <li>
                    <a href="<?php echo $network_links['twitter']; ?>" target="_blank" style="background-color:<?php echo $background_colour; ?>;" data-iconcolor="#00acee">
                        <i class="fa fa-twitter"></i>
                    </a>
                </li>
            <?php endif; ?>
            <?php if( in_array( 'google', $social_networks ) ) : ?>
                <li>
                    <a href="<?php echo $network_links['google']; ?>" target="_blank" style="background-color:<?php echo $background_colour; ?>;" data-iconcolor="#dd1812">
                        <i class="fa fa-google-plus"></i>
                    </a>
                </li>
            <?php endif; ?>
            <?php if( in_array( 'facebook', $social_networks ) ) : ?>
                <li>
                    <a href="<?php echo $network_links['facebook']; ?>" target="_blank" style="background-color:<?php echo $background_colour; ?>;" data-iconcolor="#3b5998">
                        <i class="fa fa-facebook"></i>
                    </a>
                </li>
            <?php endif; ?>
            <?php if( in_array( 'pinterest', $social_networks ) ) : ?>
                <li>
                    <a href="<?php echo $network_links['pinterest']; ?>" target="_blank" style="background-color:<?php echo $background_colour; ?>;" data-iconcolor="#C92228">
                        <i class="fa fa-pinterest"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
    </div><?php
endif;
