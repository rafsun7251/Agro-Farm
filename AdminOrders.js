document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchInput");
    const searchButton = document.getElementById("searchButton");
    const statusFilter = document.getElementById("statusFilter");

    const rows = document.querySelectorAll(
        "#orderTable tbody .order-row"
    );

    function searchOrders() {
        const searchValue = searchInput.value.toLowerCase().trim();

        rows.forEach(function (row) {
            const rowText = row.innerText.toLowerCase();

            row.style.display = rowText.includes(searchValue)
                ? ""
                : "none";
        });
    }

    function filterOrders() {
        const selectedStatus =
            statusFilter.value.toLowerCase().trim();

        rows.forEach(function (row) {

            const statusElement =
                row.querySelector(".status");

            if (!statusElement) {
                return;
            }

            const rowStatus =
                statusElement.innerText
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
        searchInput.addEventListener("keyup", searchOrders);
    }

    if (searchButton) {
        searchButton.addEventListener("click", searchOrders);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", filterOrders);
    }

});
    const deleteButtons =
        document.querySelectorAll(".delete-btn");

    deleteButtons.forEach(function (button) {

        button.addEventListener("click", function (event) {

            const confirmed = confirm(
                "Are you sure you want to delete this order?"
            );

            if (!confirmed) {
                event.preventDefault();
            }

        });

    });