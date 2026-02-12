-- Create products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sport VARCHAR(50) NOT NULL,
    size VARCHAR(5) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NOT NULL,
    description TEXT,
    stock INT NOT NULL DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert sample products
INSERT INTO products (name, sport, size, price, image, description, stock) VALUES
('Real Madrid Away Jersey', 'Soccer', 'L', 249.99, 'images/RM KITjpg.jpg', 'Official Real Madrid away jersey in excellent condition', 10),
('Manchester United Home Jersey', 'Soccer', 'M', 279.99, 'images/ManUjpg.jpg', 'Classic Manchester United home jersey - great condition', 8),
('Tottenham Hotspur Home Jersey', 'Soccer', 'S', 249.99, 'images/Spurs.jpg', 'Tottenham Hotspur home jersey - lightly used', 12),
('Liverpool Home Jersey', 'Soccer', 'XL', 249.99, 'images/LFCjpg.jpg', 'Liverpool FC home jersey - good condition', 5),
('Rodrygo Real Madrid Jersey', 'Soccer', 'M', 199.99, 'images/Rodrygo.jpg', 'Rodrygo Real Madrid jersey - excellent condition', 7),
('Son Heung-min Spurs Jersey', 'Soccer', 'L', 219.99, 'images/son2.jpg', 'Son Heung-min Tottenham jersey - lightly used', 9),
('Foden Manchester City Jersey', 'Soccer', 'M', 239.99, 'images/foden.jpg', 'Phil Foden Manchester City jersey - excellent condition', 6);

-- Create shopping cart table
CREATE TABLE IF NOT EXISTS shopping_cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Insert some sample cart items
INSERT INTO shopping_cart (user_id, product_id, quantity) VALUES
(1, 1, 1),  -- User 1 added Real Madrid jersey
(1, 2, 2),  -- User 1 added Manchester United jersey twice
(2, 3, 1);  -- User 2 added Tottenham jersey
