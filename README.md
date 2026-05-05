# Meru Smart Water Distribution System v2.0

## Setup

1. Import `meru_schema.sql` into MySQL via phpMyAdmin
2. Copy all files to `C:\xampp\htdocs\meru\`
3. Run `composer install` in the root folder
4. Visit `http://localhost/meru/`

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
