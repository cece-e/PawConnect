CREATE DATABASE IF NOT EXISTS pet_adoption_system;
USE pet_adoption_system;


CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS pets (
    pet_id INT AUTO_INCREMENT PRIMARY KEY,
    pet_name VARCHAR(100) NOT NULL,
    species ENUM('Dog','Cat','Bird','Fish','Rabbit') NOT NULL,
    breed VARCHAR(100) NOT NULL,
    age INT NOT NULL,
    weight DECIMAL(5,2) NULL COMMENT 'Weight in kg',
    gender ENUM('Male','Female') NOT NULL,
    color VARCHAR(50) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) NOT NULL,
    status ENUM('Available','Pending','Adopted') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_species (species),
    INDEX idx_status (status)
);


CREATE TABLE IF NOT EXISTS adoption_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    request_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Pending','Approved','Rejected') DEFAULT 'Pending',

    reason_for_adoption TEXT NOT NULL,

    id_picture_path VARCHAR(255) NULL,
    valid_id_path VARCHAR(255) NULL,
    home_photos_path TEXT NULL, -- can store JSON or comma-separated list of file paths

    admin_feedback TEXT NULL,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (pet_id) REFERENCES pets(pet_id)
        ON DELETE CASCADE
);

CREATE TABLE adoption_likert_responses (
    response_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,

    q1_right_reasons        TINYINT NOT NULL,
    q2_household_support    TINYINT NOT NULL,
    q3_financial_prep       TINYINT NOT NULL,
    q4_time_availability    TINYINT NOT NULL,
    q5_safe_environment     TINYINT NOT NULL,
    q6_commitment_change    TINYINT NOT NULL,
    q7_behavior_patience    TINYINT NOT NULL,
    q8_emergency_plan       TINYINT NOT NULL,
    q9_spay_neuter          TINYINT NOT NULL,
    q10_unknown_history     TINYINT NOT NULL,
    q11_lifelong_care       TINYINT NOT NULL,
    q12_policy_compliance   TINYINT NOT NULL,

    CHECK (q1_right_reasons BETWEEN 1 AND 5),
    CHECK (q2_household_support BETWEEN 1 AND 5),
    CHECK (q3_financial_prep BETWEEN 1 AND 5),
    CHECK (q4_time_availability BETWEEN 1 AND 5),
    CHECK (q5_safe_environment BETWEEN 1 AND 5),
    CHECK (q6_commitment_change BETWEEN 1 AND 5),
    CHECK (q7_behavior_patience BETWEEN 1 AND 5),
    CHECK (q8_emergency_plan BETWEEN 1 AND 5),
    CHECK (q9_spay_neuter BETWEEN 1 AND 5),
    CHECK (q10_unknown_history BETWEEN 1 AND 5),
    CHECK (q11_lifelong_care BETWEEN 1 AND 5),
    CHECK (q12_policy_compliance BETWEEN 1 AND 5),

    FOREIGN KEY (request_id) REFERENCES adoption_requests(request_id)
        ON DELETE CASCADE
);

CREATE TABLE adoption_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    adoption_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,

    FOREIGN KEY (pet_id) REFERENCES pets(pet_id)
        ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS adoption_documents (
    document_id     INT AUTO_INCREMENT PRIMARY KEY,
    request_id      INT NOT NULL,
    user_id         INT NOT NULL,
    id_picture      VARCHAR(255) NOT NULL,   -- 1x1 ID picture file
    id_copy         VARCHAR(255) NOT NULL,   -- photocopy of valid ID
    uploaded_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_docs_request
        FOREIGN KEY (request_id) REFERENCES adoption_requests(request_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_docs_user
        FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE,
    UNIQUE KEY unique_request (request_id)   -- one document set per request
);

CREATE TABLE IF NOT EXISTS adoption_home_photos (
    photo_id        INT AUTO_INCREMENT PRIMARY KEY,
    document_id     INT NOT NULL,
    photo_path      VARCHAR(255) NOT NULL,
    photo_label     VARCHAR(50) DEFAULT NULL,  -- e.g. 'inside', 'outside', 'pet area'
    uploaded_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_homephoto_document
        FOREIGN KEY (document_id) REFERENCES adoption_documents(document_id)
        ON DELETE CASCADE
);

INSERT INTO users
(fullname,email,phone,address,username,password,role)
VALUES
(
'System Administrator',
'admin@pets.com',
'09123456789',
'Pet Adoption Center',
'admin',
'$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36mS2M8Q6zGyF/F7kh/3G2K',
'admin'
);


INSERT INTO pets
(pet_name, species, breed, age, weight, gender, color, description, image, status)
VALUES
('Buddy','Dog','Golden Retriever',2,28.50,'Male','Golden',
'Friendly and playful dog looking for a loving home.',
'dog1.jpg','Available'),

('Daisy','Dog','Shih Tzu',1,6.20,'Female','Brown',
'Small and affectionate companion.',
'dog2.jpg','Available'),

('Max','Dog','Labrador',3,31.80,'Male','Black',
'Very energetic and loves children.',
'dog3.jpg','Available'),

('Luna','Cat','Persian',2,4.10,'Female','White',
'Calm indoor cat.',
'Persian.jpg','Available'),

('Milo','Cat','Siamese',1,3.60,'Male','Cream',
'Very playful and curious.',
'Siamese.jpg','Available'),

('Rocky','Dog','Bulldog',3,24.50,'Male','Brindle',
'Calm and affectionate, enjoys naps and short strolls.',
'dog4.jpg','Available'),

('Willow','Cat','Siamese',2,3.90,'Female','Cream/Brown',
'Vocal and affectionate, enjoys sitting on laps.',
'cat3.jpg','Available'),

('Kiwi','Bird','Budgerigar',1,0.04,'Male','Green/Yellow',
'Chatty and cheerful, whistles at anyone who walks by.',
'bird1.jpg','Available'),

('Sunny','Bird','Cockatiel',2,0.09,'Female','Grey/Yellow',
'Gentle and tame, enjoys sitting on your shoulder.',
'bird2.jpg','Available'),

('Nemo','Fish','Clownfish',1,0.25,'Male','Orange/White',
'Bright and active, best kept in a saltwater tank with an anemone.',
'nemo.jpg','Available'),

('Bubbles','Fish','Betta',1,0.05,'Female','Blue',
'Low-maintenance and colorful, prefers to live alone.',
'fish2.jpg','Available'),

('Thumper','Rabbit','Holland Lop',1,1.70,'Male','Brown/White',
'Floppy-eared and sweet, loves fresh veggies.',
'rabbit1.jpg','Available'),

('Snowball','Rabbit','Netherland Dwarf',2,1.20,'Female','White',
'Small and gentle, litter-trained and easy to handle.',
'rabbit2.jpg','Available');