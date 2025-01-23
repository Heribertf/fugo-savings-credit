// Notification handling
function notification(notificationId, autoClose = false) {
    // Get the notification element
    const notification = document.getElementById(notificationId);
    if (!notification) return;

    // Show notification
    notification.style.display = 'block';

    // Handle close button click
    const closeButton = notification.querySelector('.close-button');
    if (closeButton) {
        closeButton.addEventListener('click', function(e) {
            e.preventDefault();
            hideNotification(notification);
        });
    }

    // Handle tap-to-close if class exists
    if (notification.classList.contains('tap-to-close')) {
        notification.addEventListener('click', function() {
            hideNotification(notification);
        });
    }

    // Auto close if duration is set
    if (autoClose) {
        setTimeout(() => {
            hideNotification(notification);
        }, autoClose);
    }
}

// Hide notification with animation
function hideNotification(notification) {
    notification.style.opacity = '0';
    setTimeout(() => {
        notification.style.display = 'none';
        notification.style.opacity = '1';
    }, 300);
}

// Initialize notifications on page load
document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to all notifications
    const notifications = document.querySelectorAll('.notification-box');
    notifications.forEach(notification => {
        notification.style.transition = 'opacity 0.3s ease';
        notification.style.display = 'none';
    });

    // Show success notification if it exists
    const successNotification = document.getElementById('notification-success');
    if (successNotification) {
        notification('notification-success', 5000); // Auto close after 5 seconds
    }

    // Show error notification if it exists
    const errorNotification = document.getElementById('notification-error');
    if (errorNotification) {
        notification('notification-error', 5000); // Auto close after 5 seconds
    }
}); 