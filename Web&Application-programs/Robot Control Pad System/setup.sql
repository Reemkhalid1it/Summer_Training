–- Run this code once in the SQL tab in phpMyAdmin

CREATE TABLE robot_state (
    id INT PRIMARY KEY,
    command CHAR(1) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

–- Insert the only row that will be updated continuously (id = 1)
INSERT INTO robot_state (id, command) VALUES (1, 'S');
