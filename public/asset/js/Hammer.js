/* Tooltip global bootstrap: false */
(function () {
    'use strict'
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl)
    })
})()

window.Hammer = {

    exit: function(exit_text) {
        response = confirm(exit_text);
        if(response) document.getElementById('logout-form').submit();
    }
}

window.ProspectFlow = window.Hammer;
