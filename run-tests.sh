#!/bin/bash

# Exit immediately if a command exits with a non-zero status
set -e

# Variables
DB_NAME="wordpress_test"
DB_USER="root"
DB_PASS=""
DB_HOST="mysql" # Use the MySQL container service name
WP_VERSION=${WP_VERSION:-latest}

# Install WordPress test suite if not already installed
if [ ! -d "/tmp/wordpress-tests-lib" ]; then
    bash /usr/local/bin/install-wp-tests.sh $DB_NAME $DB_USER $DB_PASS $DB_HOST $WP_VERSION
fi

# Install PHPUnit if not already installed
composer global require "phpunit/phpunit=5.7.*"

# Verify PHPUnit is installed and available
which phpunit || { echo "PHPUnit not found, installation failed"; exit 1; }

# Run PHPCS to check coding standards
phpcs

# Run PHPUnit tests
phpunit

# Run tests for multisite
WP_MULTISITE=1 phpunit
