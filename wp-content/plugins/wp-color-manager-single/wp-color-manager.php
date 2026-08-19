<?php
/**
 * Plugin Name: WP Color Manager
 * Description: Quản lý màu sắc toàn bộ website từ WordPress Dashboard.
 * Version: 1.0.0
 * Author: Kha
 * License: GPL-2.0-or-later
 */

if (!defined('ABSPATH')) exit;

function wpcm_defaults() {
    return [
        'primary' => '#0073aa', 'secondary' => '#23282d', 'accent' => '#46b450',
        'background' => '#ffffff', 'surface' => '#f5f5f5', 'text' => '#333333',
        'heading' => '#111111', 'muted' => '#777777', 'link' => '#0073aa',
        'link_hover' => '#005177', 'button_text' => '#ffffff', 'border' => '#dddddd',
        'header_bg' => '#ffffff', 'footer_bg' => '#23282d', 'footer_text' => '#ffffff'
    ];
}

add_action('admin_menu', function () {
    add_menu_page('Website Colors', 'Website Colors', 'manage_options', 'wpcm-colors', 'wpcm_page', 'dashicons-art', 80);
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_wpcm-colors') return;
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
    wp_add_inline_style('wp-color-picker', '.wpcm-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:15px;max-width:1100px}.wpcm-card{background:#fff;border:1px solid #ddd;border-radius:8px;padding:16px}.wpcm-card label{display:block;font-weight:600;margin-bottom:8px}.wpcm-color{width:100px}.wpcm-preview{margin-top:20px;padding:20px;background:#fff;border:1px solid #ddd;border-radius:8px}');
    wp_add_inline_script('wp-color-picker', 'jQuery(function($){$(".wpcm-color").wpColorPicker();});');
});

add_action('admin_init', function () {
    if (!isset($_POST['wpcm_action']) || !current_user_can('manage_options')) return;
    check_admin_referer('wpcm_save');
    if ($_POST['wpcm_action'] === 'reset') {
        delete_option('wpcm_colors');
    } else {
        $colors = [];
        foreach (wpcm_defaults() as $key => $default) {
            $value = isset($_POST['wpcm_'.$key]) ? sanitize_hex_color(wp_unslash($_POST['wpcm_'.$key])) : false;
            $colors[$key] = $value ?: $default;
        }
        update_option('wpcm_colors', $colors);
    }
    wp_safe_redirect(admin_url('admin.php?page=wpcm-colors&saved=1'));
    exit;
});

function wpcm_page() {
    $defaults = wpcm_defaults();
    $colors = wp_parse_args(get_option('wpcm_colors', []), $defaults);
    $labels = [
        'primary'=>'Primary','secondary'=>'Secondary','accent'=>'Accent','background'=>'Background',
        'surface'=>'Surface','text'=>'Text','heading'=>'Heading','muted'=>'Muted Text','link'=>'Link',
        'link_hover'=>'Link Hover','button_text'=>'Button Text','border'=>'Border','header_bg'=>'Header Background',
        'footer_bg'=>'Footer Background','footer_text'=>'Footer Text'
    ];
    ?>
    <div class="wrap">
        <h1>🎨 Website Colors</h1>
        <?php if (isset($_GET['saved'])): ?><div class="notice notice-success is-dismissible"><p>Đã lưu thay đổi.</p></div><?php endif; ?>
        <p>Thay đổi bảng màu chính của website từ một nơi.</p>
        <form method="post">
            <?php wp_nonce_field('wpcm_save'); ?>
            <div class="wpcm-grid">
                <?php foreach ($labels as $key => $label): ?>
                    <div class="wpcm-card">
                        <label for="wpcm_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label>
                        <input class="wpcm-color" type="text" id="wpcm_<?php echo esc_attr($key); ?>" name="wpcm_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($colors[$key]); ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <p>
                <button class="button button-primary button-large" type="submit" name="wpcm_action" value="save">Lưu màu sắc</button>
                <button class="button button-large" type="submit" name="wpcm_action" value="reset" onclick="return confirm('Khôi phục màu mặc định?');">Khôi phục mặc định</button>
            </p>
        </form>
    </div>
    <?php
}

add_action('wp_head', function () {
    $c = wp_parse_args(get_option('wpcm_colors', []), wpcm_defaults());
    ?>
    <style id="wpcm-global-colors">
        :root{--wpcm-primary:<?php echo esc_html($c['primary']); ?>;--wpcm-secondary:<?php echo esc_html($c['secondary']); ?>;--wpcm-accent:<?php echo esc_html($c['accent']); ?>;--wpcm-background:<?php echo esc_html($c['background']); ?>;--wpcm-surface:<?php echo esc_html($c['surface']); ?>;--wpcm-text:<?php echo esc_html($c['text']); ?>;--wpcm-heading:<?php echo esc_html($c['heading']); ?>;--wpcm-muted:<?php echo esc_html($c['muted']); ?>;--wpcm-link:<?php echo esc_html($c['link']); ?>;--wpcm-link-hover:<?php echo esc_html($c['link_hover']); ?>;--wpcm-button-text:<?php echo esc_html($c['button_text']); ?>;--wpcm-border:<?php echo esc_html($c['border']); ?>;--wpcm-header-bg:<?php echo esc_html($c['header_bg']); ?>;--wpcm-footer-bg:<?php echo esc_html($c['footer_bg']); ?>;--wpcm-footer-text:<?php echo esc_html($c['footer_text']); ?>}
        body{color:var(--wpcm-text);background-color:var(--wpcm-background)}
        h1,h2,h3,h4,h5,h6{color:var(--wpcm-heading)}
        a{color:var(--wpcm-link)} a:hover{color:var(--wpcm-link-hover)}
        button,input[type=submit],input[type=button],.button,.wp-element-button{background-color:var(--wpcm-primary);border-color:var(--wpcm-primary);color:var(--wpcm-button-text)}
        header,.site-header{background-color:var(--wpcm-header-bg)}
        footer,.site-footer{background-color:var(--wpcm-footer-bg);color:var(--wpcm-footer-text)}
    </style>
    <?php
});
