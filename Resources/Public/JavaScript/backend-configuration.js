document.addEventListener('DOMContentLoaded', function () {
    let siteSelector = document.getElementById('siteSelectorInput');
    if (siteSelector) {
        siteSelector.addEventListener('change', function () {
            window.location.href = this.value;
        });
    }

    document.querySelectorAll('input[name="autocompleteMode"]').forEach(function (el) {
        el.addEventListener('change', function () {
            document.getElementById('customScriptWrapper').style.display =
                (this.value === '2') ? 'block' : 'none';
        });
    });
});
