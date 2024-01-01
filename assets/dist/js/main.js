document.addEventListener('DOMContentLoaded', function () {
    window.setBootstrapTheme = function (name) {
        document.body.parentElement.setAttribute('data-bs-theme', name);
    };
    window.checkToggle = function (checkbox, toggleClass, targetSelector) {
        var target = document.querySelector(targetSelector);
        var checked = checkbox.checked === true;
        console.log("Checked", checked);
        if (checked) {
            target.classList.add(toggleClass);
        }
        else {
            target.classList.remove(toggleClass);
        }
    };
    console.log("Welcome to Mosaic CMS.");
});
//# sourceMappingURL=main.js.map