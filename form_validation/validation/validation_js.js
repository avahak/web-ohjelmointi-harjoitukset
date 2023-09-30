"use strict";

const ALLOW_VALID_SUBMIT = true; 
const ALLOW_INVALID_SUBMIT = false;  // use true to debug php validation
const DEBUG = false;

// Returns all inputs with given name that are checked in the form.
// Use to get selected values for radio button or checkbox.
function getAllChecked(form, name) {
    let checkedList = [];
    const inputs = form.querySelectorAll('[name="' + name + '"]');
    for (const input of inputs) 
        if (input.checked)
            checkedList.push(input.value);
    return checkedList;
}

// Cuts brackets [] off from the end of the string
function cutBrackets(str) {
    return (str.endsWith("[]") ? str.substring(0, str.length-2) : str);
}

// Updates the validation status of the input. 
function updateValidationMessage(form, input, msg) {
    // console.log("updateValidationMessage", input.id, input.name, input.type, msg);
    let feedbackId = cutBrackets(input.name) + "-feedback";
    let div = document.getElementById(feedbackId);
    if (!div) 
        return;

    div.innerHTML = msg;

    updateValidity(form, input.name, msg);
}

// Update all inputs with same name (multiple in case of radio/checkbox):
function updateValidity(form, name, msg) {
    // console.log("updateValidity", name, msg);
    const inputsWithSameName = form.querySelectorAll('[name="' + name + '"], [name="' + name + '[]"]');
    // console.log("inputsWithSameName", inputsWithSameName);
    for (const inputNamesake of inputsWithSameName) {
        // inputNamesake.classList.remove("is-invalid");
        // console.log("updateValidity found element", inputNamesake);
        inputNamesake.setCustomValidity(msg);
    }
}

// Validates one input field based on the validation rules. 
// Returns empty string on success and validation error message otherwise.
function validateInput(form, input) {
    let value = input.disabled ? "" : input.value;

    let allChecked = null;
    if (input.type == "radio") {
        allChecked = getAllChecked(form, input.name);
        // console.log("getAllChecked", input.id, allChecked);
        value = (allChecked.length != 0 ? allChecked[0] : "");
    } else if (input.type == "checkbox") {
        allChecked = getAllChecked(form, input.name);
        // console.log("getAllChecked", input.id, allChecked);
        value = (allChecked.length != 0 ? allChecked.join(",") : "");
    } else if (input.type == "select-one") {
        const selectedOption = input.options[input.selectedIndex];
        value = selectedOption.disabled ? "" : value;
    }

    // console.log("validateInput: input, input.type, value = ", input, input.type, value);

    let name = cutBrackets(input.name);
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
            if (otherInput.value != value)
                isValid = false;
        }

        if ((ruleName == "min_selected") && (allChecked != null)) 
            if (allChecked.length < ruleValue)
                isValid = false;

        if ((ruleName == "max_selected") && (allChecked != null)) 
            if (allChecked.length > ruleValue)
                isValid = false;

        // console.log("validation result for ", ruleName, isValid);

        if (!isValid) {
            const defaultMsg = ruleName.startsWith("pattern") 
                ? VALIDATION_JSON['VALIDATION_DEFAULT_MESSAGES'].pattern
                : VALIDATION_JSON['VALIDATION_DEFAULT_MESSAGES'][ruleName];

            let errorMsg = VALIDATION_JSON['VALIDATION_MESSAGES'][name]?.[ruleName] || defaultMsg;
            const nameText = VALIDATION_JSON['VALIDATION_MESSAGES'][name]?.text || name;

            errorMsg = errorMsg.replace("%1", nameText).replace("%2", ruleValue);
            errorMsg = errorMsg.charAt(0).toUpperCase() + errorMsg.slice(1);

            updateValidationMessage(form, input, errorMsg);
            return errorMsg;
        }
    }
    updateValidationMessage(form, input, "");
    return "";
}

// Calls validateInput for every input in the form. Returns true if all inputs are valid.
function validateForm(form) {
    try {
        let inputFields = form.querySelectorAll("input, select, textarea");
        let isValid = true;
        for (const input of inputFields) {
            let errorMsg = validateInput(form, input);
            if (errorMsg) 
                isValid = false;
        };
        return isValid;
    } catch (error) {
        console.log("Exception caught during validation:", error);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    let form = document.getElementById(VALIDATION_JSON['FORM_ID']);

    form.addEventListener("submit", function(event) {
        if (validateForm(form)) {
            if (!ALLOW_VALID_SUBMIT) {
                event.preventDefault();
                event.stopPropagation();
            }
            // alert("SUBMITTING!");
        } else {
            if (!ALLOW_INVALID_SUBMIT) {
                event.preventDefault();
                event.stopPropagation();
            }
        }
        form.classList.add("was-validated");
    });

    if (form.classList.contains("was-validated")) {
        // was-validated was added by server-side validation.
        // This does not seem to trigger setCustomValidity so set them manually:
        let inputFields = form.querySelectorAll("input, select, textarea");
        inputFields.forEach(function(input) {
            if (input.classList.contains("is-invalid")) {
                let feedbackId = cutBrackets(input.name) + "-feedback";
                let div = document.getElementById(feedbackId);
                if (div)
                    updateValidity(form, input.name, div.innerHTML);
            }
        });
    }

    // listen to inputs:
    let inputFields = form.querySelectorAll("input, select, textarea");
    inputFields.forEach(function(input) {
        input.addEventListener("input", function() {
            // console.log("inputFields element is", input.id);
            input.classList.add("user-modified");

            // in input: close alerts that can be closed:
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                let close = alert.querySelector('.btn-close');
                if (close)
                    close.click();
            });

            if (form.classList.contains('was-validated')) {
                validateInput(form, input);
                // go through VALIDATION_TRIGGERS for the input 
                // to see if we need to validate other inputs as well
                const triggerList = VALIDATION_JSON['VALIDATION_TRIGGERS'][input.name];
                if (triggerList) {
                    for (const otherInputName of triggerList) {
                        const otherInputs = form.querySelectorAll('[name="' + otherInputName + '"]');;
                        for (const otherInput of otherInputs)
                            validateInput(form, otherInput);
                    }
                }
            }
        });
    });
});
