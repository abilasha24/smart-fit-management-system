# 🏋️ Smart Fit Management System

A Role-Based Smart Fitness Management System developed using **PHP, MySQL, HTML, CSS, Bootstrap, and JavaScript**.

This system allows Admins, Trainers, and Members to manage fitness activities through a structured dashboard-based web application.

---

## 📌 Project Overview

Smart Fit is a web-based fitness management platform that includes:

- Role-based authentication system
- Admin, Trainer, and Member dashboards
- Workout & Meal Plan management
- Schedule management
- Member progress tracking
- Automated notification system
- Secure session handling

---

## 👥 User Roles

### 👨‍💼 Admin
- Manage users (members & trainers)
- Create workout plans
- Manage subscription plans
- View system statistics

### 🏋️ Trainer
- Assign workouts to members
- Create schedules
- Monitor member progress
- Receive notifications when schedules are completed

### 🧍 Member
- View assigned workouts
- Track progress
- Add personal reminders
- Mark schedules as completed
- Receive notifications from trainer

---

## 🔄 Workflow Automation (Core Feature)

When a member marks a schedule as **Completed**:

1. Database status updates (`status = done`)
2. Completion timestamp is saved
3. A notification is automatically created for the trainer
4. Trainer dashboard shows unread badge

This demonstrates backend-triggered workflow automation.

---

## 🗄 Database Structure (Main Tables)

- users
- workouts
- schedules
- member_workouts
- notifications
- plans
- payments

---

## 🔐 Security Features

- Role-based access control
- Session validation
- Protected backend APIs
- SQL prepared statements
- Unauthorized access prevention

---

## 🛠 Technologies Used

- PHP (Backend)
- MySQL (Database)
- HTML5
- CSS3 / Bootstrap
- JavaScript
- XAMPP (Local development)

---

## 📊 System Architecture

Frontend (HTML / CSS / JS)  
⬇  
PHP Backend APIs  
⬇  
MySQL Database  
⬇  
Notification & Schedule Workflow System  

---

## 🚀 How To Run Locally

1. Install XAMPP
2. Import database in phpMyAdmin
3. Place project inside `htdocs`
4. Start Apache & MySQL
5. Open `localhost/SMART_FIT`

---

## 🎓 Academic Purpose

This project demonstrates:

- Web application development
- Database design
- Role-based system architecture
- Workflow automation
- Real-world backend logic

---

## 👩‍💻 Author

**Abilasha Selvanayakam**  
Junior Web Developer  
