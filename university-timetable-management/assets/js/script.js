document.addEventListener("DOMContentLoaded", function () {
    const logo = document.querySelector(".navbar-brand"); // Select the logo
    const sidebar = document.querySelector(".sidebar"); // Use ID selector
    const mainContent = document.querySelector(".main-content");

    if (logo && sidebar && mainContent) {
        logo.addEventListener("click", function () {
            sidebar.classList.toggle("show"); // Toggle sidebar visibility
            mainContent.classList.toggle("shifted"); // Adjust content margin
        });
    }
});

