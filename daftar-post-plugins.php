<?php
/**
 * Plugin Name: Daftar Post Plugins
 * Description: Menampilkan 5 post terbaru (judul, link, dan tanggal) dalam halaman khusus.
 * Version: 1.1
 * Author: Yohan R
 */

if (!defined('ABSPATH')) exit;

// Fungsi menampilkan daftar post
function dpp_generate_post_list() {
    $jumlah_post = get_option('dpp_jumlah_postingan', 5); // default 5
    $args = array(
        'post_type' => 'post',
        'posts_per_page' => intval($jumlah_post),
        'orderby' => 'date',
        'order' => 'DESC'
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $output = '<ul>';
        while ($query->have_posts()) {
            $query->the_post();
            $output .= '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a> - ' . get_the_date() . '</li>';
        }
        $output .= '</ul>';
        wp_reset_postdata();
    } else {
        $output = '<p>Tidak ada postingan ditemukan.</p>';
    }

    return $output;
}
add_shortcode('daftar_post', 'dpp_generate_post_list');

// Membuat halaman admin menu
function dpp_admin_menu() {
    add_menu_page(
        'Pengaturan Daftar Post',
        'Daftar Post',
        'manage_options',
        'dpp-settings',
        'dpp_settings_page',
        'dashicons-list-view',
        100
    );
}
add_action('admin_menu', 'dpp_admin_menu');

// Tampilan halaman setting admin
function dpp_settings_page() {
    ?>
    <div class="wrap">
        <h1>Pengaturan Daftar Post</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('dpp_settings_group');
                do_settings_sections('dpp-settings');
                submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Registrasi setting
function dpp_register_settings() {
    register_setting('dpp_settings_group', 'dpp_jumlah_postingan');

    add_settings_section('dpp_section', 'Pengaturan Tampilan', null, 'dpp-settings');

    add_settings_field(
        'dpp_jumlah_postingan_field',
        'Jumlah Postingan yang Ditampilkan',
        'dpp_jumlah_postingan_callback',
        'dpp-settings',
        'dpp_section'
    );
}
add_action('admin_init', 'dpp_register_settings');

function dpp_jumlah_postingan_callback() {
    $value = get_option('dpp_jumlah_postingan', 5);
    echo '<input type="number" name="dpp_jumlah_postingan" value="' . esc_attr($value) . '" min="1" max="100" />';
}

// Buat halaman otomatis saat plugin diaktifkan
function dpp_create_page_on_activation() {
    $page_title = 'Daftar Post';
    $page_check = get_page_by_title($page_title);

    if (!isset($page_check->ID)) {
        $page_data = array(
            'post_title'    => $page_title,
            'post_content'  => '[daftar_post]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
        );
        wp_insert_post($page_data);
    }

    // Set default jika belum ada
    if (get_option('dpp_jumlah_postingan') === false) {
        add_option('dpp_jumlah_postingan', 5);
    }
}
register_activation_hook(__FILE__, 'dpp_create_page_on_activation');