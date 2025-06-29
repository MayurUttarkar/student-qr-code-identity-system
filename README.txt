📘 Student QR Code Identity System - Setup Instructions

1. Extract this folder to C:/xampp/htdocs/

2. Download and extract phpqrcode library from:
   https://sourceforge.net/projects/phpqrcode/
   Place it inside: student_qr_system/phpqrcode/

3. Create MySQL Database: qr_system

4. Import database qr_system.sql (available SQL File Folder inside zip package)

5. Create 2 Folders inside - C:\xampp\htdocs\student_qr_system
   -> qrcodes (where generated qr codes are stored)
   -> uploads (where students photos are stored) 
      NOTE - bulk upload option is not avaiable for uploads.

6. Start Apache and MySQL from XAMPP.

7. Access the app:
   - Admin: http://localhost/student_qr_system/admin.php
   - Main page (QR): http://localhost/student_qr_system/index.php

8. **************For Admin Login****************************
Open Your browser put inside browser “http://localhost/student_qr_system/admin.php”
Login Details for admin :
Username : admin
Password: 1234

9. **************For Faculty Login***********************************
Open Your browser put inside browser “http://localhost/student_qr_system/index.php”
Login Details for admin :
Username: faculty
Password: 1234
