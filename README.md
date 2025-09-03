# UBNH Voting

UBNH Voting is an online election management system designed to empower students, faculty, and staff to participate in secure, fair, and transparent elections. Whether it's for student council, classroom representatives, or school-wide polls, this system streamlines the entire election process and provides real-time results, reducing the need for manual counting.

## Features

- **Student Login via QR Code:** Each student receives a unique QR code for secure login, ensuring only authorized participants can vote.
- **Session and Token Validation:** Implements one-time use tokens for QR codes to prevent voting abuse and ensure fair participation.
- **Admin-Controlled Student Enrollment:** Administrators input student data to maintain accuracy and integrity.
- **Super Admin Utilities:** 
  - Export election results in CSV format, restricting access to sensitive data.
  - Only the super admin can add candidates, following a verification and interview process to ensure candidate legitimacy.

## Technologies Used

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (with plans to migrate to Laravel Framework)
- **Deployment:** Hostinger

## QR Code Generation & Email Delivery

Once the admin inputs the details of students into the voters table:
1. A unique QR code is generated for each student using the `endroid/qr-code` package.
2. The QR code is sent to the student's registered email address using [PHPMailer](https://github.com/PHPMailer/PHPMailer), ensuring that each student receives their voting credentials securely and privately.

This process automates the distribution of QR codes and ensures seamless, secure authentication for voters.

## How It Works

1. **Student Registration:** Admins register students and generate their unique QR codes.
2. **QR Code Distribution:** Each QR code is sent to the student’s email using PHPMailer.
3. **Voting:** Students scan their QR code to securely log in and cast their vote.
4. **Candidate Management:** Candidates are added only by the super admin after a thorough screening process.
5. **Real-Time Results:** Votes are displayed in Admin dashboard real-time.
5. **Results:** Super admin can export the election results in CSV format for official use.

## Deployment

The application will be deployed using [Hostinger](https://www.hostinger.com/).

## Future Improvements

- Migration to Laravel framework for enhanced security and scalability.
- Additional analytics and reporting features.
- Enhanced user experience for both voters and administrators.

## Contributors

- **Dyanna May Pineda** — Front End Developer / Assistant Project Manager
- **Aleck Mcklaiyre Alejandro** — Technical Lead / Backend Developer



---

*UBNH Voting — Making elections secure, simple, and transparent.*
