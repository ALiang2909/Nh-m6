<?php
/*
Plugin Name: Simple Promo Discount
Plugin URI: 
Description: Plugin tạo chương trình khuyến mãi theo thời gian và phần trăm giảm giá cho WooCommerce (Có nút hủy).
Version: 1.3
Author: Trợ lý AI
*/

// Ngăn chặn truy cập trực tiếp
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 1. TẠO MENU TRONG DASHBOARD
 */
add_action('admin_menu', 'spd_add_admin_menu');
function spd_add_admin_menu() {
    add_menu_page(
        'Khuyến Mãi',
        'Khuyến Mãi',
        'manage_options',
        'simple-promo-discount',
        'spd_admin_page_html',
        'dashicons-tickets-alt',
        56
    );
}

/**
 * 2. GIAO DIỆN CÀI ĐẶT TRONG ADMIN (Có nút Hủy)
 */
function spd_admin_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $error_msg = '';

    // Xử lý khi bấm nút "Lưu Thay Đổi"
    if (isset($_POST['spd_save_settings'])) {
        check_admin_referer('spd_save_promo_settings');
        
        $percent = sanitize_text_field($_POST['spd_discount_percent']);
        $start   = sanitize_text_field($_POST['spd_start_date']);
        $end     = sanitize_text_field($_POST['spd_end_date']);

        if (strtotime($end) <= strtotime($start)) {
            $error_msg = 'Lỗi: Thời gian kết thúc phải lớn hơn thời gian bắt đầu!';
        } else {
            update_option('spd_discount_percent', $percent);
            update_option('spd_start_date', $start);
            update_option('spd_end_date', $end);
            echo '<div class="notice notice-success is-dismissible"><p>Đã lưu cài đặt chương trình khuyến mãi!</p></div>';
        }
    }

    // Xử lý khi bấm nút "Hủy Khuyến Mãi"
    if (isset($_POST['spd_cancel_settings'])) {
        check_admin_referer('spd_save_promo_settings');
        
        // Xóa dữ liệu trong database
        delete_option('spd_discount_percent');
        delete_option('spd_start_date');
        delete_option('spd_end_date');
        
        echo '<div class="notice notice-info is-dismissible"><p>Đã hủy chương trình khuyến mãi thành công!</p></div>';
    }

    if (!empty($error_msg)) {
        echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error_msg) . '</p></div>';
    }

    // Lấy dữ liệu cũ để hiển thị ra form
    $percent    = get_option('spd_discount_percent', '');
    $start_date = get_option('spd_start_date', '');
    $end_date   = get_option('spd_end_date', '');

    $current_datetime = current_time('Y-m-d\TH:i');
    ?>
    
    <div class="wrap">
        <h1>Cài Đặt Chương Trình Khuyến Mãi</h1>
        <hr>
        <form method="POST" action="">
            <?php wp_nonce_field('spd_save_promo_settings'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="spd_discount_percent">Phần trăm giảm giá (%)</label></th>
                    <td>
                        <input type="number" name="spd_discount_percent" id="spd_discount_percent" value="<?php echo esc_attr($percent); ?>" class="regular-text" min="0" max="100" step="0.1" required />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="spd_start_date">Thời gian bắt đầu</label></th>
                    <td>
                        <input type="datetime-local" name="spd_start_date" id="spd_start_date" value="<?php echo esc_attr($start_date); ?>" min="<?php echo esc_attr($current_datetime); ?>" class="regular-text" required />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="spd_end_date">Thời gian kết thúc</label></th>
                    <td>
                        <input type="datetime-local" name="spd_end_date" id="spd_end_date" value="<?php echo esc_attr($end_date); ?>" min="<?php echo esc_attr($current_datetime); ?>" class="regular-text" required />
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="spd_save_settings" class="button button-primary" value="Lưu Thay Đổi">
                <!-- Nút Hủy Khuyến Mãi -->
                <input type="submit" name="spd_cancel_settings" class="button button-secondary" value="Hủy Khuyến Mãi" onclick="return confirm('Bạn có chắc chắn muốn hủy chương trình khuyến mãi hiện tại không?');" style="margin-left: 10px; color: #b32d2e; border-color: #b32d2e;">
            </p>
        </form>
    </div>
    
    <?php
}

/**
 * 3. HÀM KIỂM TRA THỜI GIAN KHUYẾN MÃI
 */
function spd_is_promo_active() {
    $percent    = get_option('spd_discount_percent');
    $start_date = get_option('spd_start_date');
    $end_date   = get_option('spd_end_date');

    if (empty($percent) || empty($start_date) || empty($end_date)) {
        return false;
    }

    $current_date_str = current_time('Y-m-d\TH:i');

    if ($current_date_str >= $start_date && $current_date_str <= $end_date) {
        return true;
    }

    return false;
}

/**
 * 4. ÁP DỤNG GIẢM GIÁ CHO SẢN PHẨM WOOCOMMERCE
 */
add_filter('woocommerce_product_get_price', 'spd_apply_discount_to_products', 10, 2);
add_filter('woocommerce_product_variation_get_price', 'spd_apply_discount_to_products', 10, 2);
function spd_apply_discount_to_products($price, $product) {
    if (is_admin()) return $price;

    if (spd_is_promo_active()) {
        $percent = (float) get_option('spd_discount_percent');
        if ($percent > 0 && $percent <= 100 && $price > 0) {
            return $price - ($price * ($percent / 100));
        }
    }
    return $price;
}

/**
 * 5. HIỂN THỊ GIÁ GỐC BỊ GẠCH NGANG TRÊN TRANG SẢN PHẨM
 */
add_filter('woocommerce_get_price_html', 'spd_show_sale_price_html', 10, 2);
function spd_show_sale_price_html($price_html, $product) {
    if (is_admin() || !spd_is_promo_active()) return $price_html;

    $regular_price = $product->get_regular_price();
    if (empty($regular_price)) $regular_price = $product->get_price();

    $percent = (float) get_option('spd_discount_percent');
    if ($percent > 0 && $percent <= 100 && $regular_price > 0) {
        $sale_price = $regular_price - ($regular_price * ($percent / 100));
        $price_html = wc_format_sale_price(wc_get_price_to_display($product, array('price' => $regular_price)), wc_get_price_to_display($product, array('price' => $sale_price))) . $product->get_price_suffix();
    }
    return $price_html;
}

/**
 * 6. HIỂN THỊ THÔNG BÁO KHUYẾN MÃI Ở TRANG CHỦ (INDEX)
 */
add_action('wp_footer', 'spd_show_promo_banner_on_index');
function spd_show_promo_banner_on_index() {
    if (!is_front_page() && !is_home()) {
        return;
    }

    $percent    = get_option('spd_discount_percent');
    $start_date = get_option('spd_start_date');
    $end_date   = get_option('spd_end_date');

    if (empty($percent) || empty($start_date) || empty($end_date)) {
        return;
    }

    $current_date_str = current_time('Y-m-d\TH:i');

    if ($current_date_str > $end_date) {
        return;
    }

    $format_start = date_i18n('H:i d/m/Y', strtotime($start_date));
    $format_end   = date_i18n('H:i d/m/Y', strtotime($end_date));

    if ($current_date_str >= $start_date) {
        $status_text = "Đang diễn ra!";
        $bg_color = "#ff3366"; 
    } else {
        $status_text = "Sắp diễn ra!";
        $bg_color = "#ff9900"; 
    }

    ?>
    <style>
        .spd-promo-banner {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: <?php echo esc_attr($bg_color); ?>;
            color: #fff;
            text-align: center;
            padding: 15px 10px;
            z-index: 999999;
            font-size: 16px;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.2);
            font-family: sans-serif;
        }
        .spd-promo-banner strong {
            font-size: 18px;
            text-transform: uppercase;
        }
        body {
            padding-bottom: 60px !important; 
        }
    </style>
    
    <div class="spd-promo-banner">
        <strong>🔥 Siêu Khuyến Mãi <?php echo esc_html($percent); ?>% - <?php echo esc_html($status_text); ?></strong> 
        <br>
        <small>Thời gian: Từ <b><?php echo esc_html($format_start); ?></b> đến <b><?php echo esc_html($format_end); ?></b></small>
    </div>
    <?php
}