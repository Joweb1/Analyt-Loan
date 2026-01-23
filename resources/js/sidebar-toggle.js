// resources/js/sidebar-toggle.js

document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const hideSidebarBtn = document.getElementById('hideSidebarBtn');
    const showSidebarFab = document.getElementById('showSidebarFab');
    const mainContent = document.querySelector('main');

    // Function to toggle sidebar state
    const toggleSidebar = () => {
        // Toggle 'sidebar-hidden' class on the sidebar
        sidebar.classList.toggle('sidebar-hidden');

        // Toggle visibility of elements inside sidebar (like text labels)
        // This is a more complex task that would require iterating through elements
        // or a more structured HTML. For now, we'll rely on the sidebar-hidden class
        // and its associated CSS for the main toggle effect.

        // Toggle 'rotate-180' class on the arrow icon
        const arrowIcon = hideSidebarBtn.querySelector('.material-symbols-outlined');
        if (arrowIcon) {
            arrowIcon.classList.toggle('rotate-180');
        }

        // Adjust main content margin based on sidebar state
        if (sidebar.classList.contains('sidebar-hidden')) {
            mainContent.classList.remove('lg:ml-64');
            mainContent.classList.add('lg:ml-20');
            // Show the FAB when sidebar is hidden on small screens
            if (window.innerWidth < 1024 && showSidebarFab) { // 1024px is 'lg' breakpoint
                showSidebarFab.style.display = 'flex';
            }
        } else {
            mainContent.classList.remove('lg:ml-20');
            mainContent.classList.add('lg:ml-64');
            // Hide the FAB when sidebar is shown on small screens
            if (window.innerWidth < 1024 && showSidebarFab) {
                showSidebarFab.style.display = 'none';
            }
        }
    };

    // Event listeners
    if (hideSidebarBtn) {
        hideSidebarBtn.addEventListener('click', toggleSidebar);
    }

    if (showSidebarFab) {
        showSidebarFab.addEventListener('click', () => {
            if (sidebar.classList.contains('sidebar-hidden')) {
                toggleSidebar();
            }
        });
    }

    // Initial check for sidebar state on load for responsive behavior
    const handleResize = () => {
        if (window.innerWidth >= 1024) { // 'lg' breakpoint
            // On large screens, sidebar should be visible by default unless toggled
            if (sidebar.classList.contains('sidebar-hidden')) {
                mainContent.classList.remove('lg:ml-64');
                mainContent.classList.add('lg:ml-20');
            } else {
                mainContent.classList.remove('lg:ml-20');
                mainContent.classList.add('lg:ml-64');
            }
            if (showSidebarFab) {
                showSidebarFab.style.display = 'none'; // FAB not needed on large screens
            }
        } else {
            // On small screens, ensure main content has no large margin
            mainContent.classList.remove('lg:ml-64', 'lg:ml-20');
            // Show FAB if sidebar is hidden on small screens
            if (sidebar.classList.contains('sidebar-hidden') && showSidebarFab) {
                showSidebarFab.style.display = 'flex';
            } else if (showSidebarFab) {
                showSidebarFab.style.display = 'none';
            }
        }
    };

    window.addEventListener('resize', handleResize);
    handleResize(); // Call on initial load
});
