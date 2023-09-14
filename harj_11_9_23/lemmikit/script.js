// Lisää JavaScript-koodi tähän
document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("contactForm");

    form.addEventListener("submit", function (event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }

        form.classList.add("was-validated");
    });

    var inputFields = form.querySelectorAll("input, select, textarea");
    inputFields.forEach(function (input) {
        input.addEventListener("input", function () {

            // in input: close alerts that can be closed:
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let close = alert.querySelector('.btn-close');
                if (close)
                    close.click();
            });

            if (input.checkValidity()) {
                input.classList.remove("is-invalid");
            } else {
                input.classList.add("is-invalid");
            }
        });
    });
});
