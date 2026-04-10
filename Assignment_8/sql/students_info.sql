CREATE DATABASE IF NOT EXISTS college_portal;
USE college_portal;

DROP TABLE IF EXISTS students_info;

CREATE TABLE students_info (
    stud_id INT PRIMARY KEY,
    stud_name VARCHAR(100) NOT NULL,
    `class` VARCHAR(20) NOT NULL,
    division VARCHAR(10) NOT NULL,
    city VARCHAR(100) NOT NULL
);

INSERT INTO students_info (stud_id, stud_name, `class`, division, city) VALUES
    (101, 'Aarav Sharma', 'TY', 'A', 'Pune'),
    (102, 'Diya Patil', 'TY', 'B', 'Mumbai'),
    (103, 'Rohan Kulkarni', 'SY', 'A', 'Nashik'),
    (104, 'Sneha Joshi', 'FY', 'C', 'Nagpur'),
    (105, 'Aditya Deshmukh', 'SY', 'B', 'Aurangabad');
