jQuery(document).ready(function ($) {
    // Open Edit Modal
    $(document).on('click', '.qlkh-btn-edit', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var email = $(this).data('email');
        var phone = $(this).data('phone');
        var address = $(this).data('address');
        var status = $(this).data('status');
        var category = $(this).data('category');
        var notes = $(this).data('notes');

        $('#qlkh-edit-id').val(id);
        $('#qlkh-edit-name').val(name);
        $('#qlkh-edit-email').val(email);
        $('#qlkh-edit-phone').val(phone);
        $('#qlkh-edit-address').val(address);
        $('#qlkh-edit-status').val(status);
        $('#qlkh-edit-category').val(category);
        $('#qlkh-edit-notes').val(notes);

        $('#qlkh-edit-modal').addClass('active');
    });

    // Close Modals
    $('.qlkh-modal-close, .qlkh-modal-cancel').on('click', function () {
        $('.qlkh-modal-backdrop').removeClass('active');
    });

    // Save Edit Form via AJAX
    $('#qlkh-edit-form').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: qlkh_admin_obj.ajax_url,
            type: 'POST',
            data: formData + '&action=qlkh_update_customer&nonce=' + qlkh_admin_obj.nonce,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + res.data.message);
                }
            }
        });
    });

    // Open Add Modal
    $('#qlkh-btn-add-new').on('click', function () {
        $('#qlkh-add-modal').addClass('active');
    });

    // Submit Add Form via AJAX
    $('#qlkh-add-form').on('submit', function (e) {
        e.preventDefault();
        var formData = $(this).serialize();

        $.ajax({
            url: qlkh_admin_obj.ajax_url,
            type: 'POST',
            data: formData + '&action=qlkh_add_customer&nonce=' + qlkh_admin_obj.nonce,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.data.message);
                    location.reload();
                } else {
                    alert('Lỗi: ' + res.data.message);
                }
            }
        });
    });

    // Delete Customer
    $(document).on('click', '.qlkh-btn-delete', function () {
        if (!confirm('Bạn có chắc chắn muốn xóa khách hàng này không?')) return;
        var id = $(this).data('id');

        $.ajax({
            url: qlkh_admin_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'qlkh_delete_customer',
                id: id,
                nonce: qlkh_admin_obj.nonce
            },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#qlkh-row-' + id).fadeOut(300, function () { $(this).remove(); });
                } else {
                    alert('Lỗi: ' + res.data.message);
                }
            }
        });
    });
});
