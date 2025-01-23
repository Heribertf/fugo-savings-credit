document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    const email = document.getElementById('email1');
    const password = document.getElementById('password1');
    const confirmPassword = document.getElementById('password2');
    const termsCheckbox = document.getElementById('customCheckb1');

    // Show error message under input field
    function showError(input, message) {
        const formGroup = input.closest('.form-group');
        const existingError = formGroup.querySelector('.error-message');
        
        if (existingError) {
            existingError.textContent = message;
        } else {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger small mt-1';
            errorDiv.textContent = message;
            input.parentNode.appendChild(errorDiv);
        }
        
        input.classList.add('is-invalid');
    }

    // Remove error message
    function removeError(input) {
        const formGroup = input.closest('.form-group');
        const errorDiv = formGroup.querySelector('.error-message');
        if (errorDiv) {
            errorDiv.remove();
        }
        input.classList.remove('is-invalid');
    }

    // Validate email format
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email.toLowerCase());
    }

    // Validate password strength
    function validatePassword(password) {
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
        const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
        return re.test(password);
    }

    // Real-time email validation
    email.addEventListener('input', function() {
        removeError(email);
        if (this.value && !validateEmail(this.value)) {
            showError(this, 'Please enter a valid email address');
        }
    });

    // Real-time password validation
    password.addEventListener('input', function() {
        removeError(password);
        if (this.value && !validatePassword(this.value)) {
            showError(this, 'Password must be at least 8 characters long and contain uppercase, lowercase, and numbers');
        }
    });

    // Real-time confirm password validation
    confirmPassword.addEventListener('input', function() {
        removeError(confirmPassword);
        if (this.value && this.value !== password.value) {
            showError(this, 'Passwords do not match');
        }
    });

    // Form submission validation
    registerForm.addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous errors
        removeError(email);
        removeError(password);
        removeError(confirmPassword);

        // Email validation
        if (!email.value.trim()) {
            showError(email, 'Email is required');
            isValid = false;
        } else if (!validateEmail(email.value)) {
            showError(email, 'Please enter a valid email address');
            isValid = false;
        }

        // Password validation
        if (!password.value) {
            showError(password, 'Password is required');
            isValid = false;
        } else if (!validatePassword(password.value)) {
            showError(password, 'Password must be at least 8 characters long and contain uppercase, lowercase, and numbers');
            isValid = false;
        }

        // Confirm password validation
        if (!confirmPassword.value) {
            showError(confirmPassword, 'Please confirm your password');
            isValid = false;
        } else if (confirmPassword.value !== password.value) {
            showError(confirmPassword, 'Passwords do not match');
            isValid = false;
        }

        // Terms checkbox validation
        if (!termsCheckbox.checked) {
            showError(termsCheckbox, 'You must accept the terms and conditions');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });
}); 