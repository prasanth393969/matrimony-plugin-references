<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\Code;

class CodeTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function htmlToPhp()
    {
        $html = [
            // WordPress standard tags
            "<div>Ciao da {{wp_title}}, che ne dici?</div>",
            "<div>{{wp_content}}</div>",
            "<div><h3>{{wp_title}}</h3><p>{{wp_excerpt}}</p></div>",
            "{{wp_author}}",
            "{{wp_thumbnail}}",
            "{{wp_thumbnail format=\"thumbnail\"}}",
            "{{wp_thumbnail format=\"medium\"}}",
            "{{wp_thumbnail format=\"large\"}}",
            "{{wp_thumbnail format=\"full\"}}",
            "{{wp_date}}",
            "{{wp_date format=\"m/d/y\"}}",
            "{{wp_date format=\"d M Y\"}}",
            "{{wp_permalink}}",
            "{{wp_permalink anchor=\"Leggi tutto\"}}",
            "{{wp_permalink anchor=\"Leggi tutto\" target=\"_blank\"}}",
            "{{wp_taxonomy}}",
            "{{wp_navigation_links}}",
            '{{acpt_breadcrumbs}}',
            "{{acpt_breadcrumbs separator=\"raquo\"}}",
            "{{acpt_breadcrumbs separator=\"gt\"}}",
            "{{acpt_breadcrumbs separator=\"/\"}}",

            // WooCommerce tags
            '{{wc_breadcrumb}}',
            '{{wc_sale_flash}}',
            '{{wc_product_images}}',
            '{{wc_product_thumbnails}}',
            '{{wc_product_sku}}',
            '{{wc_product_title}}',
            '{{wc_product_rating}}',
            '{{wc_product_price}}',
            '{{wc_product_excerpt}}',
            '{{wc_add_to_cart}}',
            '{{wc_sharing}}',
            '{{wc_product_meta}}',
//            '<wc-product-summary>{{wp_title}}</wc-product-summary>',
//            '<wp-before-main-content>{{wp_title}}</wp-before-main-content>',
//            '<wc-before-product-summary>{{wp_title}}</wc-before-product-summary>',
//            '<wc-after-product-summary>{{wp_title}}</wc-after-product-summary>',

            // Theme tags
            "{{template_part=\"sidebar-1\"}}",
            "{{header}}",
            "{{header name=\"shop\"}}",
            "{{footer}}",
            "{{footer name=\"shop\"}}",

            // ACPT tags
            '{{acpt box="box" field="link" target="_blank"}}',
            '{{acpt box="box" field="row" elements="6"}}',
            '{{acpt box="box" field="img" width="100" height="100"}}',
            '{{acpt box="box" field="date" date-format="d/m/Y"}}',
            'Insert your text here {{acpt box="info" field="prova"}} Insert your text here {{acpt box="info" field="email"}}',
            '{{acpt box="box" field="date" parent="repe"}}',
            '{{acpt_tax box="box" field="date" parent="repe"}}',
            '{{acpt_tax box="box" field="link" target="_blank"}}',

	        // ACPT loops
	        '<acpt-loop belongs_to="customPostType" find="movie" pagination="1" per_page="9" order_by="title" sort_by="ASC" id="iy8g"><p>Drag your loop component(s) here</p></acpt-loop>',
	        '<acpt-tax-loop belongs_to="taxonomy" find="cat" pagination="1" per_page="9" order_by="title" sort_by="ASC" id="iy8g"><p>Drag your loop component(s) here</p></acpt-tax-loop>',
	        '<acpt-field-loop belongs_to="meta_field" find="6616650d-101a-40a5-a986-ba567acc194e" pagination="1" per_page="9" order_by="title" sort_by="ASC" id="iy8g"><p>Drag your loop component(s) here</p></acpt-field-loop>',
        ];

        $php = [
            // WordPress standard tags
            "<div>Ciao da [wp_title], che ne dici?</div>",
            '<div>[wp_content]</div>',
            "<div><h3>[wp_title]</h3><p>[wp_excerpt]</p></div>",
            "[wp_author]",
            "[wp_thumbnail]",
            "[wp_thumbnail format=\"thumbnail\"]",
            "[wp_thumbnail format=\"medium\"]",
            "[wp_thumbnail format=\"large\"]",
            "[wp_thumbnail format=\"full\"]",
            "[wp_date]",
            "[wp_date format=\"m/d/y\"]",
            "[wp_date format=\"d M Y\"]",
            '[wp_permalink]',
            "[wp_permalink anchor=\"Leggi tutto\"]",
            "[wp_permalink anchor=\"Leggi tutto\" target=\"_blank\"]",
            "<?php do_action(\"acpt_taxonomy_links\"); ?>",
            "<?php do_action(\"acpt_prev_next_links\"); ?>",
            "<?php do_action(\"acpt_breadcrumb\"); ?>",
            "<?php do_action(\"acpt_breadcrumb\", \"raquo\"); ?>",
            "<?php do_action(\"acpt_breadcrumb\", \"gt\"); ?>",
            "<?php do_action(\"acpt_breadcrumb\", \"/\"); ?>",

            // WooCommerce tags
            "<?php add_action('woocommerce_before_main_content', 'woocommerce_breadcrumb'); ?>",
            "<?php add_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash'); ?>",
            "<?php add_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images'); ?>",
            "<?php add_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_thumbnails'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_sku'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_title'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_price'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing'); ?>",
            "<?php add_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta'); ?>",
/*            '<?php while ( have_posts() ) : the_post(); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_title\', 5 , 0); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_rating\', 10 , 0); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_price\', 10 , 0); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_excerpt\', 20 , 0); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_add_to_cart\', 30 , 0); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_meta\', 40 , 0); remove_action( \'woocommerce_single_product_summary\', \'woocommerce_template_single_sharing\', 50 , 0); ?><?php echo get_the_title(get_the_ID()); ?><?php do_action( \'woocommerce_single_product_summary\' ); endwhile; ?>',*/
/*            '<?php while ( have_posts() ) : the_post(); remove_action( \'woocommerce_before_single_product_summary\', \'woocommerce_show_product_sale_flash\', 10 , 0); remove_action( \'woocommerce_before_single_product_summary\', \'woocommerce_show_product_images\', 20 , 0); remove_action( \'woocommerce_before_single_product_summary\', \'woocommerce_show_product_thumbnails\', 10 , 0); ?><?php echo get_the_title(get_the_ID()); ?><?php do_action( \'woocommerce_before_single_product_summary\' ); endwhile; ?>',*/
/*            '<?php remove_action( \'woocommerce_before_main_content\', \'woocommerce_breadcrumb\', 20, 0 ); ?><?php echo get_the_title(get_the_ID()); ?><?php do_action( \'woocommerce_before_main_content\' ); ?>',*/
/*            '<?php while ( have_posts() ) : the_post(); remove_action( \'woocommerce_after_single_product_summary\', \'woocommerce_output_product_data_tabs\', 10 , 0); remove_action( \'woocommerce_after_single_product_summary\', \'woocommerce_upsell_display\', 15 , 0); remove_action( \'woocommerce_after_single_product_summary\', \'woocommerce_output_related_products\', 20 , 0); ?><?php echo get_the_title(get_the_ID()); ?><?php do_action( \'woocommerce_after_single_product_summary\' ); endwhile; ?>',*/

            // Theme tags
            "<?php dynamic_sidebar('sidebar-1'); ?>",
            "<?php get_header(); ?>",
            "<?php get_header('shop'); ?>",
            "<?php get_footer(); ?>",
            "<?php get_footer('shop'); ?>",

            // ACPT tags
            '[acpt box="box" field="link" target="_blank"]',
            '[acpt box="box" field="row" elements="6"]',
            '[acpt box="box" field="img" width="100" height="100"]',
            '[acpt box="box" field="date" date-format="d/m/Y"]',
            'Insert your text here [acpt box="info" field="prova"] Insert your text here [acpt box="info" field="email"]',
            '[acpt box="box" field="date" parent="repe"]',
            '[acpt_tax box="box" field="date" parent="repe"]',
            '[acpt_tax box="box" field="link" target="_blank"]',

	        // ACPT loop
	        '[acpt_loop belongs_to="customPostType" find="movie" pagination="1" per_page="9" order_by="title" sort_by="ASC" id="iy8g"]<p>Drag your loop component(s) here</p>[/acpt_loop]',
	        '[acpt_tax_loop belongs_to="taxonomy" find="cat" pagination="1" per_page="9" order_by="title" sort_by="ASC" id="iy8g"]<p>Drag your loop component(s) here</p>[/acpt_tax_loop]',
	        '[acpt_field_loop belongs_to="meta_field" find="6616650d-101a-40a5-a986-ba567acc194e" pagination="1" per_page="9" order_by="title" sort_by="ASC" id="iy8g"]<p>Drag your loop component(s) here</p>[/acpt_field_loop]',
        ];

        foreach ($html as $index => $item){
            $this->assertEquals(Code::htmlToPhp($item), $php[$index]);
        }
    }
}