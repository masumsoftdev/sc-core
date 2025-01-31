<?php
/**
 * Plugin Name: SC Core
 * Plugin URI:  https://sockshow.com/
 * Description: A simple plugin to display WooCommerce products in a grid with a shortcode.
 * Version: 1.0.0
 * Author: Masum Billah
 * Author URI:  https://masum.anothermonk.com/
 * License: GPL2
 * Text Domain: sc-core
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode [sc_product_grid category="your-category-slug"]
function sc_product_grid_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'category' => '',
        ), 
        $atts, 
        'sc_product_grid'
    );

    if (empty($atts['category'])) {
        return '<p>Please provide a category.</p>';
    }

    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce is not activated.</p>';
    }

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => -1, 
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ),
        ),
    );

    $query = new WP_Query($args);
    if (!$query->have_posts()) {
        return '<p>No products found.</p>';
    }

    ob_start();
    ?>
    <div class="sc-product-grid">
        <?php while ($query->have_posts()) : $query->the_post(); 
            global $product;
            ?>
            <div class="sc-product-item">
                <a href="<?php the_permalink(); ?>">
                    <div class="sc-product-image">
                        <?php the_post_thumbnail('medium'); ?>
                        <?php if ($product->is_on_sale()) : ?>
                            <span class="sc-sale-badge">SALE!</span>
                            <span class="sc-discount-badge"><?php echo round(100 - ($product->get_sale_price() / $product->get_regular_price() * 100)); ?>%</span>
                        <?php endif; ?>
                    </div>
                    <h3 class="sc-product-title"><?php the_title(); ?></h3>
                    <p class="sc-price">
                        <span class="sc-regular-price"><?php echo wc_price($product->get_regular_price()); ?></span>
                        <span class="sc-sale-price"><?php echo wc_price($product->get_sale_price()); ?></span>
                    </p>
                </a>
            </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('sc_product_grid', 'sc_product_grid_shortcode');

// Enqueue CSS
function sc_core_enqueue_styles() {
    wp_enqueue_style('sc-core-styles', plugin_dir_url(__FILE__) . 'assets/sc-core.css', array(), time());
}
add_action('wp_enqueue_scripts', 'sc_core_enqueue_styles');



