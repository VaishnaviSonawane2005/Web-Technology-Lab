CREATE DATABASE IF NOT EXISTS ebookshop_db;
USE ebookshop_db;

CREATE TABLE IF NOT EXISTS ebookshop_db (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    book_title VARCHAR(255) NOT NULL,
    book_author VARCHAR(255) NOT NULL,
    book_price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    CONSTRAINT chk_book_price CHECK (book_price >= 0),
    CONSTRAINT chk_book_quantity CHECK (quantity >= 0)
);

INSERT INTO ebookshop_db (book_title, book_author, book_price, quantity)
SELECT * FROM (
    SELECT 'The Great Gatsby' AS book_title, 'F. Scott Fitzgerald' AS book_author, 299.99 AS book_price, 15 AS quantity UNION ALL
    SELECT 'To Kill a Mockingbird', 'Harper Lee', 349.99, 12 UNION ALL
    SELECT '1984', 'George Orwell', 399.99, 8 UNION ALL
    SELECT 'Pride and Prejudice', 'Jane Austen', 279.99, 20 UNION ALL
    SELECT 'Clean Code', 'Robert C. Martin', 899.99, 7 UNION ALL
    SELECT 'Effective Java', 'Joshua Bloch', 1099.99, 4
) AS sample_data
WHERE NOT EXISTS (SELECT 1 FROM ebookshop_db WHERE book_title = sample_data.book_title);

SELECT book_id, book_title, book_author, book_price, quantity
FROM ebookshop_db
ORDER BY book_id;
