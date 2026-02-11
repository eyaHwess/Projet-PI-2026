document.addEventListener('DOMContentLoaded', () => {

    const openMenu = document.getElementById('openMenu');
    const closeMenu = document.getElementById('closeMenu');
    const mobileMenu = document.getElementById('mobileMenu');

    if (openMenu && closeMenu && mobileMenu) {
        openMenu.addEventListener('click', () => {
            mobileMenu.classList.remove('h-0');
        });

        closeMenu.addEventListener('click', () => {
            mobileMenu.classList.add('h-0');
        });
    }

});
