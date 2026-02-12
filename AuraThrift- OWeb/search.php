<?php
session_start();
require_once 'config.php';

// Get search parameters
$query = $_GET['query'] ?? '';
$sport = $_GET['sport'] ?? '';
$size = $_GET['size'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'name';
$order = $_GET['order'] ?? 'asc';
$page = $_GET['page'] ?? 1;
$items_per_page = 12;

// Calculate pagination
$offset = ($page - 1) * $items_per_page;

// Build the SQL query
$sql = "SELECT * FROM products WHERE 1=1";

if (!empty($query)) {
    // Handle specific product searches
    $search_terms = strtolower($conn->real_escape_string($query));
    
    // First try exact match
    $sql_exact = "SELECT * FROM products WHERE 1=1";
    $sql_exact .= " AND (LOWER(name) = '" . $search_terms . "'
                  OR LOWER(description) = '" . $search_terms . "'
                  OR LOWER(name) LIKE '%" . $search_terms . "%'
                  OR LOWER(description) LIKE '%" . $search_terms . "%')";
    
    $result_exact = $conn->query($sql_exact);
    
    if ($result_exact->num_rows == 0) {
        // If no exact match, try partial match
        $sql .= " AND (LOWER(name) LIKE '%" . $search_terms . "%'
                OR LOWER(description) LIKE '%" . $search_terms . "%')";
    }
}

if (!empty($sport)) {
    $sql .= " AND sport = '" . $conn->real_escape_string($sport) . "'";
}

if (!empty($size)) {
    $sql .= " AND size = '" . $conn->real_escape_string($size) . "'";
}

if (!empty($min_price)) {
    $sql .= " AND price >= " . $conn->real_escape_string($min_price);
}

if (!empty($max_price)) {
    $sql .= " AND price <= " . $conn->real_escape_string($max_price);
}

// Get total results for pagination
$total_sql = "SELECT COUNT(*) as total FROM products WHERE 1=1";
if (!empty($query)) {
    $total_sql .= " AND (LOWER(name) LIKE '%" . strtolower($conn->real_escape_string($query)) . "%'
                  OR LOWER(description) LIKE '%" . strtolower($conn->real_escape_string($query)) . "%')";
}

$total_result = $conn->query($total_sql);
$total_row = $total_result->fetch_assoc();
$total_results = $total_row['total'];
$total_pages = ceil($total_results / $items_per_page);

// Add sorting and pagination
$sql .= " ORDER BY " . $conn->real_escape_string($sort) . " " . $conn->real_escape_string($order) . 
        " LIMIT " . $conn->real_escape_string($items_per_page) . 
        " OFFSET " . $conn->real_escape_string($offset);

$result = $conn->query($sql);

$page_title = 'Search - AuraThrift';
include 'header.php';
?>

<main class="search-page">
    <div class="search-container">
        <div class="search-header">
            <h1>Search Results</h1>
            <div class="search-stats">
                <?php if ($total_results > 0): ?>
                    <p><?php echo $total_results; ?> results found</p>
                <?php else: ?>
                    <p>No results found</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="search-filters">
            <form method="GET" class="search-form">
                <input type="hidden" name="page" value="1">
                
                <div class="search-input-group">
                    <input type="text" name="query" 
                           value="<?php echo htmlspecialchars($query); ?>" 
                           placeholder="Search for products..." 
                           class="search-input">
                    <button type="submit" class="search-button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div class="filter-group">
                    <label for="sport">Sport:</label>
                    <select name="sport" id="sport" class="filter-select">
                        <option value="">All Sports</option>
                        <option value="Soccer" <?php echo $sport == 'Soccer' ? 'selected' : ''; ?>>Soccer</option>
                        <option value="Basketball" <?php echo $sport == 'Basketball' ? 'selected' : ''; ?>>Basketball</option>
                        <option value="Rugby" <?php echo $sport == 'Rugby' ? 'selected' : ''; ?>>Rugby</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="size">Size:</label>
                    <select name="size" id="size" class="filter-select">
                        <option value="">All Sizes</option>
                        <option value="Small" <?php echo $size == 'Small' ? 'selected' : ''; ?>>Small</option>
                        <option value="Medium" <?php echo $size == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                        <option value="Large" <?php echo $size == 'Large' ? 'selected' : ''; ?>>Large</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="min_price">Min Price:</label>
                    <input type="number" name="min_price" 
                           id="min_price" 
                           value="<?php echo htmlspecialchars($min_price); ?>" 
                           class="filter-input">
                </div>

                <div class="filter-group">
                    <label for="max_price">Max Price:</label>
                    <input type="number" name="max_price" 
                           id="max_price" 
                           value="<?php echo htmlspecialchars($max_price); ?>" 
                           class="filter-input">
                </div>

                <div class="filter-group">
                    <label for="sort">Sort By:</label>
                    <select name="sort" id="sort" class="filter-select">
                        <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name</option>
                        <option value="price" <?php echo $sort == 'price' ? 'selected' : ''; ?>>Price</option>
                        <option value="created_at" <?php echo $sort == 'created_at' ? 'selected' : ''; ?>>Newest</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label for="order">Order:</label>
                    <select name="order" id="order" class="filter-select">
                        <option value="asc" <?php echo $order == 'asc' ? 'selected' : ''; ?>>Ascending</option>
                        <option value="desc" <?php echo $order == 'desc' ? 'selected' : ''; ?>>Descending</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Apply Filters</button>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="product-grid">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="product-card">
                        <img src="<?php echo htmlspecialchars($row['image']); ?>" 
                             alt="<?php echo htmlspecialchars($row['name']); ?>" 
                             class="product-image">
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($row['name']); ?></h3>
                            <p class="product-price">$<?php echo number_format($row['price'], 2); ?></p>
                            <p class="product-sport">Sport: <?php echo htmlspecialchars($row['sport']); ?></p>
                            <p class="product-size">Size: <?php echo htmlspecialchars($row['size']); ?></p>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $row['id']; ?>" 
                                   class="btn btn-primary">View Details</a>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <button class="btn btn-secondary add-to-cart" 
                                            data-product-id="<?php echo $row['id']; ?>">
                                        Add to Cart
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?query=<?php echo urlencode($query); ?>&sport=<?php echo urlencode($sport); ?>&size=<?php echo urlencode($size); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&page=<?php echo $page - 1; ?>" class="btn btn-secondary">Previous</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?query=<?php echo urlencode($query); ?>&sport=<?php echo urlencode($sport); ?>&size=<?php echo urlencode($size); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&page=<?php echo $i; ?>" 
                           class="btn <?php echo $i == $page ? 'btn-primary' : 'btn-secondary'; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                        <a href="?query=<?php echo urlencode($query); ?>&sport=<?php echo urlencode($sport); ?>&size=<?php echo urlencode($size); ?>&min_price=<?php echo urlencode($min_price); ?>&max_price=<?php echo urlencode($max_price); ?>&sort=<?php echo urlencode($sort); ?>&order=<?php echo urlencode($order); ?>&page=<?php echo $page + 1; ?>" class="btn btn-secondary">Next</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <?php if (!empty($query)): ?>
                <div class="no-results">
                    <p>No results found for "<?php echo htmlspecialchars($query); ?>".</p>
                    <?php if (stripos($query, 'real madrid') !== false): ?>
                        <p class="out-of-stock-message">
                            We're sorry, but we currently don't have any Real Madrid items in stock. 
                            Please check back later as we're always updating our inventory.
                        </p>
                    <?php else: ?>
                        <p>Try adjusting your filters or search terms.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add to cart functionality
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.dataset.productId;
            
            // Send AJAX request to add to cart
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'product_id=' + encodeURIComponent(productId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                } else {
                    alert('Error adding product to cart: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding to cart');
            });
        });
    });
});
</script>

<?php include 'footer.php'; ?>
