/**
 * SMS PHP Version JavaScript
 */

// Helper function to show notifications
function showNotification(message, type = 'info') {
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    }[type] || 'alert-info';

    const alertHTML = `<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>`;

    // Insert at top of page
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.insertAdjacentHTML('afterbegin', alertHTML);
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    // Enable Bootstrap form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

// AJAX form submission helper
function submitFormAjax(formId, endpoint) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(endpoint, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            setTimeout(() => {
                window.location.href = data.redirect || window.location.href;
            }, 1500);
        } else {
            showNotification(data.error || 'An error occurred', 'error');
        }
    })
    .catch(error => {
        showNotification('Network error: ' + error.message, 'error');
    });
}

// Confirm delete
function confirmDelete(message = 'Are you sure?') {
    return confirm(message);
}

// Export table to CSV
function exportTableToCSV(filename) {
    const table = document.querySelector('.table');
    let csv = [];
    
    // Get headers
    const headers = Array.from(table.querySelectorAll('thead th'))
        .map(th => '"' + th.innerText.replace(/"/g, '""') + '"')
        .join(',');
    csv.push(headers);
    
    // Get rows
    Array.from(table.querySelectorAll('tbody tr')).forEach(tr => {
        const row = Array.from(tr.querySelectorAll('td'))
            .slice(0, -1) // Exclude actions column
            .map(td => '"' + td.innerText.replace(/"/g, '""') + '"')
            .join(',');
        csv.push(row);
    });
    
    // Download
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename + '.csv';
    link.click();
}

// Date picker helper
function initDatePicker() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        if (!input.value) {
            input.valueAsDate = new Date();
        }
    });
}
