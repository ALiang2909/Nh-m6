<?php

if (!defined('ABSPATH')) {
    exit;
}

function qlkh_register_form() {
    ob_start();
    ?>

    <div class="qlkh-form-container">
        <div class="qlkh-form-header">
            <h3>ĐĂNG KÝ THÔNG TIN KHÁCH HÀNG</h3>
            <p>Vui lòng điền đầy đủ thông tin bên dưới để nhận tư vấn & ưu đãi lớn nhất</p>
        </div>

        <div class="qlkh-form-body">
            <div id="qlkh-form-response" style="display: none;"></div>

            <form id="qlkh-register-form">
                <div class="qlkh-input-group">
                    <label>Họ và tên *</label>
                    <div class="qlkh-input-wrapper">
                        <span class="dashicons dashicons-admin-users"></span>
                        <input type="text" name="ho_ten" placeholder="Nhập họ và tên của bạn" required />
                    </div>
                </div>

                <div class="qlkh-input-group">
                    <label>Địa chỉ Email *</label>
                    <div class="qlkh-input-wrapper">
                        <span class="dashicons dashicons-email"></span>
                        <input type="email" name="email" placeholder="example@domain.com" required />
                    </div>
                </div>

                <div class="qlkh-input-group">
                    <label>Số điện thoại *</label>
                    <div class="qlkh-input-wrapper">
                        <span class="dashicons dashicons-phone"></span>
                        <input type="tel" name="so_dien_thoai" placeholder="0901 234 567" required />
                    </div>
                </div>

                <div class="qlkh-input-group">
                    <label>Địa chỉ nhận tư vấn</label>
                    <div class="qlkh-input-wrapper">
                        <textarea name="dia_chi" placeholder="Nhập địa chỉ nhà hoặc văn phòng..."></textarea>
                    </div>
                </div>

                <button type="submit" class="qlkh-submit-btn">
                    <span class="dashicons dashicons-send"></span> Gửi đăng ký ngay
                </button>
            </form>
        </div>
    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('dang_ky_khach_hang', 'qlkh_register_form');

// Handle Public AJAX Registration
add_action('wp_ajax_qlkh_submit_register', 'qlkh_handle_ajax_register');
add_action('wp_ajax_nopriv_qlkh_submit_register', 'qlkh_handle_ajax_register');

function qlkh_handle_ajax_register() {
    check_ajax_referer('qlkh_public_nonce', 'nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'khach_hang';

    $ho_ten = isset($_POST['ho_ten']) ? sanitize_text_field($_POST['ho_ten']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $so_dien_thoai = isset($_POST['so_dien_thoai']) ? sanitize_text_field($_POST['so_dien_thoai']) : '';
    $dia_chi = isset($_POST['dia_chi']) ? sanitize_textarea_field($_POST['dia_chi']) : '';

    if (empty($ho_ten) || empty($email) || empty($so_dien_thoai)) {
        wp_send_json_error(array('message' => 'Vui lòng điền đầy đủ các thông tin bắt buộc (*).'));
    }

    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Địa chỉ Email không đúng định dạng.'));
    }

    // Insert database
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'ho_ten' => $ho_ten,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'dia_chi' => $dia_chi,
            'trang_thai' => 'moi',
            'loai_khach' => 'Thuong'
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );

    if ($inserted) {
        wp_send_json_success(array('message' => 'Cảm ơn bạn! Đăng ký thông tin thành công. Chúng tôi sẽ liên hệ lại sớm nhất.'));
    } else {
        wp_send_json_error(array('message' => 'Lỗi kết nối cơ sở dữ liệu. Vui lòng thử lại sau.'));
    }
}