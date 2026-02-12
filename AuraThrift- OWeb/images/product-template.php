php
// This template expects $products to be passed
if (!isset($products)) {
    $products = [];
}
?>

<header>
    <div class="cart-link">
        <img src="images/OIP.jpeg" alt="Cart" height="20">
        <span id="cart-count">0</span>
    </div>
</header>

<section class="browse-content">
    <div class="filter-sidebar">
        <h2>Filter by</h2>
        <div class="filter-group">
            <h3>Sport</h3>
            <label><input type="checkbox" class="filter-sport" value="Soccer"> Soccer</label><br>
            <label><input type="checkbox" class="filter-sport" value="Rugby"> Rugby</label><br>
            <label><input type="checkbox" class="filter-sport" value="Basketball"> Basketball</label><br>
            <label><input type="checkbox" class="filter-sport" value="Cricket"> Cricket</label><br>
            <label><input type="checkbox" class="filter-sport" value="Tennis"> Tennis</label><br>
        </div>

        <div class="filter-group">
            <h3>Size</h3>
            <select id="filter-size">
                <option value="">Any</option>
                <option value="S">Small (S)</option>
                <option value="M">Medium (M)</option>
                <option value="L">Large (L)</option>
                <option value="XL">X-Large (XL)</option>
            </select>
        </div>

        <div class="filter-group">
            <h3>Price Range (R)</h3>
            <input type="number" id="min-price" placeholder="Min Price"> -
            <input type="number" id="max-price" placeholder="Max Price">
        </div>

        <button id="apply-filters-btn">Apply Filters</button>
        <button id="clear-filters-btn">Clear Filters</button>
    </div>

    <div class="search-results">
        <h2>Products</h2>
        <div class="listings-container" id="filtered-listings-container">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                    <p class="sport"><?php echo htmlspecialchars($product['sport']); ?></p>
                    <p class="size"><?php echo htmlspecialchars($product['size']); ?></p>
                    <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                    <button class="add-to-cart-btn" data-product-id="<?php echo $product['id']; ?>">
                        Add to Cart
                    </button>
                </div>
            <?php endforeach; ?>
            <p id="no-results-message" style="display: none;">No items match your search criteria.</p>
        </div>
    </div>
</section>

<script src="../script.js"></script>
