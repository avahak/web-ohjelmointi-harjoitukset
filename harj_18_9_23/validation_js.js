// Updates the validation status of the input. 
function updateValidationMessage(input, msg) {
    let feedbackId = input.id + "-feedback";
    let div = document.getElementById(feedbackId);
    div.innerHTML = msg;

    // if (msg)
    //     input.classList.add("is-invalid");
    // else 
    //     input.classList.remove("is-invalid");
    input.classList.remove("is-invalid");


    input.setCustomValidity(msg);
}

// Validates one input field based on the validation rules. 
// Returns empty string on success and validation error message otherwise.
function validateInput(input) {
    let value = input.value;
    let name = input.id;
    let rules = VALIDATION_JSON['VALIDATION_RULES'][name];

    for (let ruleName in rules) {
        let ruleValue = rules[ruleName];

        let isValid = true;

        if ((ruleName == "required") && (ruleValue)) 
            if (value.length == 0) 
                isValid = false;

        if (ruleName == "min_length")
            if (value.length < ruleValue)
                isValid = false;

        if (ruleName == "max_length")
            if (value.length > ruleValue)
                isValid = false;

        if (ruleName.startsWith("pattern")) {
            const ptrn = new RegExp(ruleValue);
            if (!ptrn.test(value))
                isValid = false;
        }

        if ((ruleName == "numeric") && ruleValue) 
            if (isNaN(value)) 
                isValid = false;

        if (ruleName == "min")
            if (value < ruleValue)
                isValid = false;

        if (ruleName == "max")
            if (value > ruleValue)
                isValid = false;

        if (ruleName == "force_equality") {
            let otherInput = document.getElementById(ruleValue);
            if ((!otherInput.value) || (otherInput.value != value))
                isValid = false;
        }

        if (!isValid) {
            const defaultMsg = ruleName.startsWith("pattern") 
                ? VALIDATION_JSON['VALIDATION_DEFAULT_MESSAGES'].pattern
                : VALIDATION_JSON['VALIDATION_DEFAULT_MESSAGES'][ruleName];

            let errorMsg = VALIDATION_JSON['VALIDATION_MESSAGES'][name]?.[ruleName] || defaultMsg;
            const nameText = VALIDATION_JSON['VALIDATION_MESSAGES'][name]?.text || name;

            errorMsg = errorMsg.replace("%1", nameText).replace("%2", ruleValue);
            errorMsg = errorMsg.charAt(0).toUpperCase() + errorMsg.slice(1) + ".";

            updateValidationMessage(input, errorMsg);
            return errorMsg;
        }
    }
    updateValidationMessage(input, "");
    return "";
}

// Calls validateInput for every input in the field. Returns true if all inputs are valid.
function validateForm(form) {
    let inputFields = form.querySelectorAll("input, select, textarea");
    let isValid = true;
    for (const input of inputFields) {
        let errorMsg = validateInput(input);
        if (errorMsg) 
            isValid = false;
    };
    return isValid;
}

document.addEventListener("DOMContentLoaded", function() {
    let form = document.getElementById("my_form");

    form.addEventListener("submit", function(event) {
        if (validateForm(form)) {
            // alert("SUBMITTING!");
        } else {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add("was-validated");
    });

    if (form.classList.contains("was-validated")) {
        // was-validated was added by server-side validation.
        // This does not seem to trigger setCustomValidity so set them manually:
        let inputFields = form.querySelectorAll("input, select, textarea");
        inputFields.forEach(function(input) {
            if (input.classList.contains("is-invalid")) {
                let feedbackId = input.id + "-feedback";
                let div = document.getElementById(feedbackId);
                input.setCustomValidity(div.innerHTML);
            }
        });
    }

    // listen to inputs:
    let inputFields = form.querySelectorAll("input, select, textarea");
    inputFields.forEach(function(input) {
        input.addEventListener("input", function() {
            input.classList.add("user-modified");

            // in input: close alerts that can be closed:
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let close = alert.querySelector('.btn-close');
                if (close)
                    close.click();
            });

            if (form.classList.contains('was-validated')) {
                validateInput(input);
                // go through VALIDATION_TRIGGERS for the input 
                // to see if we need to validate other inputs as well
                const triggerList = VALIDATION_JSON['VALIDATION_TRIGGERS'][input.id];
                if (triggerList) {
                    for (const otherInputId of triggerList) {
                        const otherInput = document.getElementById(otherInputId);
                        validateInput(otherInput);
                    }
                }
            }
        });
    });
});
