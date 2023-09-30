let baseList = null;            // list of all currency bases
let currentBase = null;         // current currency base
let currentData = null;         // json-object for the current currency base
changeBase('USD');

document.querySelector('#amount1').addEventListener('change', () => updateAmount(2));
document.querySelector('#amount2').addEventListener('change', () => updateAmount(1));
document.querySelector('#base1').addEventListener('change', () => updateBase(1));
document.querySelector('#base2').addEventListener('change', () => updateBase(2));

// Called when 1st select box is changed and once at start, fetches new currency json
// and updates the variables currentBase, currentData. Also generates baseList when first called.
function changeBase(newBase) {
    // We use an intermediate php script to fetch the data so that we can hide the api-key
    // in a file that is not accessible to the user:
    fetch(`php-fetch.php?base=${newBase}`)
    .then(response => response.json())
    .then(result => {
        if (result['error']) {
            console.log('Error:', result['error']);
        } else {
            if (!baseList) {
                baseList = Object.keys(result.rates);
                updateSelection();
            }
            currentBase = newBase;
            currentData = result;
            updateAmount(2);
        }
    })
    .catch(error => {
        console.log('Error:', error);
    });
}

// Called to update #amount(index) text input field
// Also generates #spam-area if second select box option --- is used
function updateAmount(index) {
    let amount1 = document.querySelector('#amount1');
    let amount2 = document.querySelector('#amount2');
    let exchangeRate = currentData ? Number(currentData.rates[getSelectedBase(2)]) : undefined;
    if (index === 1) 
        amount1.value = (Number(amount2.value) / exchangeRate).toFixed(2);
    else 
        amount2.value = (Number(amount1.value) * exchangeRate).toFixed(2);
    if (isNaN(amount1.value))
        amount1.value = '';
    if (isNaN(amount2.value))
        amount2.value = '';

    // update spam-area
    let spam = document.querySelector('#spam-area');
    if (getSelectedBase(2) === '---') {
        spam.style.display = 'block';
        let txt = '';
        baseList.forEach( (base) => {
            let value1 = Number(amount1.value).toFixed(2);
            let value2 = (value1*Number(currentData.rates[base])).toFixed(2);
            txt = txt + `${value1} ${currentBase} <--> ${value2} ${base}\n`;
        });
        spam.innerHTML = txt;
    } else {
        spam.style.display = 'none';
    }
}

// Returns content of the select box of given index (1 or 2)
function getSelectedBase(index) {
    let select = document.querySelector(`#base${index}`);
    return select.options[select.selectedIndex].text;
}

// Called when selected option is changed on the select input
function updateBase(index) {
    let newBase = getSelectedBase(index);
    if (index === 1)
        changeBase(newBase);
    else {
        updateAmount(2);
    }
}

// Called to generate options for select inputs
function updateSelection() {
    let selectElements = document.querySelectorAll('.container-base');
    selectElements.forEach( (select) => {
        // clear the select options if needed
        while (select.options.length) 
            select.options.remove(0);
        if (select.id === 'base2') {
            let option = document.createElement('option');
            option.text = '---';
            select.add(option);
        }
        // add new select options
        baseList.forEach( (base) => {
            let option = document.createElement('option');
            option.text = base;
            select.add(option);
        });
    });
}