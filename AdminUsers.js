document.addEventListener("DOMContentLoaded", function () {

    const searchInput = document.getElementById("searchInput");
    const roleFilter = document.getElementById("roleFilter");
    const rows = document.querySelectorAll(
        "#userTable tbody .user-row"
    );


    // ==========================================
    // SEARCH USERS
    // ==========================================

    function searchUsers() {

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
    // FILTER USERS BY ROLE
    // ==========================================

    function filterUsers() {

        const selectedRole = roleFilter.value
            .toLowerCase()
            .trim();

        rows.forEach(function (row) {

            const roleElement = row.querySelector(".role");

            if (!roleElement) {
                return;
            }

            const role = roleElement.innerText
                .toLowerCase()
                .trim();

            row.style.display =
                selectedRole === "" ||
                role === selectedRole
                    ? ""
                    : "none";
        });

    }


    // ==========================================
    // SEARCH EVENT
    // ==========================================

    if (searchInput) {

        searchInput.addEventListener(
            "keyup",
            searchUsers
        );

    }


    // ==========================================
    // ROLE FILTER EVENT
    // ==========================================

    if (roleFilter) {

        roleFilter.addEventListener(
            "change",
            filterUsers
        );

    }

});
const searchButton = document.getElementById("searchButton");

if (searchButton) {

    searchButton.addEventListener(
        "click",
        searchUsers
    );

}