<?php
/**
 * Mini-cart
 *
 * Contains the markup for the mini-cart, used by the cart widget
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;
?>

<?php do_action( 'woocommerce_before_mini_cart' ); ?>

<div class="mini-cart-overview">
    <a href="<?php echo WC()->cart->get_cart_url(); ?>" class="navbar-text">
        <i class="fa fa-shopping-cart"></i>
        <span><?php echo WC()->cart->cart_contents_count; ?></span>
        -
        <?php echo WC()->cart->get_cart_subtotal(); ?>
    </a>
</div>

<a class="mini-cart-underlay" onclick="jQuery('.mini-cart-container').removeClass('active');jQuery('.mini-cart-underlay').removeClass('cart-open');jQuery('body').removeClass('mini-cart-opened');" href="javascript:void(0);"></a>

<div id="mini-cart-container" class="mini-cart-container <?php echo oxy_get_option( 'pageslide_cart_swatch' ); ?>" style="top: 0; bottom: 0; position: fixed;">
    <a onclick="jQuery('.mini-cart-container').removeClass('active');jQuery('.mini-cart-underlay').removeClass('cart-open');jQuery('body').removeClass('mini-cart-opened');" href="javascript:void(0);" class="btn btn-primary btn-cart-sidebar block margin-bottom">Close Cart</a>

    <ul class="cart_list product_list_widget <?php echo $args['list_class']; ?>">

    	<?php if ( sizeof( WC()->cart->get_cart() ) > 0 ) : ?>

    		<?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :

    			$_product = $cart_item['data'];

    			// Only display if allowed
    			if ( ! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) || ! $_product->exists() || $cart_item['quantity'] == 0 )
    				continue;

    			// Get price
    			$product_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $_product->get_price_excluding_tax() : $_product->get_price_including_tax();

    			$product_price = apply_filters( 'woocommerce_cart_item_price_html', woocommerce_price( $product_price ), $cart_item, $cart_item_key );
    			?>

    			<li>
    				<div class="media">
                        <a class= "product-image pull-left" href="<?php echo get_permalink( $cart_item['product_id'] ); ?>">
        					<?php echo $_product->get_image(); ?>
                        </a>
                        <div class="media-body">
                            <h5 class="media-heading">
                                <a href="<?php echo get_permalink( $cart_item['product_id'] ); ?>">
        					       <?php echo apply_filters('woocommerce_widget_cart_product_title', $_product->get_title(), $_product ); ?>

        				        </a>
                            </h5>
                            <?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf( '<a href="%s" class="remove" title="%s">&times;</a>', esc_url( WC()->cart->get_remove_url( $cart_item_key ) ), __( 'Remove this item', 'woocommerce' ) ), $cart_item_key );  ?>
                            <p>
            				    <?php echo WC()->cart->get_item_data( $cart_item ); ?>
                            </p>
                            <p>
        				        <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity">' . sprintf( '%s &times; %s', $cart_item['quantity'], $product_price ) . '</span>', $cart_item, $cart_item_key ); ?>
                            </p>
                         </div>
                    </div>
    			</li>

    		<?php endforeach; ?>

    	<?php else : ?>

    		<li class="empty"><?php _e( 'No products in the cart.', 'woocommerce' ); ?></li>

    	<?php endif; ?>

    </ul><!-- end product list -->

    <?php if ( sizeof( WC()->cart->get_cart() ) > 0 ) : ?>

    	<div class="cart-actions">
            <p class="total overlay"><strong><?php _e( 'Subtotal', 'woocommerce' ); ?>:</strong> <?php echo WC()->cart->get_cart_subtotal(); ?></p>

        	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

        	<div class="buttons">
        		<a href="<?php echo WC()->cart->get_cart_url(); ?>" class="btn btn-success block"><?php _e( 'View Cart', 'woocommerce' ); ?><i class="fa fa-shopping-cart"></i></a>
        		<a href="<?php echo WC()->cart->get_checkout_url(); ?>" class="btn checkout btn-primary block"><?php _e( 'Checkout', 'woocommerce' ); ?><i class="fa fa-credit-card"></i></a>
        	</div>
        </div>

    <?php endif; ?>
</div>
<?php do_action( 'woocommerce_after_mini_cart' ); ?>
