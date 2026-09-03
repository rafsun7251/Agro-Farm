document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchInput");
    const categoryFilter = document.getElementById("categoryFilter");
    const statusFilter = document.getElementById("statusFilter");
    const searchButton = document.getElementById("searchButton");

    const rows = document.querySelectorAll(
        "#cropTable tbody .crop-row"
    );


    // ==========================================
    // SEARCH CROPS
    // ==========================================

    function searchCrops() {

        const searchValue = searchInput.value
            .toLowerCase()
            .trim();

        rows.forEach(function (row) {

            const rowText = row.innerText.toLowerCase();

            row.style.display =
                rowText.includes(searchValue)
                    ? ""
                    : "none";
        });

    }


    // ==========================================
    // FILTER CROPS
    // ==========================================

    function filterCrops() {

        const category = categoryFilter.value
            .toLowerCase()
            .trim();

        const status = statusFilter.value
            .toLowerCase()
            .trim();

        rows.forEach(function (row) {

            const categoryElement =
                row.querySelector(".category");

            const statusElement =
                row.querySelector(".status");

            if (!categoryElement || !statusElement) {
                return;
            }

            const rowCategory =
                categoryElement.innerText
                    .toLowerCase()
                    .trim();

            const rowStatus =
                statusElement.innerText
                    .toLowerCase()
                    .trim();

            const categoryMatch =
                category === "" ||
                rowCategory === category;

            const statusMatch =
                status === "" ||
                rowStatus === status;

            row.style.display =
                categoryMatch && statusMatch
                    ? ""
                    : "none";
        });

    }


    // ==========================================
    // SEARCH BUTTON
    // ==========================================

    if (searchButton) {

        searchButton.addEventListener(
            "click",
            searchCrops
        );

    }


    // ==========================================
    // SEARCH WHILE TYPING
    // ==========================================

    if (searchInput) {

        searchInput.addEventListener(
            "keyup",
            searchCrops
        );

    }


    // ==========================================
    // CATEGORY FILTER
    // ==========================================

    if (categoryFilter) {

        categoryFilter.addEventListener(
            "change",
            filterCrops
        );

    }


    // ==========================================
    // STATUS FILTER
    // ==========================================

    if (statusFilter) {

        statusFilter.addEventListener(
            "change",
            filterCrops
        );

    }


    console.log("Admin Crop Management initialized.");

});