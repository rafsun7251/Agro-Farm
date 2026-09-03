document.addEventListener("DOMContentLoaded", function () {

    // ==========================================
    // ADMIN DASHBOARD CONTROLLER
    // ==========================================

    console.log("Admin Dashboard Controller Loaded");

    // ------------------------------------------
    // Sidebar Menu Active State
    // ------------------------------------------

    const menuItems = document.querySelectorAll(".menu-item");

    menuItems.forEach(function (item) {

        item.addEventListener("click", function () {

            // Remove active class from all menu items
            menuItems.forEach(function (menuItem) {
                menuItem.classList.remove("active");
            });

            // Add active class to selected menu item
            this.classList.add("active");
        });

    });


    // ------------------------------------------
    // Quick Action Button Animation
    // ------------------------------------------

    const actionButtons = document.querySelectorAll(".action-button");

    actionButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            this.classList.add("clicked");

            setTimeout(() => {
                this.classList.remove("clicked");
            }, 200);

        });

    });


    // ------------------------------------------
    // Statistics Card Interaction
    // ------------------------------------------

    const statCards = document.querySelectorAll(".stat-card");

    statCards.forEach(function (card) {

        card.addEventListener("click", function () {

            statCards.forEach(function (statCard) {
                statCard.classList.remove("selected");
            });

            this.classList.add("selected");

        });

    });


    // ------------------------------------------
    // Admin Profile Interaction
    // ------------------------------------------

    const adminProfile = document.querySelector(".admin-profile");

    if (adminProfile) {

        adminProfile.addEventListener("click", function () {

            this.classList.toggle("profile-active");

        });

    }


    // ------------------------------------------
    // Dashboard Loaded
    // ------------------------------------------

    console.log("Admin Dashboard initialized successfully.");

});