document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchInput");
    const searchButton = document.getElementById("searchButton");
    const statusFilter = document.getElementById("statusFilter");
    const methodFilter = document.getElementById("methodFilter");

    const rows = document.querySelectorAll(
        "#paymentTable tbody .payment-row"
    );

    function filterPayments() {

        const searchValue = searchInput.value
            .toLowerCase()
            .trim();

        const statusValue = statusFilter.value
            .toLowerCase()
            .trim();

        const methodValue = methodFilter.value
            .toLowerCase()
            .trim();

        rows.forEach(function (row) {

            const rowText = row.innerText.toLowerCase();

            const rowStatus = row.dataset.status
                .toLowerCase()
                .trim();

            const rowMethod = row.dataset.method
                .toLowerCase()
                .trim();

            const matchesSearch =
                searchValue === "" ||
                rowText.includes(searchValue);

            const matchesStatus =
                statusValue === "" ||
                rowStatus === statusValue;

            const matchesMethod =
                methodValue === "" ||
                rowMethod === methodValue;

            row.style.display =
                matchesSearch &&
                matchesStatus &&
                matchesMethod
                    ? ""
                    : "none";
        });
    }

    if (searchInput) {
        searchInput.addEventListener("keyup", filterPayments);
    }

    if (searchButton) {
        searchButton.addEventListener("click", filterPayments);
    }

    if (statusFilter) {
        statusFilter.addEventListener("change", filterPayments);
    }

    if (methodFilter) {
        methodFilter.addEventListener("change", filterPayments);
    }

});