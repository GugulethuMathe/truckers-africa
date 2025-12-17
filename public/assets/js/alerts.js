// Common alert function for the entire application
function showAlert(message, type = 'error') {
    return Swal.fire({
        icon: type,
        title: 'Notice',
        text: message,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
}

// Confirmation dialog
function showConfirm(title, text, confirmButtonText = 'Yes', cancelButtonText = 'No') {
    return Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: confirmButtonText,
        cancelButtonText: cancelButtonText
    });
}

// Success message with auto-close
function showSuccess(message) {
    return showAlert(message, 'success');
}

// Error message with auto-close
function showError(message) {
    return showAlert(message, 'error');
}

// Warning message with auto-close
function showWarning(message) {
    return showAlert(message, 'warning');
}

// Info message with auto-close
function showInfo(message) {
    return showAlert(message, 'info');
}

// Loading state
function showLoading(message = 'Please wait...') {
    Swal.fire({
        title: message,
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });
}

// Close loading state
function hideLoading() {
    Swal.close();
}
