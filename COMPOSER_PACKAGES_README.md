# Composer Packages & Installation Guide

This project uses Composer to manage PHP dependencies. Below is a list of packages installed via Composer and step-by-step installation instructions.

## Installed Composer Packages

- **endroid/qr-code**
  - Used for generating QR codes with student details
  - Supports PNG output with customizable size and error correction
- **Other dependencies**
  - Packages automatically installed with endroid/qr-code (e.g., ext-gd for image processing)

## Prerequisites

Before installing packages, ensure you have:
- **PHP 7.4 or newer** (check with `php -v`)
- **XAMPP or similar web server** with PHP enabled
- **Internet connection** for downloading packages

## Step-by-Step Installation Instructions

### Step 1: Install Composer
1. Download Composer from https://getcomposer.org/download/
2. **For Windows:**
   - Download and run `Composer-Setup.exe`
   - Follow the installation wizard
   - Make sure it adds Composer to your system PATH
3. **Verify installation:**
   - Open Command Prompt or PowerShell
   - Run: `composer --version`
   - You should see version information

### Step 2: Enable Required PHP Extensions
1. **Locate your php.ini file:**
   - For XAMPP: `C:\xampp\php\php.ini`
   - For other installations: Check your PHP info or ask your server admin
2. **Edit php.ini file:**
   - Open `php.ini` in a text editor (run as Administrator if needed)
   - Find and uncomment these lines (remove the `;` at the beginning):
     ```ini
     extension=gd
     extension=zip
     extension=mbstring
     extension=xml
     extension=fileinfo
     ```
   - If these lines don't exist, add them to the file
3. **Save the file and restart Apache/web server**

### Step 3: Navigate to Your Project Directory
1. Open Command Prompt or PowerShell **as Administrator**
2. Navigate to your project root:
   ```powershell
   cd C:\xampp\htdocs\UBNHS-voting
   ```

### Step 4: Install QR Code Package
1. **Install endroid/qr-code package:**
   ```powershell
   composer require endroid/qr-code
   ```
2. **Wait for installation to complete** - this may take a few minutes
3. **You should see output like:**
   ```
   Using version ^4.8 for endroid/qr-code
   ./composer.json has been created
   ./composer.lock has been created
   Loading composer repositories with package information
   Updating dependencies (including require-dev)
   Package operations: X installs, 0 updates, 0 removals
   Writing lock file
   Generating autoload files
   ```

### Step 5: Verify Installation
1. **Check that these directories exist:**
   - `C:\xampp\htdocs\UBNHS-voting\vendor\`
   - `C:\xampp\htdocs\UBNHS-voting\vendor\endroid\qr-code\`
2. **Check that these files exist:**
   - `composer.json`
   - `composer.lock`
   - `vendor\autoload.php`
3. **Test the installation:**
   - Create a test PHP file with:
     ```php
     <?php
     require_once(__DIR__ . '/vendor/autoload.php');
     
     if (class_exists('Endroid\QrCode\QrCode')) {
         echo "QR Code library installed successfully!";
     } else {
         echo "QR Code library NOT found!";
     }
     ?>
     ```
   - Run this file in your browser

## Usage in Your PHP Scripts

Include the autoloader at the top of your PHP files:
```php
<?php
require_once(__DIR__ . '/vendor/autoload.php');

// Now you can use QR code classes
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

// Your QR code generation code here...
?>
```

## Troubleshooting

### Common Issues and Solutions

**1. "composer: command not found" or "'composer' is not recognized"**
- Solution: Reinstall Composer and make sure it's added to your system PATH
- Alternative: Use the full path to composer.phar

**2. "Your requirements could not be resolved to an installable set of packages"**
- Solution: Update your PHP version to 7.4 or newer
- Check: Run `php -v` to see your current version

**3. "ext-gd is missing from your system"**
- Solution: Enable the GD extension in php.ini (see Step 2)
- Restart your web server after making changes

**4. Permission denied errors on Windows**
- Solution: Run Command Prompt as Administrator
- Make sure your project directory is not read-only

**5. QR codes not generating**
- Check: Ensure `vendor/autoload.php` exists
- Check: Verify GD extension is enabled with `php -m | find "gd"`
- Check: Make sure your web directory has write permissions

**6. "Class 'Endroid\QrCode\QrCode' not found"**
- Solution: Make sure you included the autoloader: `require_once 'vendor/autoload.php';`
- Check: Verify the package was installed correctly in the vendor directory

### Getting More Help

**If you encounter other issues:**
1. Check the error logs in your XAMPP control panel
2. Visit the official documentation:
   - Composer: https://getcomposer.org/doc/
   - Endroid QR Code: https://github.com/endroid/qr-code
3. Check PHP error logs for detailed error messages

## Important Notes

- **Never commit the `vendor/` directory to version control** - it's generated automatically
- **Always commit `composer.json` and `composer.lock`** - these track your dependencies
- **Run `composer install`** (not `composer require`) when setting up on a new machine if `composer.json` already exists
- **The QR code generation has fallbacks** - if the Endroid library fails, it will use Google Charts API as backup

## Project Structure After Installation

```
UBNHS-voting/
├── vendor/                 (auto-generated, don't edit)
│   ├── endroid/
│   ├── autoload.php
│   └── ...
├── composer.json          (tracks your dependencies)
├── composer.lock          (locks specific versions)
└── your-php-files.php
```

---

**For additional support, consult:**
- Composer Documentation: https://getcomposer.org/doc/
- Endroid QR Code Documentation: https://github.com/endroid/qr-code
- PHP GD Extension Documentation: https://www.php.net/manual/en/book.image.php