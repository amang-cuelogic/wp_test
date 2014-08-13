<?php
/**
 * Simple Icon shortcode partial
 *
 * @package Omega
 * @subpackage Frontend
 * @since 1.01
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license **LICENSE**
 * @version 1.2
 */
?>
<li>
    <?php if( !empty( $fa_icon ) ) : ?>
        <i class="fa fa-li fa-<?php echo $fa_icon; ?>" style="color:<?php echo $icon_color; ?>;">
        </i>
    <?php endif; ?>
    <?php echo $title; ?>
</li>