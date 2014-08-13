<div class="list-container <?php echo implode( ' ', $classes ); ?>"><?php
    foreach ($items as $item) :
        global $post;
        $post = $item;
        setup_postdata($post);
        $custom_fields = get_post_custom($post->ID);
        $cite  = (isset($custom_fields[THEME_SHORT.'_citation']))? $custom_fields[THEME_SHORT.'_citation'][0]:''; ?>
        <div class="<?php echo implode( ' ', $wrapper_classes ); ?>" data-os-animation="<?php echo $scroll_animation; ?>" data-os-animation-delay="<?php echo $scroll_animation_delay; ?>s">
            <blockquote class="blockquote-list">
                <div class="box box-small box-round">
                    <div class="box-dummy"></div>
                    <div class="box-inner">
                        <?php echo get_the_post_thumbnail( $post->ID, 'thumbnail' ); ?>
                    </div>
                </div>
                <p><?php echo strip_tags( get_the_content() ); ?></p>
                <footer>
                    <?php
                    the_title();
                    if( !empty( $cite ) ) {?>
                    <cite title="Source Title"><?php
                        echo $cite; ?>
                    </cite>
                <?php } ?>
                </footer>
            </blockquote>
        </div><?php
        if( $testimonial_scroll_animation_timing === 'staggered' ) :
                $scroll_animation_delay += $item_delay;
        endif;
    endforeach; ?>
    </ul>
</div><?php
wp_reset_postdata(); ?>
