# 📝 Workly — Task Management System  
**Simple • Clean • Minimal**

Workly is a lightweight and user-friendly task management system designed to help you organize your day-to-day activities with ease. You can add, update, and delete tasks efficiently, ensuring your workflow stays neat and productive.

---

## 📌 Features
- ➕ Add new tasks easily  
- ✏️ Update your tasks anytime  
- ❌ Delete tasks instantly  
- 📂 Create unlimited tasks  
- 🎨 Clean and minimal UI  
- 📥 Import / 📤 Export tasks  
- 🔐 Secure Login & Register system  

---

## 🛠️ Tech Stack
- **HTML**  
- **Tailwind CSS**  
- **JavaScript**  
- **PHP**  
- **phpMyAdmin (MySQL Database)**  

---

## ⚙️ Installation & Setup

Follow these steps to run **Workly — Task Management System** on your local machine.

### 1. Prerequisites
- Install **XAMPP** (or any local PHP server)  
- Ensure **MySQL** is running (default port for XAMPP is 3306, but your setup uses **3307**)  
- Any PHP version will work.

### 2. Project Setup
1. Download or clone this repository.
2. Copy the project folder `Task Management System/` into the `htdocs` folder of XAMPP.
	Folder location : C:\xampp\htdocs\Task Management System
3. Start **Apache** and **MySQL** from the XAMPP Control Panel.

### 3. Database Setup
1. Open **phpMyAdmin**: `http://localhost:3307/phpmyadmin/` (replace `3307` with your MySQL port if different)
2. Import the `schema.sql` file located in the project root:
- Click **Import → Choose File → Select schema.sql → Go**
3. This will create the `task_system` database along with all necessary tables.
4. If you want to test a demo account, the SQL file may already include one. Otherwise, you can register manually via `register.html`.

> 💡 **Note:** The database will be local to your machine. Any tasks or accounts you see are independent of others’ machines.

### 4. Run the Project
1. Open your browser and go to: 
	http://localhost/Task%20Management%20System/public/register.html
2. Log in using the demo account (if available) or register a new account.
	Demo account : email: demo@gmail.com
		       password : 123123
3. Start adding, updating, and deleting tasks!

---

## 📁 Folder Structure
	Task Management System/
├── api/
│ ├── auth.php
│ ├── calendar.php
│ └── tasks.php
├── public/
│ ├── assets/
│ │ └── logo.png
│ ├── js/
│ │ ├── index.js
│ │ ├── login.js
│ │ └── register.js
│ ├── index.html
│ ├── login.html
│ └── register.html
├── db.php
└── schema.sql

