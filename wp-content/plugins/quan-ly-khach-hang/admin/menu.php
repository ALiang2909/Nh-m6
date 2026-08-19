<?php

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'qlkh_create_admin_menu');

function qlkh_create_admin_menu() {

    add_menu_page(
        'Quản lý khách hàng',
        'Khách hàng',
        'manage_options',
        'qlkh-khach-hang',
        'qlkh_customer_page',
        'dashicons-groups',
        25
    );

    add_submenu_page(
        'qlkh-khach-hang',
        'Danh sách khách hàng',
        'Danh sách KH',
        'manage_options',
        'qlkh-khach-hang',
        'qlkh_customer_page'
    );
}