CREATE TABLE IF NOT EXISTS students (
    id INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    nim VARCHAR(15) UNIQUE NOT NULL, 
    prodi VARCHAR(50) NOT NULL, 
    angkatan INT(4) NOT NULL, 
    foto_path VARCHAR(255) NULL, 
    status ENUM('active', 'inactive') DEFAULT 'active'
);