<?php

if (!defined('ABSPATH')) {
    exit;
}

function qlkh_customer_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'khach_hang';

    // Search and Filter Params
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $status_filter = isset($_GET['trang_thai']) ? sanitize_text_field($_GET['trang_thai']) : '';

    $where = array("1=1");
    if (!empty($search)) {
        $where[] = $wpdb->prepare("(ho_ten LIKE %s OR email LIKE %s OR so_dien_thoai LIKE %s)", "%$search%", "%$search%", "%$search%");
    }
    if (!empty($status_filter)) {
        $where[] = $wpdb->prepare("trang_thai = %s", $status_filter);
    }
    $where_sql = implode(" AND ", $where);

    $customers = $wpdb->get_results("SELECT * FROM $table_name WHERE $where_sql ORDER BY id DESC");

    // Stats calculations
    $total_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $today_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE DATE(ngay_dang_ky) = CURDATE()");
    $vip_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE loai_khach = 'VIP'");
    $lienhe_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE trang_thai = 'lien_he'");

    // Export CSV logic
    if (isset($_GET['action']) && $_GET['action'] === 'qlkh_export_csv') {
        if (!current_user_can('manage_options')) return;
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=danh_sach_khach_hang_' . date('Y-m-d') . '.csv');
        $output = fopen('php://output', 'w');
        fputs($output, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF)));
        fputcsv($output, array('ID', 'Họ Tên', 'Email', 'Số Điện Thoại', 'Địa Chỉ', 'Phân Loại', 'Trạng Thái', 'Ngày Đăng Ký'));
        foreach ($customers as $c) {
            fputcsv($output, array($c->id, $c->ho_ten, $c->email, $c->so_dien_thoai, $c->dia_chi, $c->loai_khach, $c->trang_thai, $c->ngay_dang_ky));
        }
        fclose($output);
        exit;
    }
    ?>

    <div class="qlkh-admin-wrap">
        <!-- Header -->
        <div class="qlkh-header">
            <div>
                <h1><span class="dashicons dashicons-groups"></span> Quản Lý Khách Hàng</h1>
                <p>Danh sách và công cụ quản trị thông tin khách hàng tiềm năng</p>
            </div>
            <div class="qlkh-header-actions">
                <button type="button" class="qlkh-btn qlkh-btn-primary" id="qlkh-btn-add-new">
                    <span class="dashicons dashicons-plus-alt2"></span> Thêm Khách Hàng
                </button>
                <a href="<?php echo add_query_arg(array('action' => 'qlkh_export_csv')); ?>" class="qlkh-btn qlkh-btn-secondary">
                    <span class="dashicons dashicons-download"></span> Xuất Excel/CSV
                </a>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="qlkh-stats-grid">
            <div class="qlkh-stat-card">
                <div class="qlkh-stat-icon blue"><span class="dashicons dashicons-admin-users"></span></div>
                <div class="qlkh-stat-info">
                    <h3><?php echo esc_html($total_count); ?></h3>
                    <p>Tổng số khách hàng</p>
                </div>
            </div>
            <div class="qlkh-stat-card">
                <div class="qlkh-stat-icon green"><span class="dashicons dashicons-calendar-alt"></span></div>
                <div class="qlkh-stat-info">
                    <h3><?php echo esc_html($today_count); ?></h3>
                    <p>Đăng ký hôm nay</p>
                </div>
            </div>
            <div class="qlkh-stat-card">
                <div class="qlkh-stat-icon purple"><span class="dashicons dashicons-star-filled"></span></div>
                <div class="qlkh-stat-info">
                    <h3><?php echo esc_html($vip_count); ?></h3>
                    <p>Khách hàng VIP</p>
                </div>
            </div>
            <div class="qlkh-stat-card">
                <div class="qlkh-stat-icon orange"><span class="dashicons dashicons-phone"></span></div>
                <div class="qlkh-stat-info">
                    <h3><?php echo esc_html($lienhe_count); ?></h3>
                    <p>Đã liên hệ</p>
                </div>
            </div>
        </div>

        <!-- Toolbar & Filter -->
        <div class="qlkh-toolbar">
            <form method="get" class="qlkh-search-box">
                <input type="hidden" name="page" value="qlkh-khach-hang" />
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Tìm tên, email, sđt..." />
                <select name="trang_thai" onchange="this.form.submit()">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="moi" <?php selected($status_filter, 'moi'); ?>>Mới đăng ký</option>
                    <option value="lien_he" <?php selected($status_filter, 'lien_he'); ?>>Đã liên hệ</option>
                    <option value="vip" <?php selected($status_filter, 'vip'); ?>>Khách VIP</option>
                    <option value="tam_ngung" <?php selected($status_filter, 'tam_ngung'); ?>>Tạm ngưng</option>
                </select>
                <button type="submit" class="qlkh-btn qlkh-btn-secondary"><span class="dashicons dashicons-search"></span> Lọc</button>
            </form>
        </div>

        <!-- Data Table -->
        <div class="qlkh-table-container">
            <table class="qlkh-table">
                <thead>
                    <tr>
                        <th>Họ & Tên</th>
                        <th>Email</th>
                        <th>Số điện thoại</th>
                        <th>Địa chỉ</th>
                        <th>Loại KH</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng ký</th>
                        <th style="text-align: right;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $customer): ?>
                        <tr id="qlkh-row-<?php echo esc_attr($customer->id); ?>">
                            <td>
                                <div class="qlkh-customer-name-cell">
                                    <div class="qlkh-customer-avatar">
                                        <?php echo esc_html(mb_substr($customer->ho_ten, 0, 1, 'UTF-8')); ?>
                                    </div>
                                    <div>
                                        <strong><?php echo esc_html($customer->ho_ten); ?></strong>
                                    </div>
                                </div>
                            </td>
                            <td><a href="mailto:<?php echo esc_attr($customer->email); ?>"><?php echo esc_html($customer->email); ?></a></td>
                            <td><a href="tel:<?php echo esc_attr($customer->so_dien_thoai); ?>"><?php echo esc_html($customer->so_dien_thoai); ?></a></td>
                            <td><?php echo esc_html($customer->dia_chi ? $customer->dia_chi : '---'); ?></td>
                            <td>
                                <span class="qlkh-badge <?php echo $customer->loai_khach === 'VIP' ? 'qlkh-badge-vip' : 'qlkh-badge-moi'; ?>">
                                    <?php echo esc_html($customer->loai_khach ? $customer->loai_khach : 'Thường'); ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                $badge_class = 'qlkh-badge-moi';
                                $status_text = 'Mới đăng ký';
                                if ($customer->trang_thai === 'lien_he') { $badge_class = 'qlkh-badge-lien_he'; $status_text = 'Đã liên hệ'; }
                                elseif ($customer->trang_thai === 'vip') { $badge_class = 'qlkh-badge-vip'; $status_text = 'Khách VIP'; }
                                elseif ($customer->trang_thai === 'tam_ngung') { $badge_class = 'qlkh-badge-tam_ngung'; $status_text = 'Tạm ngưng'; }
                                ?>
                                <span class="qlkh-badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                            <td><?php echo esc_html(date('H:i d/m/Y', strtotime($customer->ngay_dang_ky))); ?></td>
                            <td style="text-align: right;">
                                <button type="button" class="qlkh-btn qlkh-btn-secondary qlkh-btn-sm qlkh-btn-edit"
                                    data-id="<?php echo esc_attr($customer->id); ?>"
                                    data-name="<?php echo esc_attr($customer->ho_ten); ?>"
                                    data-email="<?php echo esc_attr($customer->email); ?>"
                                    data-phone="<?php echo esc_attr($customer->so_dien_thoai); ?>"
                                    data-address="<?php echo esc_attr($customer->dia_chi); ?>"
                                    data-status="<?php echo esc_attr($customer->trang_thai); ?>"
                                    data-category="<?php echo esc_attr($customer->loai_khach); ?>"
                                    data-notes="<?php echo esc_attr($customer->ghi_chu); ?>">
                                    <span class="dashicons dashicons-edit"></span> Sửa
                                </button>
                                <button type="button" class="qlkh-btn qlkh-btn-danger qlkh-btn-sm qlkh-btn-delete" data-id="<?php echo esc_attr($customer->id); ?>">
                                    <span class="dashicons dashicons-trash"></span> Xóa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">
                            <div class="qlkh-empty-state">
                                <span class="dashicons dashicons-id-alt" style="font-size: 40px; width: 40px; height: 40px; color: #94a3b8;"></span>
                                <p>Chưa có dữ liệu khách hàng nào phù hợp.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="qlkh-modal-backdrop" id="qlkh-edit-modal">
        <div class="qlkh-modal">
            <div class="qlkh-modal-header">
                <h3>Chỉnh Sửa Thông Tin Khách Hàng</h3>
                <button type="button" class="qlkh-modal-close">&times;</button>
            </div>
            <form id="qlkh-edit-form">
                <div class="qlkh-modal-body">
                    <input type="hidden" name="id" id="qlkh-edit-id" />
                    <div class="qlkh-form-group">
                        <label>Họ tên khách hàng</label>
                        <input type="text" name="ho_ten" id="qlkh-edit-name" required />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Email</label>
                        <input type="email" name="email" id="qlkh-edit-email" required />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Số điện thoại</label>
                        <input type="text" name="so_dien_thoai" id="qlkh-edit-phone" required />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="dia_chi" id="qlkh-edit-address" />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Phân loại khách hàng</label>
                        <select name="loai_khach" id="qlkh-edit-category">
                            <option value="Thuong">Khách thường</option>
                            <option value="VIP">Khách VIP</option>
                        </select>
                    </div>
                    <div class="qlkh-form-group">
                        <label>Trạng thái chăm sóc</label>
                        <select name="trang_thai" id="qlkh-edit-status">
                            <option value="moi">Mới đăng ký</option>
                            <option value="lien_he">Đã liên hệ</option>
                            <option value="vip">Khách VIP</option>
                            <option value="tam_ngung">Tạm ngưng</option>
                        </select>
                    </div>
                    <div class="qlkh-form-group">
                        <label>Ghi chú</label>
                        <textarea name="ghi_chu" id="qlkh-edit-notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="qlkh-modal-footer">
                    <button type="button" class="qlkh-btn qlkh-btn-secondary qlkh-modal-cancel">Hủy</button>
                    <button type="submit" class="qlkh-btn qlkh-btn-primary">Lưu Thay Đổi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Modal -->
    <div class="qlkh-modal-backdrop" id="qlkh-add-modal">
        <div class="qlkh-modal">
            <div class="qlkh-modal-header">
                <h3>Thêm Khách Hàng Mới</h3>
                <button type="button" class="qlkh-modal-close">&times;</button>
            </div>
            <form id="qlkh-add-form">
                <div class="qlkh-modal-body">
                    <div class="qlkh-form-group">
                        <label>Họ tên khách hàng *</label>
                        <input type="text" name="ho_ten" required />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Email *</label>
                        <input type="email" name="email" required />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Số điện thoại *</label>
                        <input type="text" name="so_dien_thoai" required />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Địa chỉ</label>
                        <input type="text" name="dia_chi" />
                    </div>
                    <div class="qlkh-form-group">
                        <label>Phân loại khách hàng</label>
                        <select name="loai_khach">
                            <option value="Thuong">Khách thường</option>
                            <option value="VIP">Khách VIP</option>
                        </select>
                    </div>
                    <div class="qlkh-form-group">
                        <label>Trạng thái</label>
                        <select name="trang_thai">
                            <option value="moi">Mới đăng ký</option>
                            <option value="lien_he">Đã liên hệ</option>
                            <option value="vip">Khách VIP</option>
                        </select>
                    </div>
                </div>
                <div class="qlkh-modal-footer">
                    <button type="button" class="qlkh-btn qlkh-btn-secondary qlkh-modal-cancel">Hủy</button>
                    <button type="submit" class="qlkh-btn qlkh-btn-primary">Tạo Khách Hàng</button>
                </div>
            </form>
        </div>
    </div>
    <?php
}

// AJAX Update Customer
add_action('wp_ajax_qlkh_update_customer', 'qlkh_ajax_update_customer');
function qlkh_ajax_update_customer() {
    check_ajax_referer('qlkh_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Không có quyền thực hiện.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'khach_hang';

    $id = intval($_POST['id']);
    $ho_ten = sanitize_text_field($_POST['ho_ten']);
    $email = sanitize_email($_POST['email']);
    $so_dien_thoai = sanitize_text_field($_POST['so_dien_thoai']);
    $dia_chi = sanitize_text_field($_POST['dia_chi']);
    $loai_khach = sanitize_text_field($_POST['loai_khach']);
    $trang_thai = sanitize_text_field($_POST['trang_thai']);
    $ghi_chu = sanitize_textarea_field($_POST['ghi_chu']);

    $wpdb->update(
        $table_name,
        array(
            'ho_ten' => $ho_ten,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'dia_chi' => $dia_chi,
            'loai_khach' => $loai_khach,
            'trang_thai' => $trang_thai,
            'ghi_chu' => $ghi_chu
        ),
        array('id' => $id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s'),
        array('%d')
    );

    wp_send_json_success(array('message' => 'Cập nhật thông tin khách hàng thành công!'));
}

// AJAX Add Customer from Admin
add_action('wp_ajax_qlkh_add_customer', 'qlkh_ajax_add_customer');
function qlkh_ajax_add_customer() {
    check_ajax_referer('qlkh_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Không có quyền thực hiện.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'khach_hang';

    $ho_ten = sanitize_text_field($_POST['ho_ten']);
    $email = sanitize_email($_POST['email']);
    $so_dien_thoai = sanitize_text_field($_POST['so_dien_thoai']);
    $dia_chi = sanitize_text_field($_POST['dia_chi']);
    $loai_khach = sanitize_text_field($_POST['loai_khach']);
    $trang_thai = sanitize_text_field($_POST['trang_thai']);

    $wpdb->insert(
        $table_name,
        array(
            'ho_ten' => $ho_ten,
            'email' => $email,
            'so_dien_thoai' => $so_dien_thoai,
            'dia_chi' => $dia_chi,
            'loai_khach' => $loai_khach,
            'trang_thai' => $trang_thai
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );

    wp_send_json_success(array('message' => 'Thêm khách hàng thành công!'));
}

// AJAX Delete Customer
add_action('wp_ajax_qlkh_delete_customer', 'qlkh_ajax_delete_customer');
function qlkh_ajax_delete_customer() {
    check_ajax_referer('qlkh_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Không có quyền thực hiện.'));
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'khach_hang';
    $id = intval($_POST['id']);

    $wpdb->delete($table_name, array('id' => $id), array('%d'));

    wp_send_json_success(array('message' => 'Xóa khách hàng thành công!'));
}