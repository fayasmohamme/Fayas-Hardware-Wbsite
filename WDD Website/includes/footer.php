    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>🔧 FayasHardware</h3>
                    <p>Your trusted hardware store for all your needs.</p>
                </div>
                <div class="footer-section">
                    <h3>Contact Us</h3>
                    <p>Email: info@ecommerce.com</p>
                    <p>Phone: +1 (555) 123-4567</p>
                    <p>Address: 123 Main Street, City, State 12345</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="products.php">Products</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> FayasHardware. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <?php require_once 'config/functions.php'; ?>
    <script src="<?php echo getAssetUrl('js/script.js'); ?>"></script>
</body>
</html>

