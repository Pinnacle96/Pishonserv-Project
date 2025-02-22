document.addEventListener("DOMContentLoaded", function () {
  const track = document.getElementById("testimonial-track");
  const slides = document.querySelectorAll(".testimonial-slide");
  const prevButton = document.getElementById("prev");
  const nextButton = document.getElementById("next");

  let index = 0;
  const totalSlides = slides.length;

  function updateSlidePosition() {
    track.style.transform = `translateX(-${index * 100}%)`;
  }

  function nextSlide() {
    index = (index + 1) % totalSlides;
    updateSlidePosition();
  }

  function prevSlide() {
    index = (index - 1 + totalSlides) % totalSlides;
    updateSlidePosition();
  }

  // Auto-slide every 5 seconds
  setInterval(nextSlide, 5000);

  // Button Listeners
  nextButton.addEventListener("click", nextSlide);
  prevButton.addEventListener("click", prevSlide);
});
