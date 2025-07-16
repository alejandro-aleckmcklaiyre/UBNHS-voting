document.addEventListener('DOMContentLoaded', function() {
    // Get error from URL query string
    const params = new URLSearchParams(window.location.search);
    const error = params.get('error');
    if (error) {
        let errorMsg = '';
        if (error === 'invalid') {
            errorMsg = 'Wrong username or password.';
        } else if (error === 'empty') {
            errorMsg = 'Please enter both username and password.';
        } else if (error === 'db') {
            errorMsg = 'Database error. Please try again.';
        } else {
            errorMsg = 'Login failed.';
        }
        var formContainer = document.querySelector('.image-container1');
        var errorDiv = document.createElement('div');
        errorDiv.textContent = errorMsg;
        errorDiv.style.background = '#A8201A';
        errorDiv.style.color = '#fff';
        errorDiv.style.padding = '10px 20px';
        errorDiv.style.marginBottom = '15px';
        errorDiv.style.borderRadius = '6px';
        errorDiv.style.textAlign = 'center';
        errorDiv.style.fontWeight = 'bold';
        errorDiv.style.fontSize = '16px';
        formContainer.insertBefore(errorDiv, formContainer.firstChild.nextSibling);
    }
});
