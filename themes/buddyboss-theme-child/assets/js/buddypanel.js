window.onload = function () {
    const menuItem = document.querySelector('#menu-item-last-courses');
    const dropdown = menuItem ? menuItem.querySelector('.sub-menu') : null;
    const arrowIcon = menuItem ? menuItem.querySelector('.bb-icon-angle-down') : null;
    const toggleButton = document.getElementById('toggle-sidebar');
    const buddypanel = document.querySelector('body > aside');
    const siteContent = document.querySelector('.bb-buddypanel:not(.activate) .site, .bb-buddypanel:not(.register) .site');
    const originalMarginLeft = document.body.style.marginLeft;
    const originalMarginRight = document.body.style.marginRight;
    const buddypanelMenu = document.querySelector('#buddypanel-menu');

    if (!menuItem || !dropdown || !toggleButton || !buddypanel) {
        console.warn("Required elements for BuddyPanel are missing. Aborting initialization.");
        return;
    }

    // Function to mark the current menu item based on URL
    function highlightCurrentMenuItem() {
        const currentUrl = window.location.href;
        buddypanelMenu.querySelectorAll('a').forEach(function (menuLink) {
            const menuItem = menuLink.closest('li');
            if (menuItem && !menuItem.classList.contains('user-not-active')) {
                menuItem.classList.remove('current-menu-item');
                if (menuLink.href === currentUrl) {
                    menuItem.classList.add('current-menu-item');
                }
            }
        });
    }

    highlightCurrentMenuItem();

    function checkViewport() {
        if (window.innerWidth < 800) {
            toggleButton.style.display = 'none';
        } else {
            toggleButton.style.display = 'flex';
        }
    }

    checkViewport();
    window.addEventListener('resize', checkViewport);

    function applyStyles() {
        buddypanel.style.opacity = "0";
        buddypanel.style.visibility = "hidden";
        document.body.classList.remove('bb-buddypanel', 'buddypanel-open');
        document.body.style.setProperty('margin-left', '0', 'important');
        document.body.style.setProperty('margin-right', '0', 'important');
        toggleButton.style.right = '0';
        toggleButton.innerHTML = "&gt;";
    }

    function restoreStyles() {
        buddypanel.style.opacity = "1";
        buddypanel.style.visibility = "visible";
        document.body.style.setProperty('margin-left', originalMarginLeft, 'important');
        document.body.style.setProperty('margin-right', originalMarginRight, 'important');
        document.body.classList.add('bb-buddypanel', 'buddypanel-open');
        toggleButton.style.right = '220px';
        toggleButton.innerHTML = "&lt;";
    }

    toggleButton.addEventListener('click', function () {
        const isOpen = buddypanel.style.visibility !== "hidden";
        if (isOpen) {
            applyStyles();
        } else {
            restoreStyles();
        }
    });

    if (dropdown) {
        menuItem.classList.add('open');
        dropdown.classList.add('bb-open');
        if (arrowIcon) {
            arrowIcon.classList.add('bs-submenu-open');
        }
    }

    menuItem.addEventListener('click', function (event) {
        event.preventDefault();
        const isOpen = menuItem.classList.toggle('open');
        dropdown.classList.toggle('bb-open', isOpen);
        if (arrowIcon) {
            arrowIcon.classList.toggle('bs-submenu-open', isOpen);
        }
    });

    const profileTab = document.querySelector('#user-xprofile > div');
    if (profileTab) {
        profileTab.innerHTML = 'פרופיל';
    }
};
