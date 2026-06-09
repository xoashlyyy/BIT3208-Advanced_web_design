INVENTORY MANAGEMENT REPORT
https://docs.google.com/document/d/17EE30lvmD0waLpaprqtnwxPGBpSY3ipR/edit?usp=drive_link&ouid=112568412333547557219&rtpof=true&sd=true

ADVANCED WEB: StockTrack Inventory Management System
Name: Ashley Joy Onyango
Institution: Mount Kenya University
Course Code: BIT3208 
Instructor: Lec. Nyoro Michael 

Project Overview

StockTrack is a dynamic, data driven inventory management platform designed to streamline tracking and warehouse operations. The system allows authorized users to securely add, view, update, and delete inventory records through a session managed dashboard. 

Technology Stack

Frontend Interface: HTML, CSS, JavaScript 
Backend Server: PHP 
Database Management: MySQL 
Development Environment: XAMPP (Apache/MySQL)
UI/UX Prototyping: Figma, Canva 
Version Control: Git & GitHub 

Weekly Development Logbook (Weeks 1-5)

Week 1: Local Environment Setup:
Configured the local server environment using XAMPP to run Apache and MySQL services successfully.
Resolved port blocking issues to ensure smooth server execution.
Established a secure backend database connection using PHP, implementing the mysqli_report() function for graceful error handling.

Week 2: Wireframes and GUI Design:
Applied UI/UX principles to map out the navigation structure and user flow, aiming to minimize clicks for inventory tasks.
Designed low-fidelity wireframes and progressed to a high-fidelity Figma prototype using a modern slate and indigo color palette.
Adapted the primary dashboard layout into a compact, card-based design to support mobile viewports.

Week 3: JavaScript and PHP Basics:
Engineered a dynamic login form integrating frontend validation and backend foundations.
Implemented client-side JavaScript to prevent form submission if password inputs are under 8 characters, updating the DOM with real-time error alerts.
Captured input events dynamically to update UI elements (like the username display) in real-time.

Week 4: Server-Side Components and Backend Development:
Transitioned the platform from static pages to a dynamic application using server-side PHP processing.
Secured the authentication system by handling HTTP POST requests for user registration and credential validation.
Initiated secure, session-managed dashboards upon successful login.
Isolated backend logic cleanly into an includes directory to maintain professional directory structuring.

Week 5: Database Components and CRUD Operations:
Integrated persistent data storage by binding the PHP backend to a newly structured MySQL database.
Successfully implemented Create, Read, Update, and Delete (CRUD) operations for comprehensive inventory control.
Rendered live data fetches directly onto the frontend GUI dashboard.
Mitigated injection vulnerabilities during data insertions by strictly enforcing parameterized prepared statements.

