<?php
/**
 * Show error messages
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! $errors ) return;
?>
<?php foreach ( $errors as $error ) : ?>
	<div class="alert alert-danger">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <?php echo wp_kses_post( $error ); ?>
    </div>
<?php endforeach; ?>
