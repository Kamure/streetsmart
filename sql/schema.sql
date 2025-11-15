
CREATE TABLE users (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(150) UNIQUE,
  phone VARCHAR(20),
  password VARCHAR(255),
  role ENUM('admin', 'seller', 'customer') DEFAULT 'customer',
  avatar_path VARCHAR(255),
  bio TEXT,
  skills TEXT,
  email_verified_at TIMESTAMP NULL,
  remember_token VARCHAR(100) NULL,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);
ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0; 

CREATE TABLE shops (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  name VARCHAR(120),
  category VARCHAR(100),
  location VARCHAR(150),
  logo_path VARCHAR(255),
  is_verified BOOLEAN DEFAULT FALSE,
  latitude DECIMAL(10, 6),
  longitude DECIMAL(10, 6),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);


CREATE TABLE products (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  shop_id BIGINT,
  name VARCHAR(120),
  description TEXT,
  price DECIMAL(10,2),
  stock INT DEFAULT 0,
  image_path VARCHAR(255),
  category VARCHAR(100),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);

CREATE TABLE services (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    shop_id BIGINT NOT NULL,
    name VARCHAR(120),
    description TEXT,
    price DECIMAL(10,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (shop_id) REFERENCES shops(id)
);


CREATE TABLE orders (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_id BIGINT,
  shop_id BIGINT,
  total DECIMAL(10,2),
  status ENUM('pending','paid','cancelled','shipped') DEFAULT 'pending',
  payment_ref VARCHAR(100),
  payment_method ENUM('mpesa','cash') DEFAULT 'mpesa',
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);


CREATE TABLE order_items (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT,
  product_id BIGINT,
  quantity INT,
  price DECIMAL(10,2),
  subtotal DECIMAL(10,2),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);


CREATE TABLE payments (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT,
  transaction_id VARCHAR(100),
  amount DECIMAL(10,2),
  method VARCHAR(50),
  status ENUM('pending','success','failed'),
  phone_number VARCHAR(20),
  raw_response JSON,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);


CREATE TABLE reviews (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  seller_id BIGINT,
  customer_id BIGINT,
  rating TINYINT,
  comment TEXT,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);


CREATE TABLE otp_codes (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT,
  code VARCHAR(6),
  expires_at TIMESTAMP,
  used BOOLEAN DEFAULT FALSE,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
);


