# Database Schema
 
## Tables Structure
 
### Users Table
```sql
CREATE TABLE Users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(50) UNIQUE,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```
**Description**: Stores user account information.  
**Relationships**:  
- One-to-many with `Products` (users can list multiple products)  
- One-to-many with `Sessions` (users can have multiple sessions)
 
---
 
### Sessions Table
```sql
CREATE TABLE Sessions (
    session_id VARCHAR(255) PRIMARY KEY,
    user_id INT NOT NULL,
    expires_at INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);
```
**Description**: Manages user authentication sessions.  
**Relationships**:  
- Many-to-one with `Users` (each session belongs to one user)
 
---
 
### Products Table
```sql
CREATE TABLE Products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image_path VARCHAR(255),
    stock INT DEFAULT 1,
    product_condition VARCHAR(25) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);
```
**Description**: Contains product listings.  
**Relationships**:  
- Many-to-one with `Users` (products belong to the user who listed them)

---

### Orders Table
```sql
CREATE TABLE Orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    quantity INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(255),
    box_id INT,
    pickup_code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES Users(user_id),
    FOREIGN KEY (seller_id) REFERENCES Users(user_id),
    FOREIGN KEY (box_id) REFERENCES SecureBoxes(box_id)
);
```

**Description**: Tracks all transactions between buyers and sellers including pickup details.  
**Relationships**:  
- Many-to-one with `Users` as buyer
- Many-to-one with `Users` as seller
- One-to-one with `SecureBoxes` (each order may use one secure box)

---

### SecureBoxes Table
```sql
CREATE TABLE SecureBoxes (
    box_id INT AUTO_INCREMENT PRIMARY KEY,
    description VARCHAR(255),
    status VARCHAR(255),
    order_id INT,
    FOREIGN KEY (order_id) REFERENCES Orders(order_id)
);
```

**Description**:  Manages physical secure boxes used for product exchanges.  
**Relationships**:  
- One-to-one with `Orders` (each box is assigned to one order)

---

### Notifications Table

```sql
CREATE TABLE Notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
);
```

**Description**:  Stores system notifications for users. 
**Relationships**:  
- Many-to-one with `Users` (each notification belongs to one user)

### Circular Dependency Note
The schema contains a circular reference between Orders and SecureBoxes. This is resolved by:

- First creating Orders without the box_id foreign key
- Then creating SecureBoxes with its order_id foreign key
- Finally adding the box_id foreign key to Orders using ALTER TABLE

```sql
ALTER TABLE Orders
ADD CONSTRAINT fk_orders_box
FOREIGN KEY (box_id) REFERENCES SecureBoxes(box_id);
```
