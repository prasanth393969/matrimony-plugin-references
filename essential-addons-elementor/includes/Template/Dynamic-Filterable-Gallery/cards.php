<?php
use Essential_Addons_Elementor\Classes\Helper;
/**
 * Template Name: Cards
 *
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

$show_category_child_items    = ! empty( $settings['category_show_child_items'] ) && 'yes' === $settings['category_show_child_items'] ? 1 : 0;
$show_product_cat_child_items = ! empty( $settings['product_cat_show_child_items'] ) && 'yes' === $settings['product_cat_show_child_items'] ? 1 : 0;

$helperClass        = new Essential_Addons_Elementor\Pro\Classes\Helper();
$classes            = $helperClass->get_dynamic_gallery_item_classes( $show_category_child_items, $show_product_cat_child_items );
$fetch_acf_image    = isset( $settings['eael_gf_hide_parent_items'] ) && 'yes'   === $settings['eael_gf_hide_parent_items'];
$has_post_thumbnail = has_post_thumbnail() || ( $fetch_acf_image && 'attachment' === get_post_type() );
$classes[]          = get_post_field( 'post_name' );
$image_url          = $has_post_thumbnail ? wp_get_attachment_image_url(get_post_thumbnail_id(), $settings['image_size']) : \Elementor\Utils::get_placeholder_image_src();
$alt_text           = $has_post_thumbnail ? get_post_meta(get_post_thumbnail_id(), '_wp_attachment_image_alt', true) : '';
$popup_thumb_url    = $has_post_thumbnail ? wp_get_attachment_image_url(get_post_thumbnail_id(), 'full') : \Elementor\Utils::get_placeholder_image_src();
$post_url        = get_the_permalink();
$post_title      = get_the_title();
$post_id         = get_the_ID();
$post_desc       = get_the_excerpt() ?: get_the_content();
$description        = wp_trim_words( strip_shortcodes( $post_desc ), $settings['eael_post_excerpt'], '<a class="eael_post_excerpt_read_more" href="' . get_the_permalink() . '"'. ( $settings['read_more_link_nofollow'] ? 'rel="nofollow"' : '' ) . '' . ( $settings['read_more_link_target_blank'] ? 'target="_blank"' : '' ) .'> ' . $settings['eael_post_excerpt_read_more'] . '</a>');
$image_clickable = 'yes' === $settings['eael_dfg_full_image_clickable'] && $settings['eael_fg_grid_style'] == 'eael-cards';
$item_content = '';

if( 'attachment' === get_post_type() ){
    $attachment = get_post( $post_id );
    $post_title = $attachment->post_title;
}

if( $settings['eael_show_hover_title'] ) {
    $item_content .= '<h2 class="title"><a href="' . esc_url( $post_url ) . '"'. ( $settings['title_link_nofollow'] ? ' rel="nofollow"' : '' ) . '' . ( $settings['title_link_target_blank'] ? ' target="_blank"' : '' ) .'>' . $post_title . '</a></h2>';
}
if($settings['eael_show_hover_excerpt']) {
    $item_content .= '<p>' . $description . '</p>';
}
?>
<div class="dynamic-gallery-item <?php echo esc_attr( urldecode( implode(' ', $classes ) ) ) ?>">
    <div class="dynamic-gallery-item-inner" data-itemid="<?php echo esc_attr( $post_id ); ?>">

        <?php if ( $image_clickable ){ ?>
            <a href="<?php echo esc_url( $post_url ); ?>"<?php echo ( $settings['image_link_nofollow'] ? ' rel="nofollow"' : '' ) . '' . ( $settings['image_link_target_blank'] ? ' target="_blank"' : '' ); ?>>
        <?php } ?>
        <div class="dynamic-gallery-thumbnail">
            <?php
            echo '<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt_text ) . '">';
            if ('media' == $settings['eael_fg_show_popup_styles'] && 'eael-none' == $settings['eael_fg_grid_hover_style']) {
                echo '<a href="'. esc_url( $popup_thumb_url ) .'" class="popup-only-media eael-magnific-link" data-elementor-open-lightbox="yes"></a>';
            }

            if ('eael-none' !== $settings['eael_fg_grid_hover_style'] && ! $image_clickable ) {
                if ('media' == $settings['eael_fg_show_popup_styles']) {
                    echo '<div class="caption media-only-caption">';
                } else {
                    echo '<div class="caption ' . esc_attr($settings['eael_fg_grid_hover_style']) . ' ">';
                }
                if ('true' == $settings['eael_fg_show_popup']) {
                    if ('media' == $settings['eael_fg_show_popup_styles']) {
                        echo '<a href="'. esc_url( $popup_thumb_url ) .'" class="popup-media eael-magnific-link" data-elementor-open-lightbox="yes"></a>';
                    } elseif ('buttons' == $settings['eael_fg_show_popup_styles']) {
                        echo '<div class="buttons">';
                            if ( ! empty( $settings['eael_section_fg_zoom_icon'] ) ) {
                                echo  '<a href="'. esc_url( $popup_thumb_url ) .'" class="eael-magnific-link" data-elementor-open-lightbox="yes">';

                                    if( isset( $settings['eael_section_fg_zoom_icon']['url'] ) ) {
                                        echo '<img class="eael-dnmcg-svg-icon" src="'.esc_url($settings['eael_section_fg_zoom_icon']['url']).'" alt="'.esc_attr(get_post_meta($settings['eael_section_fg_zoom_icon']['id'], '_wp_attachment_image_alt', true)).'" />';
                                    }else if ( ! empty( $settings['eael_section_fg_zoom_icon_new'] ) ) {
                                        \Elementor\Icons_Manager::render_icon($settings['eael_section_fg_zoom_icon_new'], ['aria-hidden' => 'true']);
                                    }else {
                                        echo '<i class="' . esc_attr($settings['eael_section_fg_zoom_icon']) . '"></i>';
                                    }
                                echo '</a>';
                            }

                            if ( ! empty( $settings['eael_section_fg_link_icon'] ) ) {
                                echo  '<a href="' . esc_url( $post_url ) . '"'. ( $settings['link_nofollow'] ? 'rel="nofollow"' : '' ) . '' . ( $settings['link_target_blank'] ? 'target="_blank"' : '' ) .'>';
                                    if( isset( $settings['eael_section_fg_link_icon']['url'] ) ) {
                                        echo '<img class="eael-dnmcg-svg-icon" src="'.esc_url($settings['eael_section_fg_link_icon']['url']).'" alt="'.esc_attr(get_post_meta($settings['eael_section_fg_link_icon']['id'], '_wp_attachment_image_alt', true)).'" />';
                                    }else if ( ! empty( $settings['eael_section_fg_link_icon_new'] ) ) {
                                        \Elementor\Icons_Manager::render_icon($settings['eael_section_fg_link_icon_new'], ['aria-hidden' => 'true']);
                                    }else {
                                        echo '<i class="' . esc_attr($settings['eael_section_fg_link_icon']) . '"></i>';
                                    }
                                echo '</a>';
                            }
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
            ?>
        </div>

        <?php if ( $image_clickable ){ ?>
            </a>
        <?php } ?>

        <div class="item-content">
            <?php 
            echo $item_content ? wp_kses( $item_content, Helper::eael_allowed_tags() ) : '';
            
            if (('buttons' == $settings['eael_fg_show_popup_styles']) && ('eael-none' == $settings['eael_fg_grid_hover_style'])) { ?>
                <div class="buttons entry-footer-buttons">
                    <?php if (!empty($settings['eael_section_fg_zoom_icon'])) {
                        ?>
                        <a href="<?php echo esc_url( $popup_thumb_url ); ?>" class="eael-magnific-link" data-elementor-open-lightbox="yes"><i class="<?php echo esc_attr($settings['eael_section_fg_zoom_icon']); ?>"></i></a>
                    <?php } ?>
                    <?php if (!empty($settings['eael_section_fg_link_icon'])) { ?>
                        <a href="<?php echo esc_url(  $post_url ); ?>"<?php echo ( $settings['link_nofollow'] ? ' rel="nofollow"' : '' ) . '' . ( $settings['link_target_blank'] ? ' target="_blank"' : '' ); ?>><i class="<?php echo esc_attr($settings['eael_section_fg_link_icon']); ?>"></i></a>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
