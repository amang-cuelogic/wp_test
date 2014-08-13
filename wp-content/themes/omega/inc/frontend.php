<?php
/**
 * This is where all the themes frontend actions at
 *
 * @package Omega
 * @subpackage Frontend
 * @since 1.0
 *
 * @copyright (c) 2014 Oxygenna.com
 * @license http://wiki.envato.com/support/legal-terms/licensing-terms/
 * @version 1.2
 */

/******************************
 *
 * THEME SETUP
 *
 *****************************/

/**
 * Add theme support for automatic feed links. required by developer.
 *
 **/
add_theme_support( 'automatic-feed-links', 'editor-style' );

// add support for automatic feeds
add_theme_support( 'automatic-feed-links' ).

/*
 * add theme support for post formats.
 **/
add_theme_support( 'post-formats', array( 'image', 'gallery', 'video', 'link', 'audio', 'quote' ) );

// add support for featured images
add_theme_support( 'post-thumbnails' );
// add support for custom background
add_theme_support( 'custom-background' );

add_editor_style( OXY_THEME_URI . 'inc/assets/stylesheets/editor-style.css' );

/*
 * Set theme content width. Required
 *
 **/
if ( ! isset( $content_width ) )  {
    $content_width = 1170;
}


if( function_exists( 'vc_disable_frontend' ) ) {
    vc_disable_frontend( true );
}

function oxy_create_image_sizes() {
    if( function_exists( 'add_image_size' ) ) {
        add_image_size( 'square-image', 600, 600, true );
        add_image_size( 'portfolio-thumb', oxy_get_option('portfolio_item_width'), oxy_get_option('portfolio_item_height'), oxy_get_option('portfolio_item_crop') === 'on' );
    }
}
add_action( 'init', 'oxy_create_image_sizes');

function oxy_detect_user_agent() {
    global $oxy_is_iphone, $oxy_is_ipad, $oxy_is_android;
    $oxy_is_iphone  = stripos($_SERVER['HTTP_USER_AGENT'],"iPhone");
    $oxy_is_ipad    = stripos($_SERVER['HTTP_USER_AGENT'],"iPad");
    $oxy_is_android = stripos($_SERVER['HTTP_USER_AGENT'],"Android");
}

add_action( 'init', 'oxy_detect_user_agent');

function oxy_display_image_sizes( $sizes ) {
    $sizes['square-image'] = __('Square Image', 'omega-admin-td');
    $sizes['hex-image']    = __('Hexagon Image', 'omega-admin-td');
    $sizes['rect-image']   = __('Rectangle Image', 'omega-admin-td');

    $sizes['square-image-small'] = __('Square Small Image', 'omega-admin-td');
    $sizes['hex-image-small']    = __('Hexagon Small Image', 'omega-admin-td');
    $sizes['rect-image-small']   = __('Rectangle Small Image', 'omega-admin-td');
    return $sizes;
}
// hook for displaying the size in the media screen
add_filter( 'image_size_names_choose', 'oxy_display_image_sizes');


/******************************
 *
 * HEAD
 *
 *****************************/

/**
 * Apple device icons
 *
 * @return echos html for apple icons
 **/
function oxy_add_apple_icons( $option_name, $sizes = '' ) {
    $icon = oxy_get_option( $option_name );
    if( false !== $icon ) {
        $sizes = empty( $sizes ) ? '' : ' sizes="' . $sizes . '"';
        echo '<link rel="apple-touch-icon" href="' . $icon . '"' . $sizes  . ' />';
    }
}

/******************************
 *
 * HEADER
 *
 *****************************/

function oxy_create_logo() {
    if( function_exists( 'icl_get_home_url' ) ) {
        $home_link = icl_get_home_url();
    }
    else {
        $home_link = home_url();
    }

    $output = '<a href="' . $home_link . '" class="navbar-brand">';

    $url = oxy_get_option( 'logo_image' );
    if( '' !== $url ) {
        $output .= '<img src="';
        $output .= $url;
        $output .= '" alt="' . get_bloginfo('name');
        $output .= '"/>';
    }
    $output .= oxy_get_option( 'logo_text' );
    $output.= '</a>';

    echo $output;
}

function oxy_is_version( $version ) {
    global $wp_version;
    return version_compare( $wp_version, $version, '>=' );
}

// Register main menu
register_nav_menus( array(
    'primary' => __( 'Primary Navigation', 'omega-admin-td' ),
    'account' => __( 'Account Navigation', 'omega-admin-td' ),
));

/**
 * Gets a theme option
 *
 * @return theme option value or false if not set
 * @since 1.0
 **/
function oxy_get_option( $name ) {
    global $oxy_theme_options;
    if( isset( $oxy_theme_options[$name] ) ) {
        return $oxy_theme_options[$name];
    }
    else {
        return false;
    }
}


/**
 * Loads theme scripts
 *
 * @return void
 *
 **/
function oxy_load_scripts() {
    // load js
    wp_enqueue_script( THEME_SHORT.'-theme', OXY_THEME_URI . 'assets/js/theme.js', array( 'jquery', 'wp-mediaelement'), '1.0', true );

    wp_localize_script( THEME_SHORT.'-theme', 'scriptData', array(
        'navbarScrolledPoint'   => oxy_get_option('navbar_scrolled_point'),
        'navbarHeight'          => oxy_get_option('navbar_height'),
        'navbarScrolled'        => oxy_get_option('navbar_scrolled'),
        'scrollFinishedMessage' => __('No more items to load.', 'omega-td'),
    ));

    // load theme style
    wp_enqueue_style( THEME_SHORT.'-bootstrap', OXY_THEME_URI . 'assets/css/bootstrap.css', array(), false, 'all' );
    wp_enqueue_style( THEME_SHORT.'-theme', OXY_THEME_URI . 'assets/css/theme.css', array( 'wp-mediaelement', THEME_SHORT.'-bootstrap' ), false, 'all' );

    if( oxy_get_option( 'swatch_css_save' ) === 'file' ) {
        $swatch_files = get_option( THEME_SHORT . '-swatch-files', array() );
        foreach ( $swatch_files as $slug => $filename ) {
            wp_enqueue_style( THEME_SHORT.'-swatch-' . $slug , OXY_THEME_URI . 'assets/css/' . $filename, array( THEME_SHORT.'-theme' ), false, 'all' );
        }
    }

}
add_action( 'wp_enqueue_scripts', 'oxy_load_scripts' );

/*************** COMMENTS ***************************/

/** COMMENTS WALKER */
class OxyCommentWalker extends Walker_Comment {

    // init classwide variables
    var $tree_type = 'comment';
    var $db_fields = array( 'parent' => 'comment_parent', 'id' => 'comment_ID' );


    function display_element( $element, &$children_elements, $max_depth, $depth=0, $args, &$output ) {

        if ( !$element )
           return;

        $id_field = $this->db_fields['id'];
        $id = $element->$id_field;
        // we add li tags only in 0 depth comments
        if($depth==0){?>
            <li class="media media-comment"><?php
        }
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );

        // If we're at the max depth, and the current element still has children, loop over those and display them at this level
        // This is to prevent them being orphaned to the end of the list.
        if ( $max_depth <= $depth + 1 && isset( $children_elements[$id]) ) {
            foreach ( $children_elements[ $id ] as $child )
                $this->display_element( $child, $children_elements, $max_depth, $depth, $args, $output );

            unset( $children_elements[ $id ] );
        }
        // we add li tags only in 0 depth comments
        if($depth==0){?>
            </li><?php
        }

    }

    /** CONSTRUCTOR
     * You'll have to use this if you plan to get to the top of the comments list, as
     * start_lvl() only goes as high as 1 deep nested comments */
    function __construct() { ?>



    <?php }

    /** START_LVL
     * Starts the list before the CHILD elements are added. Unlike most of the walkers,
     * the start_lvl function means the start of a nested comment. It applies to the first
     * new level under the comments that are not replies. Also, it appear that, by default,
     * WordPress just echos the walk instead of passing it to &$output properly. Go figure.  */
    function start_lvl( &$output, $depth = 0, $args = array() ) {
        $GLOBALS['comment_depth'] = $depth + 1; ?>

                <!--<ul class="children">-->
    <?php }

    /** END_LVL
     * Ends the children list of after the elements are added. */
    function end_lvl( &$output, $depth = 0, $args = array() ) {
        $GLOBALS['comment_depth'] = $depth + 1; ?>

        <!--</ul>--><!-- /.children -->

    <?php }

    /** START_EL */
    function start_el( &$output, $comment, $depth=0, $args=array(), $id = 0 ) {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $GLOBALS['comment'] = $comment;
        $parent_class = ( empty( $args['has_children'] ) ? '' : 'parent' ); ?>
        <?php
        switch ( $comment->comment_type ) :
             case 'pingback':
             case 'trackback':
             // Display trackbacks differently than normal comments.
        ?>
        <div>
            <p><?php _e( 'Pingback:', 'omega-td' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'omega-td' ), '<span class="edit-link">', '</span>' ); ?></p>
        <?php
            break;
            default:
            // Proceed with normal comments.
            global $post;
            if($depth != 0){?>
                <div <?php comment_class( 'media media-comment' ); ?> ><?php
            }
        ?>
                    <div class="media-avatar pull-left">
                        <?php echo get_avatar( $comment, 48 ); ?>
                    </div>
                    <div class="media-body">
                        <div class="media-inner">
                            <div <?php comment_class(); ?> id="comment-<?php comment_ID() ?>">
                                <h4 class="media-heading clearfix bold">
                                    <?php comment_author_link(); ?>
                                    -
                                    <?php comment_date(); ?>
                                    <span class="comment-reply pull-right">
                                        <?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'reply', 'omega-td' ), 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
                                    </span>
                                </h4>
                                <?php comment_text(); ?>
                            </div>
                        </div>

    <?php endswitch; ?>

    <?php }

    function end_el(&$output, $comment, $depth = 0, $args = array() ) {
        switch ( $comment->comment_type ) :
            case 'pingback':
            case 'trackback':
             // Display trackbacks differently than normal comments.
        ?>
        </div>
        <?php
            break;
            default:
        ?>
                </div><!-- /media body -->
        <?php if($depth != 0){?>
            </div><?php
        }
        endswitch;
    }

    /** DESTRUCTOR
     * I just using this since we needed to use the constructor to reach the top
     * of the comments list, just seems to balance out :) */
    function __destruct() { ?>

    <!-- /#comment-list -->

    <?php }
}


/**
 * Customize comments form
 *
 *@return void
 *@since 1.0
 **/
function oxy_comment_form( $args = array(), $post_id = null ) {
    global $user_identity, $id;

    if ( null === $post_id )
        $post_id = $id;
    else
        $id = $post_id;

    $commenter = wp_get_current_commenter();

    $req = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );
    $fields =  array(
        'author' => '<div class="form-group form-icon-group"><input id="author" name="author" placeholder="' . __('Your name *', 'omega-td') . '" type="text" class="input-block-level form-control" value="' . esc_attr( $commenter['comment_author'] ) .  '"/><i class="fa fa-user"></i></div>',
        'email'  => '<div class="form-group form-icon-group"><input id="email" name="email" placeholder="' . __('Your email *', 'omega-td') . '" type="text" class="input-block-level form-control" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" /><i class="fa fa-envelope"></i></div>',
        'url'    => '',
    );

    $required_text = sprintf( ' ' . __('Required fields are marked %s', 'omega-td'), '<span class="required"><a>*</a></span>' );
    $defaults = array(
        'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
        'comment_field'        => '<div class="form-group form-icon-group"><textarea id="comment" name="comment" placeholder="' . __('Your message', 'omega-td') . '" class="input-block-level form-control" rows="3"></textarea><i class="fa fa-pencil"></i></div>',
        'must_log_in'          => '<p class="must-log-in">' .  sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.', 'omega-td' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
        'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>', 'omega-td' ), admin_url( 'profile.php' ), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
        'comment_notes_before' => '',
        'comment_notes_after'  => '',

        'id_form'              => 'commentform',
        'id_submit'            => 'submit',
        'title_reply'          => __( 'Join the conversation', 'omega-td' ),
        'title_reply_to'       => __( 'Leave a Reply', 'omega-td' ),
        'cancel_reply_link'    => __( 'Cancel reply', 'omega-td' ),
        'label_submit'         => __( 'Add comment', 'omega-td' ),
    );

    $args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );

    ?>
        <?php if ( comments_open() ) : ?>
            <?php do_action( 'comment_form_before' ); ?>
            <?php $extra_post_class  = oxy_get_option('blog_post_icons') == 'on'? 'post-showinfo':''; ?>
            <div class="comments-form <?php echo $extra_post_class ?>"  id="respond">
                <div class="comments-head">
                    <h3 id="reply-title"><?php comment_form_title( $args['title_reply'], $args['title_reply_to'] ); ?> <small id="cancel-comment-reply"><?php cancel_comment_reply_link('Cancel') ?></small></h3>
                    <?php if(oxy_get_option('blog_post_icons') == 'on'){ ?>
                        <div class="post-icon">
                            <i class="fa fa-comment"></i>
                        </div>
                    <?php } ?>
                </div>
                <?php if ( get_option( 'comment_registration' ) && !is_user_logged_in() ) : ?>
                    <?php echo $args['must_log_in']; ?>
                    <?php do_action( 'comment_form_must_log_in_after' ); ?>
                <?php else : ?>
                    <form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comments-body">
                        <fieldset>
                        <?php do_action( 'comment_form_top' ); ?>
                        <?php if ( is_user_logged_in() ) : ?>
                            <?php echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity ); ?>
                            <?php do_action( 'comment_form_logged_in_after', $commenter, $user_identity ); ?>
                        <?php else : ?>
                            <?php echo $args['comment_notes_before']; ?>
                            <?php
                            do_action( 'comment_form_before_fields' );
                            foreach( (array) $args['fields'] as $name => $field ) {
                                echo apply_filters( 'comment_form_field_'.$name, $field ) . "\n";
                            }
                            do_action( 'comment_form_after_fields' );
                            ?>
                        <?php endif; ?>
                        <?php echo apply_filters( 'comment_form_field_comment', $args['comment_field'] ); ?>
                        <?php echo $args['comment_notes_after']; ?>
                        <div class="form-group small-screen-center">
                            <div class="controls small-screen-center">
                                <button name="submit" type="submit" class="btn btn-primary btn-icon btn-icon-right" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>">
                                    <?php echo esc_attr( $args['label_submit'] ); ?>
                                    <span>
                                        <i class="fa fa-comment"></i>
                                    </span>
                                </button>
                                <?php comment_id_fields(); ?>
                            </div>
                        </div>


                        <?php do_action( 'comment_form', $post_id ); ?>
                        </fieldset>
                    </form>
                <?php endif; ?>

            </div><!-- #respond -->
            <?php do_action( 'comment_form_after' ); ?>
        <?php else : ?>
            <?php do_action( 'comment_form_comments_closed' ); ?>
        <?php endif; ?>
    <?php
}

/**
 * Enables threaded comments
 *
 *@return void
 *@since 1.0
 **/

function oxy_enable_threaded_comments(){
    if (!is_admin()) {
        if (is_singular() AND comments_open() AND (get_option('thread_comments') == 1))
            wp_enqueue_script('comment-reply');
    }
}
add_action('get_header', 'oxy_enable_threaded_comments');


/**
 * Adds rounded class to avatars
 *
 * @return modified css
 * @since 1.0
 **/
function oxy_change_avatar_css($class , $id , $size) {
    // if it's the admin bar , don't touch it.
    if( $size == 150 || $size == 48 || $size == 300 ) {
        // comment walker sends an object
        if( is_object( $id ) ) {
            $author_url = $id->user_id == 0 ? '#' : get_author_posts_url( $id->user_id );
        }
        else {
            $author_url = get_author_posts_url( $id );
        }
        // show avatar option
            $class = str_replace("class='avatar", "class='media-objects img-circle ", $class);
            return  '<a href="' . $author_url . '">' . $class . '</a>';
        //don't show anything
        return '';
    }
    return $class;

}
add_filter( 'get_avatar', 'oxy_change_avatar_css' ,10 , 5);


// override default tag cloud widget output
function oxy_custom_wp_tag_cloud_filter($content, $args) {
    $content = str_replace('<a' , '<li><a' , $content);
    $content = str_replace('</a>' , '</a></li>' , $content);
    return '<ul>'. $content . '</ul>';
}

add_filter('wp_tag_cloud','oxy_custom_wp_tag_cloud_filter', 10, 2);

/* function to return an icon depending the format of the post */

function oxy_post_icon( $post_id , $echo =true){
    $format = get_post_format( $post_id );
    switch ($format) {
        case 'image':
            $output = '<i class="fa fa-picture-o"></i>';
        break;
        case 'gallery':
            $output = '<i class="fa fa-camera"></i>';
        break;
        case 'link':
            $output = '<i class="fa fa-link"></i>';
        break;
        case 'quote':
            $output = '<i class="fa fa-quote-right"></i>';
        break;
        case 'audio':
            $output = '<i class="fa fa-headphones"></i>';
        break;
        case 'video':
            $output = '<i class="fa fa-play"></i>';
        break;
        default:
            $output = '<i class="fa fa-file-text"></i>';
        break;
    }
    if($echo)
        echo $output;
    else
        return $output;
}

// use option read more link
add_filter( 'the_content_more_link', 'oxy_filter_more_link', 10, 2 );

function oxy_filter_more_link( $more_link, $more_link_text ) {
    // remove #more
    $more_link = preg_replace( '|#more-[0-9]+|', '', $more_link );
    return str_replace( $more_link_text, oxy_get_option('blog_readmore'), $more_link );
}

function oxy_read_more_link( $echo = true){
    global $post;
    $output = "";
    if( isset( $post ) ) {
        $more_text = oxy_get_option('blog_readmore')? oxy_get_option('blog_readmore'): 'Read more';
        $more_text_classes = oxy_get_option('blog_readmore_style') == 'on'? 'btn btn-primary' : '';

        $output .= '<a href="' . get_permalink() . '" class="post-more-link ' . $more_text_classes . '">' . $more_text . '</a>';
    }

    if($echo == true){
        echo $output;
    }
    else{
        return $output;
    }
}

function oxy_get_content_shortcode( $post, $shortcode_name ) {
    $pattern = get_shortcode_regex();
    // look for an embeded shortcode in the post content
    if( preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches ) && array_key_exists( 2, $matches ) && in_array( $shortcode_name, $matches[2] ) ) {
        return $matches;
    }
}

function oxy_get_content_gallery( $post_content ) {
    $pattern = get_shortcode_regex();
    $gallery_ids = null;
    if( preg_match_all( '/'. $pattern .'/s', $post_content, $matches ) && array_key_exists( 2, $matches ) && in_array( 'gallery', $matches[2] ) ) {
        // gallery shortcode is being used

        // do we have some attribues?
        if( array_key_exists( 3, $matches ) ) {
            if( array_key_exists( 0, $matches[3] ) ) {
                $gallery_attrs = shortcode_parse_atts( $matches[3][0] );
                if( array_key_exists( 'ids', $gallery_attrs) ) {
                    $gallery_ids = explode( ',', $gallery_attrs['ids'] );
                    return $gallery_ids;
                }
            }
        }
    }
}


/**
 * Gets the url that a slide should link to
 *
 * @return url link
 * @since 1.2
 **/
function oxy_get_slide_link( $post ) {
    $link_type = get_post_meta( $post->ID, THEME_SHORT . '_link_type', true );
    switch( $link_type ) {
        case 'page':
            $id = get_post_meta( $post->ID, THEME_SHORT . '_page_link', true );
            return esc_url( get_permalink( $id ) );
        break;
        case 'post':
            $id = get_post_meta( $post->ID, THEME_SHORT . '_post_link', true );
            return esc_url( get_permalink( $id ) );
        break;
        case 'category':
            $slug = get_post_meta( $post->ID, THEME_SHORT . '_category_link', true );
            $cat = get_category_by_slug( $slug );
            return esc_url( get_category_link( $cat->term_id ) );
        break;
        case 'portfolio':
            $id = get_post_meta( $post->ID, THEME_SHORT . '_portfolio_link', true );
            return esc_url( get_permalink( $id ) );
        break;
        case 'url':
            return esc_url( get_post_meta( $post->ID, THEME_SHORT . '_url_link', true ) );
        break;
        case 'no-link':
            return '';
        break;
        case 'default':
        default:
            return esc_url(get_permalink( $post ));
        break;
    }
}

/* --------------- add a wrapper for the embeded videos -------------*/

function oxy_add_video_embed_note( $html, $url, $attr ) {
    $parsed_url = parse_url( $url );
    if( strpos( $parsed_url['host'], 'youtube.com' ) !== false ||
        strpos( $parsed_url['host'], 'vimeo.com' ) !== false ||
        strpos( $parsed_url['host'], 'wordpress.tv' ) !== false ) {
        return '<div class="video-wrapper feature-video">'. $html .'</div>';
    }
    else {
        return $html;
    }
}
add_filter('embed_oembed_html', 'oxy_add_video_embed_note', 10, 3);


function oxy_create_hero_section( $image = null, $title = null ) {
    $image = $image === null ? get_header_image() : $image;
?>
<section class="section section-alt">
    <div class="row">
        <div class="super-hero-unit">
            <figure>
                <img alt="some image" src="<?php echo $image; ?>">
            </figure>
        </div>
    </div>
</section>
<?php
}

/* Function for replacing title contents including underscores with span tags*/
function oxy_filter_title( $title ) {
    $title = preg_replace("/_(.*)_\b/",'<span class="light">$1</span>' , $title);
    return $title;
}

/* function for limiting the excerpt */
function oxy_limit_excerpt($string, $word_limit) {
    $words = explode(' ', $string, ($word_limit + 1));
    if( count($words) > $word_limit ) {
        array_pop($words);
    }
    return implode(' ', $words).'...';
}

function oxy_remove_readmore_span($content) {
    global $post;
    if( isset( $post ) ) {
        $content = str_replace('<span id="more-' . $post->ID . '"></span><!--noteaser-->', '', $content);
        $content = str_replace('<span id="more-' . $post->ID . '"></span>', '', $content);
    }
    return $content;
}
add_filter('the_content', 'oxy_remove_readmore_span');

function change_excerpt_more( $more ) {
    return '';
}
add_filter('excerpt_more', 'change_excerpt_more');

// add custom active class in menu items
function oxy_custom_active_item_class($classes = array(), $menu_item = false){
    if(in_array('current-menu-item', $menu_item->classes)){
        $classes[] = 'active';
    }
    return $classes;
}
add_filter( 'nav_menu_css_class', 'oxy_custom_active_item_class', 10, 2 );


// returns the hover color for each icon
function oxy_get_icon_color( $icon ) {
    $colour = '';
    switch( $icon ) {
        case 'adn':
            $colour = '#003871 ';
        break;
        case 'android':
            $colour = '#a4c639';
        break;
        case 'apple':
            $colour = '#333333';
        break;
        case 'bitbucket':
        case 'bitbucket-square':
            $colour = '#205081';
        break;
        case 'bitcoin':
        case 'btc':
            $colour = '#FF6600';
        break;
        case 'css3':
            $colour = '#404040';
        break;
        case 'dribbble':
            $colour = '#ea4c89';
        break;
        case 'dropbox':
            $colour = '#3d9ae8';
        break;
        case 'facebook':
        case 'facebook-square':
            $colour = '#3b5998';
        break;
        case 'flickr':
            $colour = '#0063dc';
        break;
        case 'foursquare':
            $colour = '#25a0ca';
        break;
        case 'github':
        case 'github-alt':
        case 'github-square':
            $colour = '#4183c4';
        break;
        case 'gittip':
            $colour = '#614C3E';
        break;
        case 'google-plus':
        case 'google-plus-square':
            $colour = '#E45135';
        break;
        case 'html5':
            $colour = '#ec6231';
        break;
        case 'instagram':
            $colour = '#634d40';
        break;
        case 'linkedin':
        case 'linkedin-square':
            $colour = '#5FB0D5';
        break;
        case 'linux':
            $colour = '#294170';
        break;
        case 'maxcdn':
            $colour = '#e47911';
        break;
        case 'pagelines':
            $colour = '#288EFE';
        break;
        case 'pinterest':
        case 'pinterest-square':
            $colour = '#910101';
        break;
        case 'renren':
            $colour = '#005eac';
        break;
        case 'skype':
            $colour = '#00aff0';
        break;
        case 'stack-exchange':
            $colour = '#3a6da6';
        break;
        case 'stack-overflow':
            $colour = '#ef8236';
        break;
        case 'trello':
            $colour = '#00c6d4';
        break;
        case 'tumblr':
        case 'tumblr-square':
            $colour = '#34526f';
        break;
        case 'twitter':
        case 'twitter-square':
            $colour = '#00acee';
        break;
        case 'vimeo-square':
            $colour = '#86c9ef';
        break;
        case 'vk':
            $colour = '#45668e';
        break;
        case 'weibo':
            $colour = '#e64141';
        break;
        case 'windows':
            $colour = '#00CCFF';
        break;
        case 'xing':
        case 'xing-square':
            $colour = '#126567';
        break;
        case 'youtube':
        case 'youtube-play':
        case 'youtube-square':
            $colour = '#c4302b';
        break;
        default:
            $colour = '#3b9999';
        break;

    }

    return apply_filters( 'oxy_social_icon_colour', $colour, $icon );
}

/**
 * Overrides default site swatch for navbar
 *
 * @return void
 * @author
 **/
function oxy_navbar_page_overrides( $classes ) {
    if( is_page() ) {
        global $post;
        $swatch = get_post_meta( $post->ID, THEME_SHORT . '_site_top_swatch', true );
        if( $swatch !== '' ) {
            // remove site swatch
            for( $i = 0 ; $i < count( $classes ) ; $i++ ) {
                if( strpos( $classes[$i], 'swatch-' ) !== false ) {
                    unset( $classes[$i] );
                }
            }
            $classes[] = $swatch;
        }
    }
    return $classes;
}
add_filter( 'oxy_navbar_class', 'oxy_navbar_page_overrides', 10 , 1 );


function oxy_get_external_link(){
    global $post;
    $link_shortcode = oxy_get_content_shortcode( $post, 'link' );
    if( $link_shortcode !== null ) {
        if( isset( $link_shortcode[5] ) ) {
            $link_shortcode = $link_shortcode[5];
            if( isset( $link_shortcode[0] ) ) {
                return $link_shortcode[0] ;
            }
            else{
                return get_permalink();
            }
        }
    }
}

function oxy_create_nav_top_bar() {
?>
<div class="top-bar <?php oxy_top_bar_classes(); ?>">
    <div class="container">
        <div class="top top-left">
            <?php dynamic_sidebar( 'above-nav-left' ); ?>
        </div>
        <div class="top top-right">
            <?php dynamic_sidebar( 'above-nav-right' ); ?>
        </div>
    </div>
</div>
<?php
}

function oxy_top_bar_classes() {
    global $oxy_theme_options;
    $classes = array();

    // add swatch to top bar
    $classes[] = $oxy_theme_options['top_bar_swatch'];

    // add extra class if one of the top sidebars has a shopping cart
    $sidebar_widgets = wp_get_sidebars_widgets();
    // $top_bar_widgets = array_merge( $sidebar_widgets['above-nav-left'], $sidebar_widgets['above-nav-right'] );
    // foreach( $top_bar_widgets as $widget ) {
    //     if( strpos( $widget, 'shopping_cart' ) !== false ) {
    //         $classes[] = 'has-shopping-cart';
    //     }
    // }

    $classes = apply_filters( 'oxy_top_bar_class', $classes );
    echo implode( ' ', $classes );
}

//Renders the middle header part when we don't have a combo menu with 3 sections
function oxy_create_nav() {
    global $oxy_theme_options;
    global $post;

    $show_top_bar = true;
    $show_top_nav = true;
    $menu_swatch = $oxy_theme_options['menu_swatch'];

    // check for page overrides
    if( is_page() ) {
        // are we showing the top bar?
        if( get_post_meta( $post->ID, THEME_SHORT . '_site_top_bar', true ) === 'hide' ) {
            $show_top_bar = false;
        }
        // are we showing the top nav?
        if( get_post_meta( $post->ID, THEME_SHORT . '_site_top_nav', true ) === 'hide' ) {
            $show_top_nav = false;
        }
        // does the page override the swatch?
        $menu_swatch_override = get_post_meta( $post->ID, THEME_SHORT . '_site_top_swatch', true );
        if( !empty( $menu_swatch_override ) ) {
            $menu_swatch = $menu_swatch_override;
        }
    }

    // render the top bar
    if ( $show_top_bar ) {
        oxy_create_nav_top_bar();
    }

    // render the nav
    if ( $show_top_nav ) {
        $locations = get_nav_menu_locations();
        if( isset( $locations['primary'] ) ) {
            $primary_menu = wp_get_nav_menu_object( $locations['primary'] );

            if( $primary_menu ) {
                echo oxy_shortcode_menu( array(
                    'slug'                   => $primary_menu->slug,
                    'menu_swatch'            => $menu_swatch,
                    'underline_menu'         => $oxy_theme_options['underline_menu'],
                    'menu_capitalization'    => $oxy_theme_options['menu_capitalization'],
                    'container_class'        => $oxy_theme_options['fullwidth_menu'],
                    'header_type'            => $oxy_theme_options['header_type'],
                    // extra options
                    'margin_top'             => 'no-top',
                    'margin_bottom'          => 'no-bottom',
                    'scroll_animation'       => 'none',
                    'scroll_animation_delay' => '0',
                ));
            }
        }
    }
}

// SEARCH WIDGET OUTPUT OVERRIDE

/* -------------------- OVERRIDE DEFAULT SEARCH WIDGET OUTPUT ------------------*/
function oxy_custom_search_form( $form ) {
    $output = '<form role="search" method="get" id="searchform" action="' . home_url( '/' ) . '">';
    $output .= '<div class="input-group">';
    $output .= '<input type="text" value="' . get_search_query() . '" name="s" id="s" class="form-control" placeholder="' . __('Search', 'omega-td') . '"/>';
    $output .= '<span class="input-group-btn">';
    $output .= '<button class="btn" type="submit" id="searchsubmit" value="'. esc_attr__('Search') . '">';
    $output .= '<i class="fa fa-search"></i>';
    $output .= '</button></span>';
    $output .= '</div></form>';
    return $output;
}
add_filter( 'get_search_form', 'oxy_custom_search_form' );


// REMOVING DEFAULT CONTACT FORM 7 STYLES
add_action( 'wp_print_styles', 'oxy_remove_cf7_css', 100 );

function oxy_remove_cf7_css(){
    wp_deregister_style( 'contact-form-7' );
}

function oxy_portfolio_pagination( $pagination_type ) {
    global $wp_query;
    $range = 2;
    switch( $pagination_type ) {
        case 'next_prev':
            if( $wp_query->max_num_pages > 1 ) : ?>
                <nav class="post-navigation">
                    <ul class="pager">
                        <li class="previous">
                            <?php previous_posts_link( __( '<i class="fa fa-angle-left"></i>Previous', 'omega-td' ) ); ?>
                        </li>
                        <li class="next">
                            <?php next_posts_link( __( 'Next<i class="fa fa-angle-right"></i>', 'omega-td' ) ); ?>
                        </li>
                    </ul>
                </nav>
            <?php endif;
        break;
        case 'pages':
        default:
            $showitems = ($range * 2) + 1;
            $paged = $wp_query->query_vars['paged'];

            // only show pages if we have more than 1
            if( !empty( $wp_query->max_num_pages ) ) : ?>
                <nav class="post-navigation text-center">
                    <ul class="pagination">
                        <li class="<?php echo $paged > 1 ? '' : 'disabled' ?>">
                            <?php if( $paged > 1 ) : ?>
                                <a href="<?php echo get_pagenum_link( $paged - 1 ); ?>">
                                    <i class="fa fa-angle-left"></i>
                                </a>
                            <?php else : ?>
                                <span>
                                    <i class="fa fa-angle-left"></i>
                                </span>
                            <?php endif; ?>
                        </li>

                        <?php for( $i = 1; $i <= $wp_query->max_num_pages; $i++ ) : ?>
                            <li>
                                <?php if( $paged == $i ) : ?>
                                    <span class="current">
                                        <?php echo $i; ?>
                                    </span>
                                <?php else : ?>
                                    <a href="<?php echo get_pagenum_link( $i ); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>

                        <li class="<?php echo $paged < $wp_query->max_num_pages ? '' : 'disabled' ?>">
                            <?php if( $paged < $wp_query->max_num_pages ) : ?>
                                <a href="<?php echo get_pagenum_link( $paged + 1 ); ?>">
                                    <i class="fa fa-angle-right"></i>
                                </a>
                            <?php else : ?>
                                <span>
                                    <i class="fa fa-angle-right"></i>
                                </span>
                            <?php endif; ?>
                        </li>
                    </ul>
                </nav>
            <?php endif;
        break;
    }
}

function oxy_pagination( $pages = '', $range = 2, $blog = true, $infinite = false, $echo = true ) {
    global $wp_query;
    global $paged;
    $output = '';
    if($infinite == false){
        // we don't use infinite pagination , show display the chosen pagination style
        switch( oxy_get_option( 'blog_pagination' ) ) {
            case 'next_prev':
                if( $wp_query->max_num_pages > 1 ) {
                    $output .= '<nav id="nav-below" class="post-navigation padded">';
                    $output .= '<ul class="pager">';
                    $output .= '<li class="previous">' . get_next_posts_link( __( '<i class="fa fa-angle-left"></i>Previous', 'omega-td' ) ) . '</li>';
                    $output .= '<li class="next">' . get_previous_posts_link( __( 'Next<i class="fa fa-angle-right"></i>', 'omega-td' ) ) . '</li>';
                    $output .= '</ul>';
                    $output .= '</nav>';
                }
            break;
            case 'pages':
            default:
                $showitems = ($range * 2)+1;
                if(empty($paged)) {
                    $paged = 1;
                }

                if($pages == '') {
                    global $wp_query;
                    $pages = $wp_query->max_num_pages;
                    if(!$pages) {
                        $pages = 1;
                    }
                }

                $extraClass = "";
                if($blog == true && oxy_get_option('blog_post_icons') == 'on'){
                    // this class pushes the pagination 80px to the right in blog pages
                    $extraClass = "post-showinfo";
                }

                if(1 != $pages) {
                    $output.= '<div class="text-center '.$extraClass.'"><ul class="post-navigation pagination">';
                    $output.= ($paged > 1 )? '<li><a href="' . get_pagenum_link($paged - 1) . '">&lsaquo;</a></li>' : '<li class="disabled"><a>&lsaquo;</a></li>';

                    for($i=1; $i <= $pages; $i++) {
                        if(1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems )) {
                            $output.= ($paged == $i) ? '<li class="active"><span class="current">' . $i . '</span></li>' : '<li><a href="' . get_pagenum_link($i) . '" class="inactive">' . $i . '</a></li>';
                        }
                    }

                    $output.= ($paged < $pages)? "<li><a href='".get_pagenum_link($paged + 1)."'>&rsaquo;</a></li>": "<li class='disabled'><a>&rsaquo;</a></li>";
                    $output.= "</ul></div>\n";
                }
            break;
        }
    }

    if($echo == true) {
        echo $output;
    }
    else {
        return $output;
    }
}


/**
 * Hidden Pagination for masonry with infinite-scroll
 *
 * @return void
 * @author
 **/
function infinite_scroll_pagination(){
    echo '<div class="infinite-scroll" style="visibility:hidden;">'. get_next_posts_link().'</div>';
}


/**
 * Bootstrap the page links under a post
 *
 * @return void
 * @author
 **/
function oxy_wp_link_pages($args = '') {
    $defaults = array(
        'before' => '' ,
        'after' => '',
        'link_before' => '',
        'link_after' => '',
        'next_or_number' => 'number',
        'nextpagelink' => __('Next page', 'omega-td' ),
        'previouspagelink' => __('Previous page', 'omega-td' ),
        'pagelink' => '%',
        'echo' => 1
    );

    $r = wp_parse_args( $args, $defaults );
    $r = apply_filters( 'wp_link_pages_args', $r );
    extract( $r, EXTR_SKIP );

    global $page, $numpages, $multipage, $more, $pagenow;

    $output = '';
    if ( $multipage ) {
        if ( 'number' == $next_or_number ) {
            $output .= $before . '<ul class="post-navigation pagination">';
            $laquo = $page == 1 ? 'class="disabled"' : '';
            $output .= '<li ' . $laquo .'>' . _wp_link_page($page -1) . '&laquo;</a></li>';
            for ( $i = 1; $i < ($numpages+1); $i = $i + 1 ) {
                $j = str_replace('%',$i,$pagelink);

                if ( ($i != $page) || ((!$more) && ($page==1)) ) {
                    $output .= '<li>';
                    $output .= _wp_link_page($i) ;
                }
                else{
                    $output .= '<li class="active">';
                    $output .= _wp_link_page($i) ;
                }
                $output .= $link_before . $j . $link_after ;
                $output .= '</a>';

                $output .= '</li>';
            }
            $raquo = $page == $numpages ? 'class="disabled"' : '';
            $output .= '<li ' . $raquo .'>' . _wp_link_page($page +1) . '&raquo;</a></li>';
            $output .= '</ul>' . $after;
        }
        else {
            if ( $more ) {
                $output .= $before . '<ul class="pager">';
                $i = $page - 1;
                if ( $i && $more ) {
                    $output .= '<li class="previous">' . _wp_link_page($i);
                    $output .= $link_before. $previouspagelink . $link_after . '</a></li>';
                }
                $i = $page + 1;
                if ( $i <= $numpages && $more ) {
                    $output .= '<li class="next">' .  _wp_link_page($i);
                    $output .= $link_before. $nextpagelink . $link_after . '</a></li>';
                }
                $output .= '</ul>' . $after;
            }
        }
    }

    if ( $echo ) {
        echo $output;
    }

    return $output;
}

function oxy_post_thumbnail_name($post , $echo = false){
    $name = basename ( get_attached_file(  get_post_thumbnail_id($post->ID)) );
    if($echo == true){
        echo $name;
    }
    else{
        return $name;
    }
}

function oxy_remove_category_list_rel( $output ) {
    // make rel valid
    return str_replace( ' rel="category tag"', ' rel="tag"', $output );
}
add_filter( 'wp_list_categories', 'oxy_remove_category_list_rel' );
add_filter( 'the_category', 'oxy_remove_category_list_rel' );

// remove site header on pages with the site header turned off
function oxy_filter_body_class( $classes ) {
    global $oxy_theme_options;

    if( $oxy_theme_options['layout_type'] == 'boxed') {
        $classes[] = 'layout-boxed';
    }

    if( is_page() ) {
        global $post;
        if( get_post_meta( $post->ID, THEME_SHORT . '_site_top_nav_transparency', true ) === 'on' ) {
            $classes[] = 'transparent-header';
        }
    }
    // add classes for mobile agents
    global $oxy_is_iphone, $oxy_is_android, $oxy_is_ipad;
    if($oxy_is_iphone){
        $classes[] = 'oxy-agent-iphone';
    }
    else if($oxy_is_ipad){
        $classes[] = 'oxy-agent-ipad';
    }
    else if($oxy_is_android){
        $classes[] = 'oxy-agent-android';
    }

    // current language code if wpml is installed
    if( function_exists( 'icl_get_home_url' ) ) {
          $classes[] = ICL_LANGUAGE_CODE;
    }

    // woocommerce cart popup option
    if( $oxy_theme_options['woo_cart_popup'] == 'show' ){
        $classes[] = 'woo-cart-popup';
    }

    return $classes;
}
add_filter( 'body_class', 'oxy_filter_body_class');


function oxy_is_woocommerce_active() {
    // check if woo commerce is active
    return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
}

function oxy_woo_page_title( $echo = false ){
    if( is_search() ) {
        $page_title = sprintf( __( 'Search Results: &ldquo;%s&rdquo;', 'woocommerce' ), get_search_query() );
        if ( get_query_var( 'paged' ) ){
            $page_title .= sprintf( __( '&nbsp;&ndash; Page %s', 'woocommerce' ), get_query_var( 'paged' ) );
        }
    }
    elseif( is_tax() ) {
        $page_title = single_term_title( "", false );

    }
    else {
        $shop_page_id = woocommerce_get_page_id( 'shop' );
        $page_title   = get_the_title( $shop_page_id );
    }
    if($echo){
        echo $page_title;
    }
    else{
        return $page_title;
    }
}

add_filter('next_posts_link_attributes', 'next_posts_link_attributes');
add_filter('previous_posts_link_attributes', 'prev_posts_link_attributes');

function next_posts_link_attributes() {
    return 'class="btn btn-primary btn-icon btn-icon-left"';
}
function prev_posts_link_attributes() {
    return 'class="btn btn-primary btn-icon btn-icon-right"';
}

/**
 * Creates a shaped image for a post
 *
 * @return HTML for the image
 **/
function oxy_create_shaped_featured_image( $post, $shape, $shape_size, $shadow, $link = null, $figcaption = null, $small_image_version = null ) {

    $image_box = '';
    $shadow_class = $shadow ? ' flat-shadow' : '';

    if ( $shape == 'hex' ) {
        $image_box .= '<div class="box-wrap">';
    }
    $image_box .= '<div class="box-' . $shape . $shadow_class . ' box-' . $shape_size . '">';
    $image_box .= '<div class="box-dummy"></div>';

    $shape_image_size = $shape === 'round' ? 'square-image' : $shape . '-image';
    $extra_image_class = $shape === 'round' ? 'img-circle' : '';
    if($small_image_version == true){
        $shape_image_size = $shape_image_size . '-small';
    }

    $image_type = get_post_meta( $post->ID, THEME_SHORT. '_image_type', true );
    if( '' !== $image_type ) {
        $icon           = get_post_meta( $post->ID, THEME_SHORT. '_icon', true );
        $icon_animation = get_post_meta( $post->ID, THEME_SHORT. '_icon_animation', true );

        switch( $image_type ) {
            case 'image':
                $image_tag = get_the_post_thumbnail( $post->ID,  $shape_image_size, array( 'data-animation' => $icon_animation , 'class' => $extra_image_class ) );
            break;
            case 'both':
                $image_tag = get_the_post_thumbnail( $post->ID,  $shape_image_size, array( 'class' => $extra_image_class ) );
                $image_tag .= '<i class="fa fa-' . $icon . '" data-animation="' . $icon_animation . '"></i>';
            break;
            case 'icon':
                $image_tag = '<i class="fa fa-' . $icon . '" data-animation="' . $icon_animation . '"></i>';
            break;
        }
    }
    else {
        $image_tag = get_the_post_thumbnail( $post->ID,  $shape_image_size, array() );
    }

    if( null !== $figcaption ){
        // staff figcaption
        $image_box .= '<figure class="box-inner">' . $image_tag . '<figcaption class="box-caption">'.$figcaption.'</figcaption></figure>';
    }
    else if( null === $link ) {
        $image_box .= '<div class="box-inner">' . $image_tag . '</div>';
    }
    else {
        $image_box .= '<a class="box-inner" href="' . $link . '">' . $image_tag . '</a>';
    }

    $image_box .= '</div>';

    if ( $shape == 'hex' ) {
        $image_box .= '</div>';
    }

    return $image_box;
}

/**
 * Class Name: wp_bootstrap_navwalker
 * GitHub URI: https://github.com/twittem/wp-bootstrap-navwalker
 * Description: A custom WordPress nav walker class to implement the Bootstrap 3 navigation style in a custom theme using the WordPress built in menu manager.
 * Version: 2.0.4
 * Author: Edward McIntyre - @twittem
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

class oxy_bootstrap_navwalker extends Walker_Nav_Menu {
    private $parent_item;
    private $current_item;
    /**
     * @see Walker::start_lvl()
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int $depth Depth of page. Used for padding.
     */
    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        // store the parent item so we can decide if it needs to be a dropdown or not
        // columns will not use dropdowns
        $this->parent_item = $this->current_item;

        $indent = str_repeat( "\t", $depth );

        $classes = array();
        $classes[] = 'dropdown-menu';

        // if this is a column we dont want it to be a dropdown menu
        if( null !== $this->parent_item && $this->parent_item->object === 'oxy_mega_columns' ) {
            unset( $classes[0] );
        }
        // if this is a mega menu we want to add a row class to it
        if( $this->current_item->object === 'oxy_mega_menu' ) {
            $classes[] = 'row';
        }
        $output .= "\n$indent<ul role=\"menu\" class=\"" . implode( ' ', $classes ) . "\">\n";
    }

    /**
     * @see Walker::start_el()
     * @since 3.0.0
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item Menu item data object.
     * @param int $depth Depth of menu item. Used for padding.
     * @param int $current_page Menu item ID.
     * @param object $args
     */
    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $this->current_item = $item;

        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';
        global $oxy_is_iphone, $oxy_is_android, $oxy_is_ipad;
        /**
         * Dividers, Headers or Disabled
         * =============================
         * Determine whether the item is a Divider, Header, Disabled or regular
         * menu item. To prevent errors we use the strcasecmp() function to so a
         * comparison that is not case sensitive. The strcasecmp() function returns
         * a 0 if the strings are equal.
         */
        if ( strcasecmp( $item->attr_title, 'divider' ) == 0 && $depth === 1 ) {
            $output .= $indent . '<li role="presentation" class="divider">';
        } else if ( strcasecmp( $item->title, 'divider') == 0 && $depth === 1 ) {
            $output .= $indent . '<li role="presentation" class="divider">';
        } else if ( strcasecmp( $item->attr_title, 'dropdown-header') == 0 && $depth === 1 ) {
            $output .= $indent . '<li role="presentation" class="dropdown-header">' . esc_attr( $item->title );
        } else if ( strcasecmp($item->attr_title, 'disabled' ) == 0 ) {
            $output .= $indent . '<li role="presentation" class="disabled"><a href="#">' . esc_attr( $item->title ) . '</a>';
        } else {

            $class_names = $value = '';

            $classes = empty( $item->classes ) ? array() : (array) $item->classes;
            $classes[] = 'menu-item-' . $item->ID;

            // add column classes if item is a column post
            if( $item->object === 'oxy_mega_columns' ) {
                $classes[] = get_post_field( 'post_content', $item->object_id );
            }

            $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );

            if ( $args->has_children )
                $class_names .= ' dropdown';

            if ( in_array( 'current-menu-item', $classes ) )
                $class_names .= ' active';

            $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

            $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
            $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

            $output .= $indent . '<li' . $id . $value . $class_names .'>';

            $atts = array();
            $atts['target'] = ! empty( $item->target )  ? $item->target : '';
            $atts['rel']    = ! empty( $item->xfn )     ? $item->xfn    : '';

            // If item has_children add atts to a.
            if ( $args->has_children && $depth === 0 ) {
                $atts['href']           = '#';
                $atts['data-toggle']    = 'dropdown';
                if( oxy_get_option( 'hover_menu' ) === 'on' && !$oxy_is_iphone && !$oxy_is_android && !$oxy_is_ipad )  {
                    if( $item->object == 'oxy_mega_menu' ) {
                        $atts['href'] = empty( $item->description ) ? '#' : $item->description;
                    }
                    else {
                        $atts['href'] = ! empty( $item->url ) ? $item->url : '';
                    }
                    $atts['data-hover']  = 'dropdown';
                    unset( $atts['data-toggle'] );
                    $atts['data-delay']  = oxy_get_option( 'hover_menu_delay' ) * 1000;
                }
                $atts['class']          = 'dropdown-toggle';
            } else {
                $atts['href'] = ! empty( $item->url ) ? $item->url : '';
            }

            $atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args );

            // we want mega menu columns to use spans instead of links
            $link_tag = $item->object === 'oxy_mega_columns' ? 'strong' : 'a';

            if( $link_tag === 'strong' && isset( $atts['href'] ) ) {
                unset($atts['href']);
            }

            $attributes = '';
            foreach ( $atts as $attr => $value ) {
                if ( ! empty( $value ) ) {
                    $value = ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
                    $attributes .= ' ' . $attr . '="' . $value . '"';
                }
            }

            $item_output = $args->before;

            /*
             * Glyphicons
             * ===========
             * Since the the menu item is NOT a Divider or Header we check the see
             * if there is a value in the attr_title property. If the attr_title
             * property is NOT null we apply it as the class name for the glyphicon.
             */
            if ( ! empty( $item->attr_title ) )
                $item_output .= '<' . $link_tag . $attributes .'><i class="fa ' . esc_attr( $item->attr_title ) . '"></i>&nbsp;';
            else
                $item_output .= '<' . $link_tag . $attributes .'>';

            $item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
            // Oxygenna changed this line to remove caret
            $item_output .= '</' . $link_tag . '>';
            $item_output .= $args->after;

            if( $item->object === 'oxy_mega_columns' && !empty( $item->description ) ) {
                $item_output .= '<p>' . $item->description . '</p>';
            }

            $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
        }
    }

    /**
     * Traverse elements to create list from elements.
     *
     * Display one element if the element doesn't have any children otherwise,
     * display the element and its children. Will only traverse up to the max
     * depth and no ignore elements under that depth.
     *
     * This method shouldn't be called directly, use the walk() method instead.
     *
     * @see Walker::start_el()
     * @since 2.5.0
     *
     * @param object $element Data object
     * @param array $children_elements List of elements to continue traversing.
     * @param int $max_depth Max depth to traverse.
     * @param int $depth Depth of current element.
     * @param array $args
     * @param string $output Passed by reference. Used to append additional content.
     * @return null Null on failure with no changes to parameters.
     */
    public function display_element( $element, &$children_elements, $max_depth, $depth, $args, &$output ) {
        if ( ! $element )
            return;

        $id_field = $this->db_fields['id'];

        $element->is_dropdown = !empty( $children_elements[$element->ID] );

        // Display this element.
        if ( is_object( $args[0] ) ) {
           $args[0]->has_children = ! empty( $children_elements[ $element->$id_field ] );
           if( $args[0]->has_children && $depth > 0 ) {
                $element->classes[] = 'dropdown-submenu';
            }
        }

        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }

    /**
     * Menu Fallback
     * =============
     * If this function is assigned to the wp_nav_menu's fallback_cb variable
     * and a manu has not been assigned to the theme location in the WordPress
     * menu manager the function with display nothing to a non-logged in user,
     * and will add a link to the WordPress menu manager if logged in as an admin.
     *
     * @param array $args passed from the wp_nav_menu function.
     *
     */
    public static function fallback( $args ) {
        if ( current_user_can( 'manage_options' ) ) {

            extract( $args );

            $fb_output = null;

            if ( $container ) {
                $fb_output = '<' . $container;

                if ( $container_id )
                    $fb_output .= ' id="' . $container_id . '"';

                if ( $container_class )
                    $fb_output .= ' class="' . $container_class . '"';

                $fb_output .= '>';
            }

            $fb_output .= '<ul';

            if ( $menu_id )
                $fb_output .= ' id="' . $menu_id . '"';

            if ( $menu_class )
                $fb_output .= ' class="' . $menu_class . '"';

            $fb_output .= '>';
            $fb_output .= '<li><a href="' . admin_url( 'nav-menus.php' ) . '">Add a menu</a></li>';
            $fb_output .= '</ul>';

            if ( $container )
                $fb_output .= '</' . $container . '>';

            echo $fb_output;
        }
    }
}

function oxy_get_portfolio_item_data( $item ) {
    // setup post data
    global $post;
    $post = $item;
    setup_postdata($post);
    global $more;    // Declare global $more (before the loop).
    $more = 0;

    // grab the featured image
    $full_image_id = get_post_thumbnail_id( $post->ID );
    $full_image_src = wp_get_attachment_image_src( $full_image_id, 'full' );

    // create info data structure
    $info = new stdClass();
    $info->title = get_the_title( $post->ID );
    if( false !== $full_image_src ) {
        $info->full_image_url = $full_image_src[0];
    }
    else {
        $info->full_image_url = '';
    }
    // set default popup link
    $info->popup_link = $info->full_image_url;
    $info->isGallery = false;
    // post format specific data
    $format = get_post_format( $post->ID );
    if( false === $format ) {
        $format = 'standard';
    }
    switch( $format ) {
        case 'standard':
            $info->icon = 'fa fa-search-plus';
            $info->popup_class = 'magnific';
        break;
        case 'image':
            $info->icon = 'fa fa-picture-o';
            $info->popup_class = 'magnific';
        break;
        case 'video':
            $info->icon = 'fa fa-play';
            $info->popup_class = 'magnific-vimeo';
            $video_shortcode = oxy_get_content_shortcode( $post, 'embed' );
            if( $video_shortcode !== null ) {
                if( isset( $video_shortcode[5] ) ) {
                    $video_shortcode = $video_shortcode[5];
                    if( isset( $video_shortcode[0] ) ) {
                        $info->popup_link = $video_shortcode[0];
                    }
                }
            }
        break;
        case 'audio':
            $info->icon = 'fa fa-play';
            $info->popup_class = 'magnific-audio';
            $audio_shortcode = oxy_get_content_shortcode( $post, 'audio' );
            if( $audio_shortcode !== null){
                $audio_src = null;
                if( array_key_exists( 3, $audio_shortcode ) ) {
                    if( array_key_exists( 0, $audio_shortcode[3] ) ) {
                        $audio_attrs = shortcode_parse_atts( $audio_shortcode[3][0] );
                        if( array_key_exists( 'src', $audio_attrs) ) {
                            $info->popup_link = $audio_attrs['src'];
                        }
                    }
                }
            }
        break;
        case 'gallery':
            $info->icon = 'fa fa-search-plus';
            $info->popup_class = 'magnific-gallery';
            $info->isGallery = true;
            $gallery_ids = oxy_get_content_gallery( $post );
            if( $gallery_ids !== null ) {
                if( count( $gallery_ids ) > 0 ) {
                    // ok lets create a gallery
                    $gallery_rel = 'rel="gallery' . $post->ID . '"';
                    $gallery_images = array();
                    foreach( $gallery_ids as $gallery_image_id ) {
                        $gallery_image = wp_get_attachment_image_src( $gallery_image_id, 'full');
                        $gallery_images[] = $gallery_image[0] ;
                    }
                    $info->gallery_links = implode(",", $gallery_images);
                }
            }
        break;

    }

    $info->item_link = get_permalink( $post->ID );
    return $info;
}

/**
 * Calls a shortcode function using custom meta data array to replace attributes where available
 * Override array can be sent to override attributes
 * @param   String  shortcode function name
 * @param   Array   array of shortcode attributes
 * @param   String  content to send to the shortcode
 * @param   Array   custom meta data to use as shortcode attribites
 * @param   Array   any values of the custom data that you want to override (optional)
 *
 * @return shortcode function output
 **/
function oxy_call_shortcode_with_meta( $shortcode, $attrs, $content, $custom, $overrides = array() ) {
    // prepare attributes
    $shortcode_atts = array();
    foreach( $attrs as $attr ) {
        if( isset( $overrides[$attr] ) ) {
            $shortcode_atts[$attr] = $overrides[$attr];
        }
        else if( isset( $custom[THEME_SHORT.'_'.$attr] ) ) {
            $shortcode_atts[$attr] = $custom[THEME_SHORT.'_'.$attr][0];
        }
    }
    return call_user_func_array( $shortcode, array( $shortcode_atts, $content ) );
}

function oxy_call_shortcode_with_tax_meta( $shortcode, $attrs, $content, $id, $overrides = array() ) {
    // prepare attributes
    $shortcode_atts = array();
    foreach( $attrs as $attr ) {
        $opt = get_option( THEME_SHORT.'-tax-mtb-'.$attr.$id, '');
        if( isset( $overrides[$attr] ) ) {
            $shortcode_atts[$attr] = $overrides[$attr];
        }
        else if( !empty( $opt ) ) {
            $shortcode_atts[$attr] = $opt;
        }

    }
    return call_user_func_array( $shortcode, array( $shortcode_atts, $content ) );
}

/**
 * Outputs a page header
 *
 * @return void
 **/
function oxy_page_header( $id, $heading_overrides = array(), $section_overrides = array() ) {
    $custom = get_post_custom( $id );
    if( isset( $custom[THEME_SHORT.'_show_header'] ) && $custom[THEME_SHORT.'_show_header'][0] === 'show' ) {
        // if we have no custom title then use get_the_title
        $title = empty( $custom[THEME_SHORT.'_content'][0] ) ? get_the_title( $id ) : $custom[THEME_SHORT.'_content'][0];
        // create heading using custom meta data
        $heading = oxy_call_shortcode_with_meta( 'oxy_section_heading', array(
            'sub_header',
            'header_type',
            'section_swatch_override',
            'heading_colour',
            'sub_header_size',
            'header_size',
            'header_weight',
            'header_align',
            'header_condensed',
            'header_underline',
            'header_underline_size',
            'heading_type',
            'extra_classes',
            'margin_top',
            'margin_bottom',
            'scroll_animation',
            'scroll_animation_delay'
        ), $title, $custom, $heading_overrides );
        // create section using custom meta data
        echo oxy_call_shortcode_with_meta( 'oxy_shortcode_section', array(
            'swatch',
            'text_shadow',
            'inner_shadow',
            'width',
            'class',
            'id',
            'overlay_colour',
            'overlay_opacity',
            'overlay_grid',
            'background_video_mp4',
            'background_video_webm',
            'background_image',
            'background_image_size',
            'background_image_repeat',
            'background_image_attachment',
            'background_position_vertical',
            'height',
            'transparency',
        ), $heading, $custom, $section_overrides );
    }
}

/**
 * Calls a shortcode function using theme options array to replace attributes where available
 * Override array can be sent to override attributes
 * @param   String  shortcode function name
 * @param   Array   array of shortcode attributes
 * @param   String  content to send to the shortcode
 * @param   Array   custom meta data to use as shortcode attribites
 * @param   Array   any values of the custom data that you want to override (optional)
 *
 * @return shortcode function output
 **/
function oxy_call_shortcode_with_theme_options( $shortcode, $attrs, $content, $custom, $prefix, $overrides = array() ) {
    // prepare attributes
    $shortcode_atts = array();
    foreach( $attrs as $attr ) {
        //echo $prefix.$attr.' val: ('.$custom[$prefix.$attr].' )  ';
        if( isset( $overrides[$attr] ) ) {
             $shortcode_atts[$attr] = $overrides[$attr];
        }
        else if( isset( $custom[$prefix.$attr] ) ) {
            $shortcode_atts[$attr] = $custom[$prefix.$attr];
        }
    }
    return call_user_func_array( $shortcode, array( $shortcode_atts, $content ) );
}

/**
 * Outputs the blog header
 *
 * @return void
 **/
function oxy_blog_header( $title = null, $subtitle = null ) {
    global $oxy_theme_options;

    if ( isset( $oxy_theme_options['blog_header_show_header'] ) && $oxy_theme_options['blog_header_show_header'] === 'show' ){
        // use custom title
        $title = $title === null ? $oxy_theme_options['blog_header_content'] : $title;
        $sub_header = $subtitle === null ? $oxy_theme_options['blog_header_sub_header'] : $subtitle;

        $heading = oxy_call_shortcode_with_theme_options( 'oxy_section_heading', array(
            'sub_header',
            'header_type',
            'sub_header_size',
            'header_size',
            'header_weight',
            'header_align',
            'header_condensed',
            'header_underline',
            'header_underline_size',
            'heading_type',
            'extra_classes',
            'margin_top',
            'margin_bottom',
            'scroll_animation',
            'scroll_animation_delay'
        ), $title, $oxy_theme_options, 'blog_header_', array( 'heading_type' => 'blog' , 'sub_header' => $sub_header ) );
        // create section using theme options
        echo oxy_call_shortcode_with_theme_options( 'oxy_shortcode_section', array(
            'swatch',
            'text_shadow',
            'inner_shadow',
            'width',
            'class',
            'id',
            'overlay_colour',
            'overlay_opacity',
            'overlay_grid',
            'background_video_mp4',
            'background_video_webm',
            'background_image',
            'background_image_size',
            'background_image_repeat',
            'background_image_attachment',
            'background_position_vertical',
            'height',
            'transparency',
        ), $heading, $oxy_theme_options, 'blog_header_');
    }
}

/**
 * Overrides default footer swatch for page
 *
 * @return new classes array
 **/
function oxy_footer_page_overrides( $classes ){
    if( is_page() ) {
        global $post;
        $swatch = get_post_meta( $post->ID, THEME_SHORT . '_site_footer_swatch', true );
        if( $swatch !== '' ) {
            // remove site swatch
            for( $i = 0 ; $i < count( $classes ) ; $i++ ) {
                if( strpos( $classes[$i], 'swatch-' ) !== false ) {
                    unset( $classes[$i] );
                }
            }
            $classes[] = $swatch;
        }
    }
    return $classes;
}
add_filter( 'oxy_footer_section_class', 'oxy_footer_page_overrides' );

/**
 * Creates the classes for the footer section
 *
 * @return space separated list of footer classes
 **/
function oxy_footer_section_classes() {
    global $oxy_theme_options;

    $classes = array();
    $classes[] = $oxy_theme_options['footer_swatch'];

    $classes = apply_filters( 'oxy_footer_section_class', $classes );
    echo implode( ' ', $classes );
}

/**
 * Creates the classes for the footer section
 *
 * @return space separated list of footer classes
 **/
function oxy_upper_footer_section_classes() {
    global $oxy_theme_options;

    $classes = array();
    $classes[] = $oxy_theme_options['upper_footer_swatch'];

    $classes = apply_filters( 'oxy_upper_footer_section_class', $classes );
    echo implode( ' ', $classes );
}

// filter to skip quote posts in next/prev pagination
function oxy_adjacent_post_where( $where )
{
    $quote_format = get_term_by( 'slug', 'post-format-quote', 'post_format' );
    $link_format  = get_term_by( 'slug', 'post-format-link', 'post_format' );

    if ( $quote_format !== false || $link_format !== false ) {
        $terms = array();
        if( $quote_format !== false ){
            $terms[] = $quote_format->term_id;
        }
        if( $link_format !== false ){
            $terms[] = $link_format->term_id;
        }
        $ex_cats_posts = get_objects_in_term( $terms, array('post_format') );
        return $where . ' AND p.ID NOT IN (' .  implode($ex_cats_posts, ','). ' )';
    }
    return $where;
}
add_filter( 'get_previous_post_where', 'oxy_adjacent_post_where' );
add_filter( 'get_next_post_where', 'oxy_adjacent_post_where' );

function oxy_output_extra_css() { ?>
    <style type="text/css" media="screen">
        <?php echo get_option( THEME_SHORT . '-header-css', '' ); ?>
        <?php echo get_option( THEME_SHORT . '-default-css' ); ?>
        <?php echo oxy_get_option( 'extra_css' ); ?>
        <?php if( is_admin_bar_showing() ) : ?>
            @media screen and (min-width: 782px) {
                .admin-bar .navbar-stuck {
                    top: 32px !important;
                }
            }
        <?php endif; ?>
    </style>
<?php
    // output typography css & js
    echo get_option('oxy-typography-js');
    echo get_option('oxy-typography-css');
    if( oxy_get_option( 'swatch_css_save' ) === 'head' ) {
        $swatch_list = get_option( THEME_SHORT . '-swatch-list', array() );
        foreach ( $swatch_list as $swatch_css ) {
            echo '<style type="text/css" media="screen">' . $swatch_css . '</style>';
        }
    }
}
add_action('wp_head', 'oxy_output_extra_css');

/**
 * Converts hex to rgba with optional opacity
 *
 * @return space separated list of footer classes
 **/
function oxy_hex2rgba($color, $opacity = false) {

    $default = false;

    //Return default if no color provided
    if(empty($color)) {
        return $default;
    }

    //Check for a hex color string '#c1c2b4'
    if( ! preg_match('/^#[a-f0-9]{6}$/i', $color) ) {
        return $default;
    }

    //Sanitize $color if "#" is provided
    if ($color[0] == '#' ) {
        $color = substr( $color, 1 );
    }

    //Check if color has 6 or 3 characters and get values
    if (strlen($color) == 6) {
        $hex = array( $color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5] );
    } elseif ( strlen( $color ) == 3 ) {
        $hex = array( $color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2] );
    } else {
        return $default;
    }

    //Convert hexadec to rgb
    $rgb =  array_map('hexdec', $hex);

    //Check if opacity is set(rgba or rgb)
    if( $opacity !== false ){
        if(abs($opacity) > 1) {
            $opacity = 1.0;
        }
        $output = 'rgba('.implode(",",$rgb).','.$opacity.')';
    } else {
        $output = 'rgb('.implode(",",$rgb).')';
    }

    //Return rgb(a) color string
    return $output;
}

function oxy_prefix_options_id( $prefix, $options ){
    foreach( $options as $index => &$val ) {
        $val['id'] = $prefix.'_'.$val['id'];
    }
    return $options;
}

// Custom excerpt function
function oxy_excerpt( $limit ) {
    $excerpt = explode( ' ', get_the_excerpt(), $limit );
    if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode( ' ',$excerpt ) . '...';
    } else {
        $excerpt = implode( ' ',$excerpt );
    }
    $excerpt = preg_replace( '`[[^]]*]`', '', $excerpt );
    return $excerpt;
}
