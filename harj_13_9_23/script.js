document.addEventListener("DOMContentLoaded", function () {
    var form = document.getElementById("film_form");


    // Move .invalid-feedback divs data-default into div itself
    const divs = document.querySelectorAll('.invalid-feedback');
    divs.forEach(function(div) {
        const defaultValue = div.getAttribute('data-default');
        // if there is no text then set it to data-default
        if (div.textContent.trim() === '') 
            div.textContent = defaultValue;
    });

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
            input.classList.add("user-modified");

            // in input: close alerts that can be closed:
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let close = alert.querySelector('.btn-close');
                if (close)
                    close.click();
            });

            if (input.checkValidity()) 
                input.classList.remove("is-invalid");
            else 
                input.classList.add("is-invalid");

            // reset feedback back to default:
            let id = input.getAttribute('id');
            let id_feedback = id + "-feedback";
            let div = document.getElementById(id_feedback);
            div.textContent = div.getAttribute('data-default');
        });
    });
});
