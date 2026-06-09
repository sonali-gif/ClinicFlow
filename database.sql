CREATE DATABASE IF NOT EXISTS hospital_management;
USE hospital_management;

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL
);

-- Managers Table
CREATE TABLE IF NOT EXISTS managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL
);

-- Doctors Table
CREATE TABLE IF NOT EXISTS doctors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    specialization VARCHAR(100)
);

-- Test Managers Table
CREATE TABLE IF NOT EXISTS test_managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL
);

-- Pharmacists Table
CREATE TABLE IF NOT EXISTS pharmacists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL
);

-- Patients Table (with doctor assignment)
CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(100) NOT NULL,
    age INT,
    gender ENUM('Male', 'Female', 'Other'),
    symptoms TEXT,
    assigned_doctor_id INT,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    next_appointment DATE,
    prescription TEXT,
    medicine_items TEXT,
    tests TEXT,
    prescription_file VARCHAR(255),
    test_file VARCHAR(255),
    medicine_bill TEXT,
    test_bill TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assigned_doctor_id) REFERENCES doctors(id) ON DELETE SET NULL
);

-- Default data for testing
INSERT INTO admins (username, password, full_name) VALUES ('admin', 'admin123', 'System Administrator');
INSERT INTO managers (username, password, full_name) VALUES ('manager', 'manager123', 'Hospital Desk Manager');
INSERT INTO doctors (username, password, full_name, specialization) VALUES 
('doctor1', 'doctor123', 'Dr. Smith', 'General Physician'),
('doctor2', 'doctor123', 'Dr. Jones', 'Cardiologist');

INSERT INTO test_managers (username, password, full_name) VALUES ('testmgr', 'testmgr123', 'Test Department Manager');

INSERT INTO pharmacists (username, password, full_name) VALUES ('pharma', 'pharma123', 'Hospital Pharmacist');
