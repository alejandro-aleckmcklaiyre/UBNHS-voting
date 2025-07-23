<?php
// Start session if needed in the future
// session_start();

$page = $_GET['page'] ?? 'home';

// List of allowed pages for security
$allowed_pages = [
    'home' => 'homepage/home_page.html',
    'admin_login' => 'admin_login/admin_login.html',
    'student_qr' => 'student_qr/student_QR_scanner.html',
    'voters_page' => 'voters_page/voters_page.html',      // voters_page.html
    'voting_page' => 'voting_page/voting_page.html',      // voting_page.html
    'admin_dashboard' => 'admin_dashboard/admin_dashboard.html',
    'candidates' => 'candidates/candidates_page.html',
    // Add more pages here as you build them
];

// Default to home if page is not allowed
$file = $allowed_pages[$page] ?? $allowed_pages['home'];

// Build full path
$full_path = __DIR__ . "/pages/" . $file;

// Check if file exists before including
if (file_exists($full_path)) {
    include $full_path;
} else {
    echo "<h1>404 - Page not found</h1>";
}
