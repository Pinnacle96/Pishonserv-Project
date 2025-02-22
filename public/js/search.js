document.addEventListener("DOMContentLoaded", function () {
  let tabs = document.querySelectorAll(".tab-button");
  let categoryInput = document.getElementById("search-category");

  tabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      // Remove active state from all tabs
      tabs.forEach((t) => t.classList.remove("border-[#F4A124]", "border-b-4"));

      // Add active state to the clicked tab
      this.classList.add("border-[#F4A124]", "border-b-4");

      // Update hidden category input value
      categoryInput.value = this.dataset.category;
    });
  });
});
