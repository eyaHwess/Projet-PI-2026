// Homepage interactive scripts

document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const openMenuBtn = document.getElementById('openMenu');
    const closeMenuBtn = document.getElementById('closeMenu');
    const mobileMenu = document.getElementById('mobileMenu');

    if (openMenuBtn && mobileMenu) {
        openMenuBtn.addEventListener('click', function() {
            mobileMenu.style.height = 'auto';
        });
    }

    if (closeMenuBtn && mobileMenu) {
        closeMenuBtn.addEventListener('click', function() {
            mobileMenu.style.height = '0';
        });
    }

    // Close mobile menu when clicking menu links
    const menuLinks = mobileMenu?.querySelectorAll('a');
    if (menuLinks) {
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.style.height = '0';
            });
        });
    }

    // FAQ accordion - only one open at a time
    const details = document.querySelectorAll('details');
    details.forEach(detail => {
        detail.addEventListener('toggle', function() {
            if (this.open) {
                details.forEach(other => {
                    if (other !== this && other.open) {
                        other.open = false;
                    }
                });
            }
        });
    });
});
