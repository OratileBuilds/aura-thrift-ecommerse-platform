<?php
session_start();
include 'config.php';

// Fetch all approved products
$stmt = $conn->prepare("SELECT * FROM products WHERE status = 'approved' ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
$products = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

$page_title = 'Browse All Products';
include 'header.php';
?>

<main class="container browse-page">
    <h2>All Products</h2>
    <div class="products-grid">
        <?php if (empty($products)):
            echo "<p>No products found. Please check back later!</p>";
        else:
            foreach ($products as $product):
?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                    <button type="button" class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        Add to Cart
                    </button>
                </div>
            <?php 
            endforeach;
        endif;
        ?>
    </div>
</main>

<?php include 'footer.php'; ?>

<script>
function showLoginPrompt() {
    const modal = document.getElementById('login-modal');
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('login-modal');
    modal.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('login-modal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

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
                    const cartCountEl = document.getElementById('cart-count');
                    if(cartCountEl && data.cart_count !== undefined) {
                        cartCountEl.textContent = data.cart_count;
                    }
                } else {
                    if (data.error === 'USER_NOT_LOGGED_IN') {
                        alert('Please log in to add items to your cart.');
                        window.location.href = 'login.php';
                    } else {
                        alert(data.message || 'Error adding product to cart.');
                    }
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                alert('An error occurred while adding the product.');
            });
        });
    });
});
</script>

<style>
    .btn-secondary {
        background-color: #6c757d;
        color: white;
        border: 1px solid #6c757d;
        padding: 8px 16px;
        font-size: 0.9rem;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background-color: #5a6268;
        border-color: #545b62;
        transform: translateY(-1px);
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    /* Login Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.5);
        z-index: 1000;
    }

    .modal-content {
        background-color: white;
        margin: 15% auto;
        padding: 20px;
        border-radius: 8px;
        width: 90%;
        max-width: 400px;
        text-align: center;
    }

    .modal h3 {
        margin-bottom: 15px;
        color: #333;
    }

    .modal p {
        margin-bottom: 20px;
        color: #666;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
    }

    .btn-primary {
        background-color: #007bff;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .modal button {
        background-color: #6c757d;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .modal button:hover {
        background-color: #5a6268;
    }
</style>