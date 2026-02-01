@props(['type' => 'success', 'message'])

<div id="custom-alert-container" class="fixed top-0 right-0 w-full max-w-sm p-4 z-50 transition-all duration-300 ease-in-out transform -translate-y-full opacity-0">
    <div id="custom-alert-box" class="rounded-2xl shadow-lg p-4 w-full flex items-center space-x-3">
        <div class="flex-shrink-0">
            <span id="custom-alert-icon" class="material-symbols-outlined text-white text-lg"></span>
        </div>
        <div class="flex-1">
            <p id="custom-alert-message" class="text-white text-xs font-medium"></p>
        </div>
        <div class="flex-shrink-0">
            <button id="custom-alert-close-button" class="text-white hover:text-gray-200 focus:outline-none">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertContainer = document.getElementById('custom-alert-container');
        if (!alertContainer) return;

        const alertBox = document.getElementById('custom-alert-box');
        const alertIcon = document.getElementById('custom-alert-icon');
        const alertMessage = document.getElementById('custom-alert-message');
        const closeButton = document.getElementById('custom-alert-close-button');

        let alertTimeout;

        function showAlert(message, type) {
            if (!alertContainer || !alertBox || !alertIcon || !alertMessage) return;

            // Clear any existing timeout
            if (alertTimeout) {
                clearTimeout(alertTimeout);
            }

            // Set message and icon
            alertMessage.textContent = message || 'No message provided.';
            if (type === 'success') {
                alertIcon.textContent = 'check_circle';
            } else if (type === 'error') {
                alertIcon.textContent = 'error';
            } else {
                alertIcon.textContent = 'info';
            }

            // Set background color
            alertBox.classList.remove('bg-green-500', 'bg-red-500', 'bg-gray-700');
            if (type === 'success') {
                alertBox.classList.add('bg-green-500');
            } else if (type === 'error') {
                alertBox.classList.add('bg-red-500');
            } else {
                alertBox.classList.add('bg-gray-700');
            }
            
            // Show the alert
            alertContainer.classList.remove('opacity-0', '-translate-y-full');
            alertContainer.classList.add('opacity-100', 'translate-y-0');

            // Set timeout to hide the alert
            alertTimeout = setTimeout(() => {
                hideAlert();
            }, 5000);
        }

        function hideAlert() {
            if (!alertContainer) return;
            alertContainer.classList.remove('opacity-100', 'translate-y-0');
            alertContainer.classList.add('opacity-0', '-translate-y-full');
        }

        // Listen for Livewire event
        window.addEventListener('custom-alert', event => {
            // Livewire 3 passes the payload in event.detail[0] when it's an array
            const payload = event.detail[0] || {};
            showAlert(payload.message, payload.type);
        });

        // Close button functionality
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                if (alertTimeout) {
                    clearTimeout(alertTimeout);
                }
                hideAlert();
            });
        }
    });
</script>

