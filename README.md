QUICK START
===========

```git clone https://github.com/nishealsohuwe/todo_list.git```

```cd todo_list```

```cp .env.example .env```

```composer install --no-dev```

DB INIT
-----------

```
CREATE DATABASE todo_list;
USE todo_list;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('в работе', 'завершено', 'дедлайн') DEFAULT 'в работе',
    deadline DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

RUNSERVER
-----------
```
php -S localhost:8000 -t public
```
  

Description
===========

### TodoList API is a RESTful web application for task management with JWT authentication. 
### Users can create, edit, delete, and view tasks with pagination, and filter them by status ("in progress", "completed", "deadline"). 
### The API ensures security through data validation, SQL injection protection, and task access restrictions.

