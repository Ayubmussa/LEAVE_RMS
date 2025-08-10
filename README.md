# Project Navigation System

A web interface with login functionality and project navigation buttons, built with HTML/CSS frontend, Node.js backend, and PHP for database operations.

## Project Structure

```
project-root/
├── public/                  # Frontend files
│   ├── css/                 # CSS stylesheets
│   │   └── styles.css       # Main stylesheet
│   ├── js/                  # JavaScript files
|   │   ├── main.js          # Main page functionality
|   |   ├── tour.js          # Tour about the main page contents
│   │   ├── language.js      # Language functionaly
│   │   └── login.js         # Login page functionality
│   ├── index.html           # Main page with project navigation
│   └── login.html           # Login page
├── database/                # PHP database integration
│   ├── db_connection.php    # Database connection and setup
│   └── api.php              # PHP API endpoints
└── package.json             # Node.js project configuration
```

## Features

- User authentication (login/logout)
- Project navigation with two buttons to access other projects
- Responsive design with HTML/CSS
- Node.js backend with Express.js
- PHP database integration
- Dual authentication system (Node.js and PHP)

## Prerequisites

- Node.js (v14 or higher)
- PHP (v7.4 or higher)
- MySQL database

## Setup Instructions

### 1. PHP backend Initialization



### 2. Configure Database

1. Make sure MySQL is running
2. Update database configuration in `database/db_connection.php` if needed:
   ```php
   $host = "localhost";
   $username = "root";
   $password = "";
   $database = "leave_rms_db";
   ```

### 3. Start the Node.js Server

```bash
npm start
```

### 4. Configure PHP and Database


### 5. Access the Application

Open your browser and navigate to:
```
http://localhost
```

## Login Credentials



## How It Works

1. The application starts with a login page
2. After successful authentication, users are redirected to the main page
3. The main page displays platform options to choose from
4. Clicking on a project button will navigate to the respective project

## Technical Implementation

### Frontend
- HTML5 for structure
- CSS3 for styling (responsive design)
- JavaScript for client-side functionality
- Fetch API for communication with backend

### Backend
- RESTful API endpoints for authentication

### Database
- PHP scripts for database operations
- MySQL database for storing user and platform
- PHP API endpoints for data access

