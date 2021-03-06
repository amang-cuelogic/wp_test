<?php
/**
 * Login form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

if (is_user_logged_in()) return;
?>
<form method="post" class="login" role="form" <?php if ( $hidden ) echo 'style="display:none;"'; ?>>
	<?php if ( $message ) echo wpautop( wptexturize( $message ) ); ?>
	<div class="row">
        <div class="col-md-3">

    		<label for="username"><?php _e( 'Username or email', 'woocommerce' ); ?> <span class="required">*</span></label>
    		<input type="text" class="input-text form-control margin-bottom" name="username" id="username" />

    		<label for="password"><?php _e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
    		<input class="input-text form-control margin-bottom" type="password" name="password" id="password" />

    		<?php $woocommerce->nonce_field('login', 'login') ?>
    		<input type="submit" class="button" name="login" value="<?php _e( 'Login', 'woocommerce' ); ?>" />
    		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
    		<a class="lost_password" href="<?php echo esc_url( wp_lostpassword_url( home_url() ) ); ?>"><?php _e( 'Lost Password?', 'woocommerce' ); ?></a>
    	</div>
    </div>
	<hr>
</form>