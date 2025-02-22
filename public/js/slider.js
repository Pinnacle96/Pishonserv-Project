document.addEventListener("DOMContentLoaded", function () {
  let slider = document.getElementById("testimonial-slider");
  let slides = slider.children;
  let index = 0;

  function nextSlide() {
    index = (index + 1) % slides.length;
    slider.style.transform = `translateX(-${index * 100}%)`;
  }

  setInterval(nextSlide, 5000);
});
document.addEventListener("DOMContentLoaded", function () {
  let slides = document.querySelectorAll(".slide");
  let index = 0;

  function nextSlide() {
    slides[index].classList.remove("opacity-100");
    slides[index].classList.add("opacity-0");

    index = (index + 1) % slides.length;

    slides[index].classList.remove("opacity-0");
    slides[index].classList.add("opacity-100");
  }

  setInterval(nextSlide, 5000);
});
