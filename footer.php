    <footer class="site-footer animated-footer">
        <div class="footer-container">
            <div class="footer-column about-column">
                <h4>Rose & Gold</h4>
                <p>Crafting digital elegance with a touch of rose and a hint of gold. We specialize in creating beautiful and functional web experiences.</p>
            </div>
            <div class="footer-column links-column">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Services</a></li>
                    <li><a href="#">Portfolio</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="footer-column contact-column">
                <h4>Contact Us</h4>
                <p>Email: <a href="mailto:info@roseandgold.example">info@roseandgold.example</a></p>
                <p>Phone: +1 (234) 567-890</p>
                <div class="social-media-links">
                    <a href="#" class="social-icon">[FB]</a>
                    <a href="#" class="social-icon">[TW]</a>
                    <a href="#" class="social-icon">[IG]</a>
                    <a href="#" class="social-icon">[LI]</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date("Y"); ?> Rose & Gold Creations. All Rights Reserved. | <a href="admin_products.php" style="color: #F7CAC9; text-decoration: underline;">Admin Products</a></p>
        </div>
    </footer>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userDropdownToggle = document.getElementById('userDropdownToggle');
    const userDropdownMenu = document.getElementById('userDropdownMenu');

    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(event) {
            event.preventDefault();
            userDropdownMenu.classList.toggle('open');
        });

        // Optional: Close dropdown if clicked outside
        document.addEventListener('click', function(event) {
            if (!userDropdownToggle.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                userDropdownMenu.classList.remove('open');
            }
        });
    }
});
</script>
</body>
</html>
