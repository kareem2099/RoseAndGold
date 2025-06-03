# Use an official PHP image with Apache (e.g., PHP 8.2)
FROM php:8.2-apache

# Install system dependencies required for pdo_sqlite and other common extensions
# - libsqlite3-dev: development files for SQLite3, needed for pdo_sqlite
# - pkg-config: helps find library configurations during build
# - Other -dev libraries can be added here if you install more extensions later
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    pkg-config \
    && rm -rf /var/lib/apt/lists/*

# Now install pdo_sqlite extension for SQLite support
RUN docker-php-ext-install pdo_sqlite

# Optional: Install other common extensions if your application might need them.
# Example:
# RUN apt-get update && apt-get install -y libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
#    && docker-php-ext-configure gd --with-freetype --with-jpeg \
#    && docker-php-ext-install gd zip mbstring \
#    && rm -rf /var/lib/apt/lists/*

# Copy application source code from your current directory (.)
# to Apache's default web root in the container (/var/www/html/)
COPY . /var/www/html/

# Ensure the web server (Apache, running as www-data user) can write to the SQLite database file
# if your application modifies it (inserts, updates, deletes data).
# This command will change ownership and permissions of the SQLite file if it exists after copying.
RUN if [ -f /var/www/html/rose_gold_db.sqlite ]; then \
        chown www-data:www-data /var/www/html/rose_gold_db.sqlite && \
        chmod u+w,g+w /var/www/html/rose_gold_db.sqlite; \
    fi \
    && if [ -d /var/www/html ]; then \
        # Also ensure the directory containing the SQLite file is writable by www-data,
        # as SQLite creates temporary journal files there.
        chown www-data:www-data /var/www/html && \
        chmod u+w,g+w /var/www/html; \
    fi

# Apache's default port is 80. The base image handles EXPOSE and starting Apache.
