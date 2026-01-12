// Helper function to set cookie
function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + JSON.stringify(value) + ';expires=' + expires.toUTCString() + ';path=/';
}

// Helper function to get cookie
function getCookie(name) {
    const nameEQ = name + '=';
    const ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) {
            try {
                return JSON.parse(c.substring(nameEQ.length, c.length));
            } catch (e) {
                return [];
            }
        }
    }
    return [];
}

// Add to cart functionality
function addToCart(productId, productName, price) {
    let cart = getCookie('cart');
    
    // Check if product already in cart
    const existingItem = cart.find(item => item.productId === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            productId: productId,
            productName: productName,
            price: price,
            quantity: 1
        });
    }
    
    setCookie('cart', cart, 30);
    updateCartCount();
    
    // Show success notification
    showNotification('✅ Product added to cart!', 'success');
}

// Show notification
function showNotification(message, type = 'success') {
    // Remove existing notification if any
    const existing = document.querySelector('.cart-notification');
    if (existing) {
        existing.remove();
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'cart-notification ' + type;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Update cart count in navigation
function updateCartCount() {
    const cart = getCookie('cart');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartBadge = document.getElementById('cart-count');
    if (cartBadge) {
        cartBadge.textContent = totalItems;
    }
}

// Initialize cart count on page load
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.addEventListener('submit', function(e) {
            const inputs = form.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#dc3545';
                } else {
                    input.style.borderColor = '#ddd';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    }
}

// Search functionality
function searchProducts() {
    const searchTerm = document.getElementById('search-input').value.toLowerCase();
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productName = card.querySelector('.product-name').textContent.toLowerCase();
        if (productName.includes(searchTerm)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Category filter
function filterByCategory(category) {
    const productCards = document.querySelectorAll('.product-card');
    
    productCards.forEach(card => {
        const productCategory = card.getAttribute('data-category');
        if (category === 'all' || productCategory === category) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Close welcome message
function closeWelcomeMessage() {
    const welcomeMsg = document.getElementById('welcomeMessage');
    if (welcomeMsg) {
        welcomeMsg.style.animation = 'welcomeSlideOut 0.5s ease forwards';
        setTimeout(() => {
            welcomeMsg.remove();
        }, 500);
    }
}

// Auto-close welcome message after 4 seconds (matches progress bar animation)
document.addEventListener('DOMContentLoaded', function() {
    const welcomeMsg = document.getElementById('welcomeMessage');
    if (welcomeMsg) {
        // Auto-dismiss after 4 seconds
        setTimeout(() => {
            closeWelcomeMessage();
        }, 4000);
    }
});

