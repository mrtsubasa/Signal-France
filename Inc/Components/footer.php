<script>
    function showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notification-container');
        const notification = document.createElement('div');

        // Classes CSS selon le type
        let bgColor, textColor, icon;
        switch (type) {
            case 'success':
                bgColor = 'bg-green-500';
                textColor = 'text-white';
                icon = 'fas fa-check-circle';
                break;
            case 'error':
                bgColor = 'bg-red-500';
                textColor = 'text-white';
                icon = 'fas fa-exclamation-circle';
                break;
            case 'warning':
                bgColor = 'bg-yellow-500';
                textColor = 'text-white';
                icon = 'fas fa-exclamation-triangle';
                break;
            default:
                bgColor = 'bg-blue-500';
                textColor = 'text-white';
                icon = 'fas fa-info-circle';
        }

        notification.className = `notification ${bgColor} ${textColor} p-4 rounded-lg shadow-lg max-w-sm flex items-center space-x-3`;
        notification.innerHTML = `
            <i class="${icon} text-lg"></i>
            <span class="flex-1">${message}</span>
            <button onclick="hideNotification(this.parentElement)" class="${textColor} hover:opacity-75 transition-opacity">
                <i class="fas fa-times"></i>
            </button>
        `;

        container.appendChild(notification);

        // Animation d'entrée
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);

        // Auto-hide après la durée spécifiée
        setTimeout(() => {
            hideNotification(notification);
        }, duration);
    }

    function hideNotification(notification) {
        notification.classList.remove('show');
        notification.classList.add('hide');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.parentElement.removeChild(notification);
            }
        }, 500);
    }

    <?php if (isset($_SESSION['notification'])): ?>
        document.addEventListener('DOMContentLoaded', function () {
            showNotification(
                "<?php echo addslashes($_SESSION['notification']['message']); ?>",
                "<?php echo $_SESSION['notification']['type']; ?>"
            );
        });
        <?php unset($_SESSION['notification']); ?>
    <?php endif; ?>
</script>
</body>

</html>