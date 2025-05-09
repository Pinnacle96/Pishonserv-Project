document.addEventListener("DOMContentLoaded", function () {
  const currentPage = window.location.pathname.split("/").pop();

  document.querySelectorAll(".nav-link").forEach((link) => {
    const href = link.getAttribute("href");
    const hrefPage = href.split("/").pop(); // normalize "../index.php" => "index.php"

    if (hrefPage === currentPage) {
      link.classList.add("active-link");
    }
  });

  // Mobile menu toggle
  const menuBtn = document.getElementById("mobile-menu-btn");
  const mobileMenu = document.getElementById("mobile-menu");

  if (!menuBtn || !mobileMenu) {
    console.error("Menu button or mobile menu not found!");
    return;
  }

  menuBtn.addEventListener("click", function () {
    console.log("Button clicked!");
    mobileMenu.classList.toggle("hidden");
  });

  // User dropdown toggle
  const userMenuBtn = document.getElementById("user-menu-btn");
  const userDropdown = document.getElementById("user-dropdown");

  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener("click", function (event) {
      event.preventDefault();
      userDropdown.classList.toggle("hidden");
    });

    document.addEventListener("click", function (event) {
      if (
        !userMenuBtn.contains(event.target) &&
        !userDropdown.contains(event.target)
      ) {
        userDropdown.classList.add("hidden");
      }
    });
  }
});
