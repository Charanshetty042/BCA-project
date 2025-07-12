<!-- Footer -->
<footer style="background-color: #A3D1C6; color: #333;" class="py-4 animate-footer" data-aos="fade-up">
    <div class="container text-center">
        <div class="footer-links">
            <a href="index.php#home" class="text-dark">Home</a>
            <a href="index.php#features" class="text-dark">Services</a>
            <a href="index.php#about" class="text-dark">About Us</a>
            <a href="index.php#contact" class="text-dark">Contact</a>
            <a href="privacy.html" class="text-dark">Privacy Policy</a>
            <a href="terms.html" class="text-dark">Terms of Service</a>
        </div>
        <div class="social-icons">
            <a href="#" class="text-dark"><i class="fab fa-facebook"></i></a>
            <a href="#" class="text-dark"><i class="fab fa-twitter"></i></a>
            <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-dark"><i class="fab fa-linkedin"></i></a>
        </div>
        <p>&copy; 2025 LocalGoodsTransit. All Rights Reserved.</p>
    </div>
</footer>
<style>
@keyframes slideUpFadeIn {
  0% {
    opacity: 0;
    transform: translateY(60px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-footer {
  opacity: 0;
  transition: opacity 0.3s;
}
.animate-footer.visible {
  animation: slideUpFadeIn 1.2s cubic-bezier(0.4,0,0.2,1);
  opacity: 1;
}
</style>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
<!-- AOS JS -->
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        once: false,
    });

    // Show footer animation when it enters the viewport
    function onFooterInView() {
        var footer = document.querySelector('.animate-footer');
        if (!footer) return;
        var rect = footer.getBoundingClientRect();
        var windowHeight = (window.innerHeight || document.documentElement.clientHeight);
        if (rect.top < windowHeight) {
            footer.classList.add('visible');
            window.removeEventListener('scroll', onFooterInView);
        }
    }
    window.addEventListener('scroll', onFooterInView);
    window.addEventListener('DOMContentLoaded', onFooterInView);
</script>
</body>
</html>
