document.addEventListener('DOMContentLoaded', function () {
    const menuItem = document.querySelector('#menu-item-last-courses');
    const dropdown = menuItem.querySelector('.sub-menu');
    const arrowIcon = menuItem.querySelector('.bb-icon-angle-down');
    const toggleButton = document.getElementById('toggle-sidebar');
    const buddypanel = document.querySelector('body > aside');
    const siteContent = document.querySelector('.bb-buddypanel:not(.activate) .site, .bb-buddypanel:not(.register) .site');
    const sidebarOpen = document.querySelector('.buddypanel-open:not(.register) .site');
    const sidebarNotOpen = document.querySelector('bb-buddypanel:not(.activate) .site, .bb-buddypanel:not(.register) .site');
    const originalMarginLeft = document.body.style.marginLeft;
    const originalMarginRight = document.body.style.marginRight;
    const buddypanelMenu = document.querySelector('#buddypanel-menu');

    // Function to mark the current menu item based on URL
    function highlightCurrentMenuItem() {
        const currentUrl = window.location.href; // Get current URL
        const buddypanelMenu = document.querySelector('#buddypanel-menu');

        buddypanelMenu.querySelectorAll('a').forEach(function (menuLink) {
            const menuItem = menuLink.closest('li');
            if (menuItem.classList.contains('user-not-active') || menuLink.href.endsWith('#')) {
                return; // Skip this link if it ends with '#' or is inactive
            }

            // Clear previous highlighting
            menuItem.classList.remove('current-menu-item');

            // Compare the exact URL match or use a stricter comparison
            if (menuLink.href === currentUrl) {
                menuItem.classList.add('current-menu-item');
            }
        });

    }
    // Run the function on page load to highlight the correct menu item
    highlightCurrentMenuItem();
    function checkViewport() {
        if (window.innerWidth < 800) {
            toggleButton.style.display = 'none'; // Show button on mobile
        } else {
            toggleButton.style.display = 'flex'; // Hide button on larger screens
        }
    }
    checkViewport();
    window.addEventListener('resize', checkViewport);


    // Function to apply styles
    function applyStyles() {
        buddypanel.style.opacity = "0";
        buddypanel.style.visibility = "hidden";
        document.body.classList.remove('bb-buddypanel');
        document.body.classList.remove('buddypanel-open');

        // Force reflow to ensure styles are applied immediately
        void document.body.offsetWidth;

        // Use !important to override existing styles
        document.body.style.setProperty('margin-left', '0', 'important');
        document.body.style.setProperty('margin-right', '0', 'important');
        toggleButton.style.right = '0'; // Set the toggle button position to 0
        toggleButton.innerHTML = "&gt;";


    }
    function restoreStyles() {
        buddypanel.style.opacity = "1";
        buddypanel.style.visibility = "visible";
        document.body.style.setProperty('margin-left', originalMarginLeft, 'important');
        document.body.style.setProperty('margin-right', originalMarginRight, 'important');
        document.body.classList.add('bb-buddypanel');
        document.body.classList.add('buddypanel-open');
        toggleButton.style.right = '220px'; // Set the toggle button position to 0
        toggleButton.innerHTML = "&lt;";
    }

    // Add event listener to toggle button
    toggleButton.addEventListener('click', function () {
        const isOpen = buddypanel.style.visibility !== "hidden"; // Check if sidebar is open
        if (isOpen) {
            applyStyles(); // Hide sidebar
        } else {
            restoreStyles(); // Show sidebar and restore styles
        }
    });

    // Ensure the section starts open
    if (dropdown) {
        menuItem.classList.add('open');
        dropdown.classList.add('bb-open');
        arrowIcon.classList.add('bs-submenu-open');  // Initially set the arrow to the open state
    }

    // Toggle functionality when clicking the entire menu item
    menuItem.addEventListener('click', function (event) {
        event.preventDefault();
        const isOpen = menuItem.classList.toggle('open');
        dropdown.classList.toggle('bb-open', isOpen);
        arrowIcon.classList.toggle('bs-submenu-open', isOpen); // Rotate the arrow on toggle
    });
    const profileTab = document.querySelector('#user-xprofile > div');
    if (profileTab) {
        profileTab.innerHTML = 'פרופיל'; // Translated to Hebrew
    }

});


