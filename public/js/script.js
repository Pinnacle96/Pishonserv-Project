document.addEventListener("DOMContentLoaded", function () {
  // Image Slider Functionality
  let images = document.querySelectorAll("#hero-slider img");
  let index = 0;

  function slideShow() {
    images.forEach((img, i) => {
      img.style.opacity = i === index ? "1" : "0";
    });

    index = (index + 1) % images.length;
  }

  setInterval(slideShow, 4000); // Change image every 4 seconds

  // Tab Click Functionality
  let tabButtons = document.querySelectorAll(".tab-btn");
  let categoryInput = document.getElementById("search-category");

  tabButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Remove active class from all buttons
      tabButtons.forEach((btn) =>
        btn.classList.remove(
          "text-[#092468]",
          "font-bold",
          "border-b-2",
          "border-[#F4A124]"
        )
      );
      tabButtons.forEach((btn) => btn.classList.add("text-gray-600"));

      // Add active class to clicked button
      this.classList.remove("text-gray-600");
      this.classList.add(
        "text-[#092468]",
        "font-bold",
        "border-b-2",
        "border-[#F4A124]"
      );

      // Update hidden category input field
      categoryInput.value = this.getAttribute("data-tab");
    });
  });
});
