document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchInput");
    const searchButton = document.getElementById("searchButton");
    const statusFilter = document.getElementById("statusFilter");

    const rows = document.querySelectorAll(
        "#deliveryTable tbody .delivery-row"
    );

    function searchDeliveries() {
        const searchValue = searchInput.value
            .toLowerCase()
            .trim();

        rows.forEach(function (row) {
            const rowText = row.innerText.toLowerCase();

            row.style.display =
                rowText.includes(searchValue) ? "" : "none";
        });
    }

    function filterDeliveries() {
        const selectedStatus = statusFilter.value
            .toLowerCase()
            .trim();

        rows.forEach(function (row) {

            const statusElement = row.querySelector(".status");

            if (!statusElement) {
                return;
            }

            const rowStatus = statusElement.innerText
                .toLowerCase()
                .trim();

            row.style.display =
                selectedStatus === "" ||
                rowStatus === selectedStatus
                    ? ""
                    : "none";
        });
    }

    if (searchInput) {
        searchInput.addEventListener("keyup", searchDeliveries);
    }

    if (searchButton) {
        searchButton.addEventListener("click", searchDeliveries);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", filterDeliveries);
    }

});