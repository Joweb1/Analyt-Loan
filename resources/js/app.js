import './bootstrap';

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
    const hideSidebarBtn = document.getElementById('hideSidebarBtn');
    const showSidebarFab = document.getElementById('showSidebarFab');
    const mainContent = document.getElementById('main-content');
    const hideSidebarBtnIcon = hideSidebarBtn ? hideSidebarBtn.querySelector('.material-symbols-outlined') : null;
    const hideSidebarBtnText = hideSidebarBtn ? hideSidebarBtn.querySelector('.sidebar-nav-text') : null;
    const sidebarNavTexts = document.querySelectorAll('.sidebar-nav-text');
    const sidebarLogoText = document.getElementById('sidebar-logo-text');
    const sidebarUserProfileText = document.getElementById('sidebar-user-profile-text');
    const showSidebarFabIcon = showSidebarFab ? showSidebarFab.querySelector('.material-symbols-outlined') : null; // New reference

    const transitionDuration = 300; // ms, matches CSS transition-duration

    // Function to set sidebar state
    const setSidebarState = (isExpanded) => {
        if (isExpanded) {
            sidebar.style.display = 'flex'; // Make it display before animating
            // Use setTimeout to ensure display:flex is applied before transition
            setTimeout(() => {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.classList.remove('lg:ml-0');
                mainContent.classList.add('lg:ml-64');
                if (hideSidebarBtnIcon) hideSidebarBtnIcon.classList.remove('rotate-180');
                if (hideSidebarBtnText) hideSidebarBtnText.textContent = 'Collapse';
                if (showSidebarFabIcon) showSidebarFabIcon.textContent = 'arrow_back_ios'; // Change FAB icon

                // Show text elements
                if (sidebarLogoText) sidebarLogoText.classList.remove('hidden');
                if (sidebarUserProfileText) sidebarUserProfileText.classList.remove('hidden');
                sidebarNavTexts.forEach(el => el.classList.remove('hidden'));
                // sidebar.classList.remove('overflow-hidden'); // Removed as overflow-hidden is now permanent on aside

                // FAB always visible, so no class manipulation here
            }, 10); // Small delay to allow display change to render
        } else {
            sidebar.classList.remove('sidebar-expanded');
            sidebar.classList.add('sidebar-collapsed');
            mainContent.classList.remove('lg:ml-64');
            mainContent.classList.add('lg:ml-0');
            if (hideSidebarBtnIcon) hideSidebarBtnIcon.classList.add('rotate-180');
            if (hideSidebarBtnText) hideSidebarBtnText.textContent = 'Expand';
            if (showSidebarFabIcon) showSidebarFabIcon.textContent = 'menu'; // Change FAB icon

            // Hide text elements
            if (sidebarLogoText) sidebarLogoText.classList.add('hidden');
            if (sidebarUserProfileText) sidebarUserProfileText.classList.add('hidden');
            sidebarNavTexts.forEach(el => el.classList.add('hidden'));
            // sidebar.classList.add('overflow-hidden'); // Removed as overflow-hidden is now permanent on aside

            // FAB always visible, so no class manipulation here

            // Hide sidebar completely after transition
            setTimeout(() => {
                sidebar.style.display = 'none';
            }, transitionDuration);
        }
    };

    // Toggle function
    const toggleSidebar = () => {
        setSidebarState(sidebar.classList.contains('sidebar-collapsed'));
    };

    // Event listeners
    if (hideSidebarBtn) {
        hideSidebarBtn.addEventListener('click', toggleSidebar);
    }

    if (showSidebarFab) {
        showSidebarFab.addEventListener('click', toggleSidebar); // FAB now toggles sidebar
    }

    // Initial setup on DOMContentLoaded and resize
    const handleInitialAndResize = () => {
        setSidebarState(false); // Always start collapsed
        if (showSidebarFabIcon) showSidebarFabIcon.textContent = 'menu'; // Set initial FAB icon
        // FAB always visible, so no class manipulation here
    };

    handleInitialAndResize();
    window.addEventListener('resize', handleInitialAndResize);
}

document.addEventListener('DOMContentLoaded', setupSidebarToggle);


