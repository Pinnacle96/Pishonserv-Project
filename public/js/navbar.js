// document.addEventListener("DOMContentLoaded", function () {
//   const navbar = document.getElementById("navbar");
//   const mobileMenuBtn = document.getElementById("mobile-menu-btn");
//   const mobileMenu = document.getElementById("mobile-menu");

//   // Toggle Mobile Menu
//   mobileMenuBtn.addEventListener("click", function () {
//     mobileMenu.classList.toggle("hidden");
//   });

//   // Add sticky effect on scroll
//   window.addEventListener("scroll", function () {
//     if (window.scrollY > 50) {
//       navbar.classList.add("shadow-lg");
//     } else {
//       navbar.classList.remove("shadow-lg");
//     }
//   });
// });
document.addEventListener("DOMContentLoaded", function () {
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

  const userMenuBtn = document.getElementById("user-menu-btn");
  const userDropdown = document.getElementById("user-dropdown");
  if (userMenuBtn && userDropdown) {
    userMenuBtn.addEventListener("click", function (event) {
      event.preventDefault();
      userDropdown.classList.toggle("hidden");
    });

    // Close dropdown when clicking outside
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
