CREATE DATABASE sistema_vendas;

USE sistema_vendas;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    size VARCHAR(50),
    color VARCHAR(50),
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    image VARCHAR(255)
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_date DATETIME NOT NULL,
    total DECIMAL(10, 2) NOT NULL
);

CREATE TABLE sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    method VARCHAR(100) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE
);

-- Inserir alguns produtos de exemplo para começar
INSERT INTO `products` (`id`, `name`, `size`, `color`, `price`, `stock`, `image`) VALUES
(101, 'Camiseta Básica', 'P', 'Branca', 49.90, 15, 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta'),
(102, 'Camiseta Básica', 'M', 'Branca', 49.90, 20, 'https://placehold.co/300x300/FFFFFF/333333?text=Camiseta'),
(201, 'Camiseta Básica', 'P', 'Preta', 49.90, 18, 'https://placehold.co/300x300/333333/FFFFFF?text=Camiseta'),
(301, 'Calça Jeans Skinny', '38', 'Azul Claro', 129.90, 8, 'https://placehold.co/300x300/a0d2eb/333333?text=Calça');