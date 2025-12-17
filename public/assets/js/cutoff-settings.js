$(document).ready(function() {
    // Initialize date inputs with proper year and handling
    $('.cutoff-date').each(function() {
        const currentYear = new Date().getFullYear();
        const month = $(this).closest('tr').index();
        
        // Set min and max dates to ensure selection within the correct month
        const firstDay = new Date(currentYear, month, 1);
        const lastDay = new Date(currentYear, month + 1, 0);
        
        $(this).attr('min', firstDay.toISOString().split('T')[0]);
        $(this).attr('max', lastDay.toISOString().split('T')[0]);
        
        $(this).on('change', function() {
            validateCutoffDate($(this));
        });
    });

    function validateCutoffDate(input) {
        const date = new Date(input.val());
        const month = date.getMonth();
        const rowMonth = input.closest('tr').index();
        
        if (month !== rowMonth) {
            showError('Please select a date within the specified month');
            input.val('');
        }
    }

    // Handle form submissions
    $('form').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.text();

        // Show loading state
        submitBtn.prop('disabled', true);
        showLoading('Updating settings...');

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                hideLoading();
                if (response.success) {
                    showSuccess(response.message || 'Settings updated successfully');
                } else {
                    showError(response.message || 'Failed to update settings');
                }
            },
            error: function() {
                hideLoading();
                showError('An error occurred while updating the settings');
            },
            complete: function() {
                submitBtn.prop('disabled', false);
            }
        });
    });
});
