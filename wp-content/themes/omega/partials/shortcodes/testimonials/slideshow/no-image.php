<?php
$id = 'flexslider-' . rand(1,1000); ?>
<div id="<?php echo $id; ?>" class="<?php echo implode( ' ', $classes ); ?> feature-slider" data-os-animation="<?php echo $scroll_animation; ?>" data-os-animation-delay="<?php echo $scroll_animation_delay; ?>s" data-flex-animation="slide" data-flex-controls-position="outside" data-flex-controlsalign="center" data-flex-directions="hide" data-flex-speed="<?php echo $speed; ?>" data-flex-controls="show" data-flex-slideshow="true">
    <ul class="slides"><?php
    foreach ($items as $item) :
        global $post;
        $post = $item;
        setup_postdata($post);
        $custom_fields = get_post_custom($post->ID);
        $cite  = (isset($custom_fields[THEME_SHORT.'_citation']))? $custom_fields[THEME_SHORT.'_citation'][0]:''; ?>
        <li>
            <blockquote>
                <p><?php echo strip_tags( get_the_content() ); ?></p>
                <footer><?php
                    the_title();
                    if( !empty( $cite ) ) {?>
                    <cite title="<?php echo $cite; ?>"><?php
                        echo $cite; ?>
                    </cite>
                <?php } ?>
                </footer>
            </blockquote>
        </li><?php
    endforeach; ?>
    </ul>
</div><?php
wp_reset_postdata(); ?>