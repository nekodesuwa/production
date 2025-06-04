document.addEventListener("DOMContentLoaded", () => {
    const carousel = document.querySelector(".carousel");
    const items = document.querySelectorAll(".carousel-item");
    let currentIndex = 0;

    function updateCarousel() {
        carousel.style.transform = `translateX(-${currentIndex * 100}%)`;
    }

    document.querySelector(".carousel-control.prev").addEventListener("click", () => {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        updateCarousel();
    });

    document.querySelector(".carousel-control.next").addEventListener("click", () => {
        currentIndex = (currentIndex + 1) % items.length;
        updateCarousel();
    });
});

document.addEventListener('keydown', function(event) {
    if (event.shiftKey && event.key === 'L' && event.target.tagName !== 'INPUT' && event.target.tagName !== 'TEXTAREA') {
        window.location.href = '/pages/HEW_employee_login/employee_login.php';
    }
});

document.addEventListener("DOMContentLoaded", function () {
    const carousel = document.querySelector(".carousel");
    const items = document.querySelectorAll(".carousel-item");
    let currentIndex = 0;
    const totalItems = items.length;
    const intervalTime = 5000;

    function nextSlide() {
        currentIndex = (currentIndex + 1) % totalItems;
        updateCarousel();
    }

    function updateCarousel() {
      const offset = -currentIndex * 100;
        carousel.style.transform = `translateX(${offset}%)`;
    }


    setInterval(nextSlide, intervalTime);
});
