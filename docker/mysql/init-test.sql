-- Create the test database
CREATE DATABASE IF NOT EXISTS cinemacloud_test;

-- Grant all privileges to the cinemacloud user from any host
GRANT ALL PRIVILEGES ON cinemacloud_test.* TO 'cinemacloud'@'%' IDENTIFIED BY 'secret';
FLUSH PRIVILEGES;

