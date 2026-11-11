(function () {
    const storageKey = "admin-product-manager-theme";
    const savedTheme = window.localStorage.getItem(storageKey);
    document.documentElement.dataset.theme = savedTheme === "light" ? "light" : "dark";

    document.addEventListener("DOMContentLoaded", function () {
        const toggle = document.querySelector(".theme-switch-checkbox");
        if (!toggle) return;

        toggle.checked = document.documentElement.dataset.theme === "dark";
        toggle.addEventListener("change", function () {
            const nextTheme = toggle.checked ? "dark" : "light";
            document.documentElement.dataset.theme = nextTheme;
            window.localStorage.setItem(storageKey, nextTheme);
            document.dispatchEvent(new CustomEvent("themechange", { detail: { theme: nextTheme } }));
        });
    });
})();
