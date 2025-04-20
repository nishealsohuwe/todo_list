<?php
return [
    'driver'   => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host'     => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'todo_list',  
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset'  => 'utf8mb4'
];