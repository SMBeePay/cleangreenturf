(function () {
    var toggle = document.querySelector('.site-header__menu-toggle');
    var header = document.querySelector('.site-header');
    if (!toggle || !header) return;
    toggle.addEventListener('click', function () {
        var isOpen = header.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
})();
