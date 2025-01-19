# Setup Test Environment for PiperPrivacy Plugin
# This script sets up a local test environment using WP-CLI

# Configuration
$WP_VERSION = "latest"
$DB_NAME = "piper_privacy_test"
$DB_USER = "root"
$DB_PASS = "root"
$DB_HOST = "localhost"
$WP_TITLE = "PiperPrivacy Test Site"
$WP_ADMIN_USER = "admin"
$WP_ADMIN_PASS = "admin"
$WP_ADMIN_EMAIL = "admin@example.com"
$TEST_SITE_DIR = "C:\xampp\htdocs\piper-privacy-test"

# Create test directory
Write-Host "Creating test directory..." -ForegroundColor Green
New-Item -ItemType Directory -Force -Path $TEST_SITE_DIR

# Download WordPress
Write-Host "Downloading WordPress..." -ForegroundColor Green
Set-Location $TEST_SITE_DIR
wp core download --version=$WP_VERSION

# Create wp-config.php
Write-Host "Creating wp-config.php..." -ForegroundColor Green
wp config create --dbname=$DB_NAME --dbuser=$DB_USER --dbpass=$DB_PASS --dbhost=$DB_HOST

# Create database
Write-Host "Creating database..." -ForegroundColor Green
wp db create

# Install WordPress
Write-Host "Installing WordPress..." -ForegroundColor Green
wp core install --url="http://localhost/piper-privacy-test" --title=$WP_TITLE --admin_user=$WP_ADMIN_USER --admin_password=$WP_ADMIN_PASS --admin_email=$WP_ADMIN_EMAIL

# Install and activate required plugins
Write-Host "Installing required plugins..." -ForegroundColor Green
wp plugin install meta-box --activate
wp plugin install meta-box-conditional-logic --activate
wp plugin install meta-box-group --activate
wp plugin install meta-box-columns --activate

# Create symbolic link for our plugin
Write-Host "Creating symbolic link for PiperPrivacy plugin..." -ForegroundColor Green
New-Item -ItemType SymbolicLink -Path "$TEST_SITE_DIR\wp-content\plugins\piper-privacy" -Target "C:\Users\trevo\Dropbox\CascadeProjects\PiperPrivacy\piper-privacy"

# Activate our plugin
Write-Host "Activating PiperPrivacy plugin..." -ForegroundColor Green
wp plugin activate piper-privacy

# Install PHPUnit
Write-Host "Installing PHPUnit..." -ForegroundColor Green
composer require --dev phpunit/phpunit

# Install WordPress test suite
Write-Host "Installing WordPress test suite..." -ForegroundColor Green
wp scaffold plugin-tests piper-privacy

# Configure test environment
Write-Host "Configuring test environment..." -ForegroundColor Green
Set-Location "$TEST_SITE_DIR\wp-content\plugins\piper-privacy"
bash bin/install-wp-tests.sh $DB_NAME $DB_USER $DB_PASS $DB_HOST latest

Write-Host "Test environment setup complete!" -ForegroundColor Green
Write-Host "You can now run tests using: composer test" -ForegroundColor Yellow
