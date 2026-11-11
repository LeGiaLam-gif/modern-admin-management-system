(function () {
    const triggers = document.querySelectorAll(".product-name-trigger[data-description]");
    if (!triggers.length) return;

    const tooltip = document.createElement("div");
    tooltip.className = "product-description-tooltip";
    tooltip.id = "product-description-tooltip";
    tooltip.setAttribute("role", "tooltip");
    tooltip.hidden = true;
    document.body.appendChild(tooltip);

    let activeTrigger = null;

    function positionTooltip(trigger) {
        const bounds = trigger.getBoundingClientRect();
        const margin = 16;
        const gap = 10;
        const tooltipBounds = tooltip.getBoundingClientRect();
        const left = Math.max(margin, Math.min(bounds.left, window.innerWidth - tooltipBounds.width - margin));
        let top = bounds.bottom + gap;

        if (top + tooltipBounds.height > window.innerHeight - margin) {
            top = bounds.top - tooltipBounds.height - gap;
        }
        top = Math.max(margin, top);
        tooltip.style.left = left + "px";
        tooltip.style.top = top + "px";
    }

    function showTooltip(trigger) {
        if (activeTrigger && activeTrigger !== trigger) {
            activeTrigger.removeAttribute("aria-describedby");
            activeTrigger.setAttribute("aria-expanded", "false");
        }
        activeTrigger = trigger;
        tooltip.textContent = trigger.dataset.description || "";
        tooltip.hidden = false;
        trigger.setAttribute("aria-describedby", tooltip.id);
        trigger.setAttribute("aria-expanded", "true");
        positionTooltip(trigger);
    }

    function hideTooltip(trigger) {
        if (activeTrigger !== trigger) return;
        tooltip.hidden = true;
        trigger.removeAttribute("aria-describedby");
        trigger.setAttribute("aria-expanded", "false");
        activeTrigger = null;
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener("mouseenter", function () { showTooltip(trigger); });
        trigger.addEventListener("mouseleave", function () {
            if (document.activeElement !== trigger) hideTooltip(trigger);
        });
        trigger.addEventListener("focus", function () { showTooltip(trigger); });
        trigger.addEventListener("blur", function () { hideTooltip(trigger); });
        trigger.addEventListener("keydown", function (event) {
            if (event.key === "Escape") {
                hideTooltip(trigger);
                trigger.blur();
            }
        });
    });

    window.addEventListener("resize", function () {
        if (activeTrigger && !tooltip.hidden) positionTooltip(activeTrigger);
    });
    window.addEventListener("scroll", function () {
        if (activeTrigger && !tooltip.hidden) positionTooltip(activeTrigger);
    }, true);
})();
