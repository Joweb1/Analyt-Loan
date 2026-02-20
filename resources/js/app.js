import './bootstrap';
import './webpush';

window.togglePasswordVisibility = function(inputId, iconId) {
    const passwordInput = document.getElementById(inputId);
    const iconSpan = document.getElementById(iconId);

    if (passwordInput && iconSpan) {
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            iconSpan.textContent = 'visibility_off';
        } else {
            passwordInput.type = 'password';
            iconSpan.textContent = 'visibility';
        }
    }
};

function setupSidebarToggle() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    if (!sidebar || !mainContent) return;

    const hideSidebarBtn = document.getElementById('hideSidebarBtn');
    const showSidebarFab = document.getElementById('showSidebarFab');
    const showSidebarFabIcon = showSidebarFab ? showSidebarFab.querySelector('.material-symbols-outlined') : null;

    const setSidebarState = (expanded, save = true) => {
        // Remove the transition-blocking attribute if it exists
        document.documentElement.removeAttribute('data-sidebar-pinned');

        sidebar.setAttribute('data-expanded', expanded);
        
        if (showSidebarFabIcon) {
            showSidebarFabIcon.textContent = expanded ? 'arrow_back_ios' : 'menu';
        }

        if (save && window.innerWidth >= 700) {
            localStorage.setItem('sidebarPinned', expanded ? 'true' : 'false');
        }
    };

    const toggleSidebar = () => {
        const isExpanded = sidebar.getAttribute('data-expanded') === 'true';
        setSidebarState(!isExpanded);
    };

    if (hideSidebarBtn && !hideSidebarBtn.dataset.hasSidebarListener) {
        hideSidebarBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setSidebarState(false);
        });
        hideSidebarBtn.dataset.hasSidebarListener = 'true';
    }

    if (showSidebarFab && !showSidebarFab.dataset.hasSidebarListener) {
        showSidebarFab.addEventListener('click', (e) => {
            e.preventDefault();
            toggleSidebar();
        });
        showSidebarFab.dataset.hasSidebarListener = 'true';
    }

    const initializeSidebar = () => {
        const isPinned = localStorage.getItem('sidebarPinned') === 'true';
        const isWideScreen = window.innerWidth >= 700;
        
        // Use attribute from inline script if it already set it to true
        const alreadyExpanded = sidebar.getAttribute('data-expanded') === 'true';

        if ((isWideScreen && isPinned) || alreadyExpanded) {
            setSidebarState(true, false);
        } else {
            setSidebarState(false, false);
        }
    };

    initializeSidebar();
}

document.addEventListener('DOMContentLoaded', setupSidebarToggle);
document.addEventListener('livewire:navigated', setupSidebarToggle);


