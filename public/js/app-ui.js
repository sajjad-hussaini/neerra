document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-confirm]");
    if (button && !confirm(button.dataset.confirm)) event.preventDefault();
});

(function () {
    const toggle = document.querySelector(".menu-toggle");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.querySelector(".sidebar-overlay");
    if (!toggle || !sidebar || !overlay) return;
    const open = () => {
        sidebar.classList.add("open");
        overlay.hidden = false;
        toggle.setAttribute("aria-expanded", "true");
        document.body.style.overflow = "hidden";
    };
    const close = () => {
        sidebar.classList.remove("open");
        overlay.hidden = true;
        toggle.setAttribute("aria-expanded", "false");
        document.body.style.overflow = "";
    };
    toggle.addEventListener("click", () =>
        sidebar.classList.contains("open") ? close() : open(),
    );
    overlay.addEventListener("click", close);
    sidebar
        .querySelectorAll("a")
        .forEach((link) => link.addEventListener("click", close));
    window.addEventListener("keydown", (event) => {
        if (event.key === "Escape") close();
    });
})();
