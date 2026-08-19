jQuery(document).ready(function ($) {
    $('#qlkh-register-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('.qlkh-submit-btn');
        var $responseContainer = $('#qlkh-form-response');

        $btn.prop('disabled', true).html('<span class="qlkh-spinner"></span> Đang xử lý...');
        $responseContainer.hide().removeClass('qlkh-alert-success qlkh-alert-error').html('');

        var formData = $form.serialize();

        $.ajax({
            url: qlkh_public_obj.ajax_url,
            type: 'POST',
            data: formData + '&action=qlkh_submit_register&nonce=' + qlkh_public_obj.nonce,
            dataType: 'json',
            success: function (res) {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-send"></span> Gửi đăng ký ngay');

                if (res.success) {
                    $responseContainer
                        .addClass('qlkh-alert qlkh-alert-success')
                        .html('<span class="dashicons dashicons-yes-alt"></span> ' + res.data.message)
                        .slideDown();
                    $form[0].reset();
                } else {
                    $responseContainer
                        .addClass('qlkh-alert qlkh-alert-error')
                        .html('<span class="dashicons dashicons-warning"></span> ' + (res.data.message || 'Đã có lỗi xảy ra. Vui lòng thử lại.'))
                        .slideDown();
                }
            },
            error: function () {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-send"></span> Gửi đăng ký ngay');
                $responseContainer
                    .addClass('qlkh-alert qlkh-alert-error')
                    .html('<span class="dashicons dashicons-warning"></span> Kết nối thất bại. Vui lòng thử lại sau.')
                    .slideDown();
            }
        });
    });
});
