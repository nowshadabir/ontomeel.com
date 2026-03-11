-- Database Table for Pre-Orders
-- Designed for Ontyamel Pre-Booking System

CREATE TABLE IF NOT EXISTS pre_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    release_date DATE,
    cover_image VARCHAR(255),
    status ENUM('Upcoming', 'Open', 'Closed') DEFAULT 'Upcoming',
    is_hot_deal BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sample Data for Pre-Orders (matching your current design)
INSERT INTO pre_orders (title, author, price, discount_price, release_date, cover_image, status, is_hot_deal) 
VALUES 
('অন্ধকারে আলো', 'হুমায়ূন আহমেদ মেমোরিয়াল সিরিজ', 550.00, 450.00, '2026-03-20', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=1000', 'Open', 0),
('নীল আকাশের নিচে', 'তামিম হাসান', 400.00, 320.00, '2026-03-25', 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1000', 'Open', 0),
('অফুরন্ত পথ চলা', 'মাশরুর রহমান', 650.00, 500.00, '2026-04-02', 'https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=1000', 'Upcoming', 0),
('পূর্ণতা কম্বো (১+২)', 'হুমায়ূন আহমেদ', 700.00, 600.00, '2026-03-30', 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=400', 'Open', 1);
