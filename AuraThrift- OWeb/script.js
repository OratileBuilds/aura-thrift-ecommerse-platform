
// --- Homepage Search Button ---
document.getElementById('search-button').addEventListener('click', function () {
    const query = document.getElementById('search-input').value.trim();
    if (query !== "") {
        window.location.href = "browse.html?search=" + encodeURIComponent(query);
    }
});



// --- Image Rotation Logic ---
const heroImage = document.getElementById('heroImage');
const images = ['images/son2.jpg', 'images/Rodrygo.jpg', 'images/foden.jpg']; // update with your actual paths
let currentIndex = 0;

setInterval(() => {
  currentIndex = (currentIndex + 1) % images.length;
  heroImage.src = images[currentIndex];
}, 3000); // I set the images to change after 3 seconds 

  document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("mobileMenuToggle");
    const nav = document.getElementById("mainNav");
    
    toggle.addEventListener("click", function () {
      nav.classList.toggle("open");
    });
  });




