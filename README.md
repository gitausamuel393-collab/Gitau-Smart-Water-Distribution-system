# Meru Smart Water Distribution System v2.0

## Project Overview
The Meru Smart Water Distribution System is designed to improve water usage monitoring and billing through IoT-enabled valves 
and a web-based dashboard. It integrates real-time usage tracking, mobile payments (M-Pesa), and administrative controls.

## Features
User registration and authentication
Real-time water usage tracking (IoT integration)
Valve control system (open/close remotely)
Wallet system with M-Pesa integration
Usage and payment history tracking
Admin dashboard for system management
Data export (PDF and CSV reports)
Alerts and notifications system
## Technologies Used
PHP (Backend)
MySQL (Database)
HTML, CSS, JavaScript (Frontend)
XAMPP (Local server)
Composer (Dependency management)
M-Pesa API (Payments)
Arduino/LoRa (IoT communication)
## Configuration

Before running the system:
Update database credentials in db.php
Configure M-Pesa credentials in mpesa_config.php
Ensure Apache and MySQL are running in XAMPP

## Description
This is a web-based water management system developed using PHP and MySQL. It allows users to monitor water usage, manage 
payments, and track consumption.

## Setup

1. Import `meru_schema.sql` into MySQL via phpMyAdmin
2. Copy all files to `C:\xampp\htdocs\meru\`
3. Run `composer install` in the root folder
4. Visit `http://localhost/meru/`

## Usage
1. Register a new user account
2. Log in to the system
3. Add a water valve device
4. Monitor usage from the dashboard
5. Top up wallet using M-Pesa
6. Generate reports or export data

## File Structure
```
meru/
├── login.php              # Login page
├── register.php           # Registration page
├── db.php                 # Database connection
├── mpesa_config.php       # M-Pesa credentials
├── meru_schema.sql        # Database schema (import this first)
├── assets/styles.css      # Dark mode UI styles
├── components/
│   ├── header.php         # Sidebar + topbar
│   └── footer.php         # Footer + closing tags
├── pages/
│   ├── dashboard.php      # Main dashboard
│   ├── add_valve.php      # Add IoT valve
│   ├── update_valve.php   # AJAX valve control
│   ├── get_usage.php      # AJAX usage data
│   ├── topup.php          # Wallet top-up
│   ├── payment_history.php
│   ├── usage_history.php
│   ├── report.php         # Reports + charts
│   ├── alerts.php
│   ├── profile.php
│   ├── export_pdf.php
│   ├── export_csv.php
│   └── mpesa_callback.php
├── admin/
│   ├── dashboard.php      # Admin overview
│   ├── toggle_user.php
│   ├── export_pdf.php
│   └── export_csv.php
└── api/
    ├── save_usage.php     # IoT: POST water readings
    ├── get_command.php    # IoT: GET valve command
    └── get_valve_status.php # IoT: GET valve states

## IoT API Endpoints (Arduino/LoRa)

Save usage:
GET /api/save_usage.php?key=meru_iot_2024&user_id=1&device_id=1&flow_rate=3.5&duration=60

Get valve command:
GET /api/get_command.php?device_id=1

Get valve status:
GET /api/get_valve_status.php?key=meru_iot_2024&user_id=1
```
