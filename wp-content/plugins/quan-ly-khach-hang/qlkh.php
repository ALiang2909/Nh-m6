<?php
/**
 * Plugin Name: Quản Lý Khách Hàng Premium
 * Plugin URI: https://wordpress.org/plugins/quan-ly-khach-hang
 * Description: Plugin chuyên nghiệp đăng ký, quản lý thông tin khách hàng, thống kê & chăm sóc khách hàng phía Dashboard.
 * Version: 2.0.0
 * Author: Nhom 6
 */

if (!defined('ABSPATH')) {
    exit;
}

define('QLKH_PATH', plugin_dir_path(__FILE__));
define('QLKH_URL', plugin_dir_url(__FILE__));

require_once QLKH_PATH . 'admin/menu.php';
require_once QLKH_PATH . 'admin/customer-list.php';
require_once QLKH_PATH . 'public/register-form.php';

register_activation_hook(__FILE__, 'qlkh_create_database');

function qlkh_create_database() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'khach_hang';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        ho_ten VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        so_dien_thoai VARCHAR(20) NOT NULL,
        dia_chi TEXT,
        trang_thai VARCHAR(50) DEFAULT 'moi',
        loai_khach VARCHAR(50) DEFAULT 'Thuong',
        ghi_chu TEXT,
        ngay_dang_ky DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}

// Enqueue Admin Assets
add_action('admin_enqueue_scripts', 'qlkh_enqueue_admin_assets');
function qlkh_enqueue_admin_assets($hook) {
    if (strpos($hook, 'qlkh') === false) {
        return;
    }

    wp_enqueue_style('qlkh-admin-css', QLKH_URL . 'assets/css/admin.css', array(), '2.0.0');
    wp_enqueue_script('qlkh-admin-js', QLKH_URL . 'assets/js/admin.js', array('jquery'), '2.0.0', true);

    wp_localize_script('qlkh-admin-js', 'qlkh_admin_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('qlkh_admin_nonce')
    ));
}

// Enqueue Public Assets
add_action('wp_enqueue_scripts', 'qlkh_enqueue_public_assets');
function qlkh_enqueue_public_assets() {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('qlkh-public-css', QLKH_URL . 'assets/css/public.css', array(), '2.0.0');
    wp_enqueue_script('qlkh-public-js', QLKH_URL . 'assets/js/public-ajax.js', array('jquery'), '2.0.0', true);

    wp_localize_script('qlkh-public-js', 'qlkh_public_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('qlkh_public_nonce')
    ));
}