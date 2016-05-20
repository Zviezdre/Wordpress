<?php

add_action( 'wp_enqueue_scripts', 'theme_css_js');
function theme_css_js() {
    wp_enqueue_script( 'jquery-min-js', get_template_directory_uri() . '/js/jquery-2.2.3.min.js',  array('jquery'), '2.2.3' ,  false );
    wp_enqueue_script('theme_js', get_template_directory_uri() . '/js/bootstrap.min.js', array('jquery'), '', true );
    wp_enqueue_script('bootstrap-js', get_template_directory_uri() . '/js/ie10-viewport-bug-workaround.js', array('jquery'), true);

    wp_enqueue_style( 'bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css' );
    wp_enqueue_style( 'styles-css', get_template_directory_uri() . '/css/styles.css' );
    wp_enqueue_style( 'main', get_template_directory_uri() . '/style.css' );

}
 


add_filter( 'woocommerce_product_tabs', 'wcs_woo_remove_reviews_tab', 98 );
function wcs_woo_remove_reviews_tab($tabs) {
    unset($tabs['reviews']);
    return $tabs;
}
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    show_admin_bar(false);
}
add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

function my_custom_post_concert() {
    $labels = array(
        'name'               => _x( 'Concerts', 'post type general name' ),
        'singular_name'      => _x( 'Concert', 'post type singular name' ),
        'add_new'            => _x( 'Add New', 'book' ),
        'add_new_item'       => __( 'Add New Concert' ),
        'edit_item'          => __( 'Edit Concert' ),
        'new_item'           => __( 'New Concert' ),
        'all_items'          => __( 'All Concerts' ),
        'view_item'          => __( 'View Concert' ),
        'search_items'       => __( 'Search Concerts' ),
        'not_found'          => __( 'No concerts found' ),
        'not_found_in_trash' => __( 'No concerts found in the Trash' ),
        'parent_item_colon'  => '',
        'menu_name'          => 'Concerts'
    );
    $args = array(
        'labels'        => $labels,
        'description'   => 'Holds our concerts specific data',
        'public'        => true,
        'menu_position' => 5,
        'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
        'has_archive'   => true,
    );
    register_post_type( 'concerts', $args );
}
add_action( 'init', 'my_custom_post_concert' );

function concert_updated_messages( $messages ) {
    global $post, $post_ID;
    $messages['concerts'] = array(
        0 => '',
        1 => sprintf( __('Concert updated. <a href="%s">View concert</a>'), esc_url( get_permalink($post_ID) ) ),
        2 => __('Custom field updated.'),
        3 => __('Custom field deleted.'),
        4 => __('Concert updated.'),
        5 => isset($_GET['revision']) ? sprintf( __('Concert restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
        6 => sprintf( __('Concert published. <a href="%s">View concert</a>'), esc_url( get_permalink($post_ID) ) ),
        7 => __('Concert saved.'),
        8 => sprintf( __('Concert submitted. <a target="_blank" href="%s">Preview concert</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        9 => sprintf( __('Concert scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview concert</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
        10 => sprintf( __('Concert draft updated. <a target="_blank" href="%s">Preview concert</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
    );
    return $messages;
}
add_filter( 'post_updated_messages', 'concert_updated_messages' );


function my_taxonomies_concerts() {
    $labels = array(
        'name'              => _x( 'Concert Categories', 'taxonomy general name' ),
        'singular_name'     => _x( 'Concert Category', 'taxonomy singular name' ),
        'search_items'      => __( 'Search Concert Categories' ),
        'all_items'         => __( 'All Concert Categories' ),
        'parent_item'       => __( 'Parent Concert Category' ),
        'parent_item_colon' => __( 'Parent Concert Category:' ),
        'edit_item'         => __( 'Edit Concert Category' ),
        'update_item'       => __( 'Update Concert Category' ),
        'add_new_item'      => __( 'Add New Concert Category' ),
        'new_item_name'     => __( 'New Concert Category' ),
        'menu_name'         => __( 'Concert Categories' ),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => true,
    );
    register_taxonomy( 'concert_category', 'concerts', $args );
}
add_action( 'init', 'my_taxonomies_concerts', 0 );


add_action( 'add_meta_boxes', 'concert_price_box' );
function concert_price_box() {
    add_meta_box(
        'concert_price_box',
        __( 'Concert Price', 'myplugin_textdomain' ),
        'concert_price_box_content',
        'concerts',
        'side',
        'high'
    );
}

function concert_price_box_content( $post ) {
    wp_nonce_field( plugin_basename( __FILE__ ), 'concert_price_box_content_nonce' );
    echo '<label for="concert_price"></label>';
    echo '<input type="text" id="concert_price" name="concert_price" placeholder="enter a price" />';
}


add_action( 'save_post', 'concert_price_box_save' );
function concert_price_box_save( $post_id ) {

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
        return;

    if ( !wp_verify_nonce( $_POST['concert_price_box_content_nonce'], plugin_basename( __FILE__ ) ) )
        return;

    if ( 'page' == $_POST['post_type'] ) {
        if ( !current_user_can( 'edit_page', $post_id ) )
            return;
    } else {
        if ( !current_user_can( 'edit_post', $post_id ) )
            return;
    }
    $product_price = $_POST['concert_price'];
    update_post_meta( $post_id, 'concert_price', $product_price );
}


add_action( 'wp_default_scripts', function( $scripts ) {
    if ( ! empty( $scripts->registered['jquery'] ) ) {
        $jquery_dependencies = $scripts->registered['jquery']->deps;
        $scripts->registered['jquery']->deps = array_diff( $jquery_dependencies, array( 'jquery-migrate' ) );
    }
} );