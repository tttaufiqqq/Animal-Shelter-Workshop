<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * These modifications are for Danish's database (Booking & Adoption Module)
     */
    public function up(): void
    {
        // Add composite unique index to animal_booking table
        Schema::connection('danish')->table('animal_booking', function (Blueprint $table) {
            // Add composite unique index to prevent duplicate bookings
            // This ensures an animal can't have multiple bookings at same date/time
            $table->unique(['animalID', 'bookingID'], 'unique_animal_booking');
        });

        // Add index on booking date and time for better query performance
        Schema::connection('danish')->table('booking', function (Blueprint $table) {
            $table->index(['appointment_date', 'appointment_time', 'status'], 'idx_booking_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection('danish')->getDriverName();

        // Check if unique index exists before dropping
        if ($this->indexExists('animal_booking', 'unique_animal_booking')) {
            Schema::connection('danish')->table('animal_booking', function (Blueprint $table) use ($driver) {
                // For distributed architecture, we only have bookingID FK (not animalID)
                if ($driver === 'mysql') {
                    // Drop foreign key first
                    $table->dropForeign(['bookingID']);

                    // Drop the unique index
                    $table->dropUnique('unique_animal_booking');

                    // Recreate the foreign key
                    $table->foreign('bookingID')
                        ->references('id')
                        ->on('booking')
                        ->onDelete('cascade');
                } else if ($driver === 'sqlsrv') {
                    // SQL Server: Drop foreign key first
                    $table->dropForeign(['bookingID']);

                    // Drop the unique index
                    $table->dropUnique('unique_animal_booking');

                    // Recreate the foreign key
                    $table->foreign('bookingID')
                        ->references('id')
                        ->on('booking')
                        ->onDelete('cascade');
                } else {
                    // PostgreSQL can drop unique indexes directly
                    $table->dropUnique('unique_animal_booking');
                }
            });
        }

        // Check if index exists before dropping
        if ($this->indexExists('booking', 'idx_booking_schedule')) {
            Schema::connection('danish')->table('booking', function (Blueprint $table) {
                $table->dropIndex('idx_booking_schedule');
            });
        }
    }

    /**
     * Check if an index exists on a table in Danish's database
     */
    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::connection('danish')->getDriverName();

        switch ($driver) {
            case 'mysql':
                $result = DB::connection('danish')->select(
                    "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
                    [$index]
                );
                return !empty($result);

            case 'pgsql':
                $result = DB::connection('danish')->select(
                    "SELECT indexname FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                    [$table, $index]
                );
                return !empty($result);

            case 'sqlsrv':
                $result = DB::connection('danish')->select(
                    "SELECT name FROM sys.indexes WHERE object_id = OBJECT_ID(?) AND name = ?",
                    [$table, $index]
                );
                return !empty($result);

            default:
                return false;
        }
    }
};
