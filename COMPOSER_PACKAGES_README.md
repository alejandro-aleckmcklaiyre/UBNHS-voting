# Composer Packages & Installation Guide

This project uses Composer to manage PHP dependencies. Below is a list of packages installed via Composer and instructions for installation.

## Installed Composer Packages

- **phpoffice/phpspreadsheet**
  - Used for reading and writing Excel files (.xlsx, .xls)
- **Other dependencies**
  - Packages required by phpoffice/phpspreadsheet (e.g., ext-gd, ext-zip)

## Installation Instructions

1. **Install Composer**
   - Download Composer from https://getcomposer.org/download/
   - Follow the installation instructions for Windows.

2. **Enable Required PHP Extensions**
   - Open your `php.ini` file (usually in `C:\xampp\php\php.ini`).
   - Uncomment or add these lines:
     ```
     extension=gd
     extension=zip
     ```
   - Save the file and restart Apache.

3. **Install Packages**
   - Open a terminal in your project root (`c:\xampp\htdocs\UBNHS-voting`).
   - Run:
     ```powershell
     composer require phpoffice/phpspreadsheet
     ```
   - Composer will download all required files into the `vendor/` directory.

4. **Verify Installation**
   - Check that the `vendor/` folder exists and contains the packages.
   - Ensure there are no errors about missing extensions or PHP version.

## Troubleshooting
- If you see errors about missing extensions, make sure you enabled them in `php.ini` and restarted Apache.
- If your PHP version is too old, upgrade to PHP 7.2 or newer.

## Usage
- The autoloader is included via:
  ```php
  require_once(__DIR__ . '/vendor/autoload.php');
  ```
- You can now use PHPSpreadsheet and other Composer packages in your PHP scripts.

---
For more help, visit https://getcomposer.org/doc/ or https://phpspreadsheet.readthedocs.io/en/latest/
