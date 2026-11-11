(function () {
    const accountMenu = document.querySelector(".sidebar-account-menu");
    const settingsButton = document.querySelector(".sidebar-settings-button");
    const settingsStatus = document.getElementById("sidebar-settings-status");
    if (!accountMenu || !settingsButton || !settingsStatus) return;

    accountMenu.querySelector(".sidebar-profile-trigger").addEventListener("click", function () {
        settingsButton.setAttribute("aria-expanded", "false");
        settingsStatus.hidden = true;
    });

    settingsButton.addEventListener("click", function () {
        accountMenu.open = false;
        const isExpanded = settingsButton.getAttribute("aria-expanded") === "true";
        settingsButton.setAttribute("aria-expanded", String(!isExpanded));
        settingsStatus.hidden = isExpanded;
    });

    document.addEventListener("click", function (event) {
        if (!accountMenu.contains(event.target)) accountMenu.open = false;
        if (!settingsButton.contains(event.target) && !settingsStatus.contains(event.target)) {
            settingsButton.setAttribute("aria-expanded", "false");
            settingsStatus.hidden = true;
        }
    });
})();
