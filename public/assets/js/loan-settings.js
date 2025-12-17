$(document).ready(function() {
    // Handle form submissions
    $('form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.text();

        // Show loading state
        submitBtn.prop('disabled', true);
        showLoading('Updating setting...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess(response.message || 'Setting updated successfully');
                } else {
                    showError(response.message || 'Failed to update setting');
                }
            },
            error: function() {
                hideLoading();
                showError('An error occurred while updating the setting');
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });
});
