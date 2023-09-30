function hideEntriesByFilter() {
    // Get the selected filter from the <select> element
    var filterSelect = document.getElementById("filter");
    var selectedFilter = filterSelect.value;

    // Get all list items
    var items = document.querySelectorAll(".log-entry");

    for (var i = 0; i < items.length; i++) {
        var item = items[i];

        // Check the custom data attribute "data-log-level"
        var logLevel = item.getAttribute("data-log-level");

        // item.style.display = "none";
        if (selectedFilter === "ALL" || logLevel === selectedFilter) {
            item.style.display = "table-row";
        } else {
            item.style.display = "none";
        }
    }
}

document.addEventListener("DOMContentLoaded", function () {
    var filterSelect = document.getElementById("filter");
    filterSelect.addEventListener("change", hideEntriesByFilter);

    // Call the function initially to apply the filter
    hideEntriesByFilter();
});
