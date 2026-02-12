document.addEventListener('DOMContentLoaded', function() {
    // Add to Cart functionality
    document.querySelectorAll('.add-to-cart-btn').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const productId = this.dataset.productId;

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    // Update cart count in header
                    if (data.cart_count !== undefined) {
                        const cartCountEl = document.getElementById('cart-count');
                        if(cartCountEl) cartCountEl.textContent = data.cart_count;
                    }
                } else if (data.error === 'USER_NOT_LOGGED_IN') {
                    // If user is not logged in, ask them to log in
                    if (confirm('You need to be logged in to add items to the cart. Would you like to log in now?')) {
                        // Redirect to login page, saving the current page to return to
                        window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.pathname);
                    }
                } else {
                    // Handle other errors
                    alert(data.message || 'Error adding product to cart.');
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('An error occurred while adding the product.');
            });
        });
    });

    // Update cart count on page load
    fetch('get_cart_count.php')
        .then(response => response.json())
        .then(data => {
            if (data.cart_count !== undefined) {
                const cartCountEl = document.getElementById('cart-count');
                if(cartCountEl) cartCountEl.textContent = data.cart_count;
            }
        });
});
