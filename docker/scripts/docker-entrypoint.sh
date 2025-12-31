#!/bin/bash
set -e

echo "========================================="
echo "Animal Shelter Workshop - Docker Init"
echo "========================================="

# Determine container role
ROLE=${CONTAINER_ROLE:-app}
echo "Container Role: $ROLE"

# ==========================================
# Function: Install Composer Dependencies
# ==========================================
install_dependencies() {
    echo ""
    echo "Installing Composer dependencies..."

    if [ ! -d "vendor" ]; then
        composer install --optimize-autoloader --no-interaction
        echo "✓ Composer dependencies installed!"
    else
        echo "✓ Vendor directory exists, skipping install."
    fi
}

# ==========================================
# Function: Wait for Database Connections
# ==========================================
wait_for_databases() {
    echo ""
    echo "Waiting for database connections..."

    max_attempts=30
    attempt=0

    while [ $attempt -lt $max_attempts ]; do
        attempt=$((attempt + 1))
        echo "Attempt $attempt/$max_attempts: Checking database connections..."

        if php artisan db:check-connections; then
            echo "✓ All required databases are online!"
            return 0
        fi

        echo "Waiting 2 seconds before retry..."
        sleep 2
    done

    echo "⚠ Warning: Database connection timeout after $max_attempts attempts"
    echo "Some databases may be offline. Proceeding anyway..."
    return 1
}

# ==========================================
# Function: Run Database Migrations
# ==========================================
run_migrations() {
    echo ""
    echo "========================================="
    echo "Running Database Migrations"
    echo "========================================="

    if php artisan migrate --force; then
        echo "✓ Migrations completed successfully!"
    else
        echo "⚠ Warning: Migration failed. Check logs for details."
        echo "You may need to run 'docker compose exec app php artisan migrate' manually."
    fi
}

# ==========================================
# Function: Seed Databases
# ==========================================
seed_databases() {
    echo ""
    echo "========================================="
    echo "Seeding Databases"
    echo "========================================="

    if php artisan db:seed --force; then
        echo "✓ Database seeding completed successfully!"
    else
        echo "⚠ Warning: Seeding failed. Check logs for details."
        echo "You may need to run 'docker compose exec app php artisan db:seed' manually."
    fi
}

# ==========================================
# Function: Clear Caches
# ==========================================
clear_caches() {
    echo ""
    echo "Clearing application caches..."

    php artisan config:clear
    php artisan cache:clear
    php artisan view:clear
    php artisan db:clear-status-cache

    echo "✓ Caches cleared!"
}

# ==========================================
# Function: Set Permissions
# ==========================================
set_permissions() {
    echo ""
    echo "Setting file permissions..."

    chown -R www-data:www-data /var/www/html/storage
    chown -R www-data:www-data /var/www/html/bootstrap/cache
    chmod -R 775 /var/www/html/storage
    chmod -R 775 /var/www/html/bootstrap/cache

    echo "✓ Permissions set!"
}

# ==========================================
# Main Initialization (App Container Only)
# ==========================================
if [ "$ROLE" = "app" ]; then
    echo ""
    echo "Initializing Laravel Application..."

    # Install Composer dependencies
    install_dependencies

    # Wait for databases
    wait_for_databases

    # Run migrations
    run_migrations

    # Seed databases if AUTO_SEED is true
    if [ "${AUTO_SEED:-true}" = "true" ]; then
        seed_databases
    else
        echo ""
        echo "⚠ AUTO_SEED is disabled. Skipping database seeding."
        echo "Run 'docker compose exec app php artisan db:seed' to seed manually."
    fi

    # Clear caches
    clear_caches

    # Set permissions
    set_permissions

    echo ""
    echo "========================================="
    echo "✓ Initialization Complete!"
    echo "========================================="
    echo "Starting application services..."
    echo ""
fi

# ==========================================
# Queue Worker Initialization
# ==========================================
if [ "$ROLE" = "queue" ]; then
    echo ""
    echo "Initializing Queue Worker..."
    echo "Waiting for app container to complete initialization..."
    sleep 10
    echo "✓ Queue worker ready!"
fi

# ==========================================
# Scheduler Initialization
# ==========================================
if [ "$ROLE" = "scheduler" ]; then
    echo ""
    echo "Initializing Task Scheduler..."
    echo "Waiting for app container to complete initialization..."
    sleep 10
    echo "✓ Task scheduler ready!"
fi

# ==========================================
# Execute Command
# ==========================================
exec "$@"
