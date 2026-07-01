<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('shelter')->create('audit_log', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50);
            $table->string('operation', 10);
            $table->unsignedBigInteger('record_id')->nullable();
            $table->text('old_values')->nullable();
            $table->text('new_values')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('table_name');
            $table->index('operation');
            $table->index('record_id');
            $table->index('user_id');
            $table->index('created_at');
        });

        $conn   = DB::connection('shelter');
        $sqlDir = database_path('sql/atiqah-shelter/data_integrity_triggers');

        $conn->unprepared(require "$sqlDir/trg_section.php");
        $conn->unprepared(require "$sqlDir/trg_slot.php");
        $conn->unprepared(require "$sqlDir/trg_category.php");
        $conn->unprepared(require "$sqlDir/trg_inventory.php");
    }

    public function down(): void
    {
        $conn = DB::connection('shelter');

        foreach (['section_after_insert', 'section_after_update', 'section_after_delete',
                  'slot_after_insert', 'slot_after_update', 'slot_after_delete',
                  'slot_before_insert_validate', 'slot_before_update_validate',
                  'category_after_insert', 'category_after_update', 'category_after_delete',
                  'inventory_after_insert', 'inventory_after_update', 'inventory_after_delete',
                  'inventory_before_insert_validate', 'inventory_before_update_validate'] as $t) {
            $conn->unprepared("DROP TRIGGER IF EXISTS {$t}");
        }

        Schema::connection('shelter')->dropIfExists('audit_log');
    }
};
