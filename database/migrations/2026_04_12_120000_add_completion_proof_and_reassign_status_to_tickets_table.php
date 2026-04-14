<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'completion_note')) {
                $table->text('completion_note')->nullable()->after('closed_at');
            }
            if (!Schema::hasColumn('tickets', 'completion_photo_path')) {
                $table->string('completion_photo_path')->nullable()->after('completion_note');
            }
            if (!Schema::hasColumn('tickets', 'completion_photo_name')) {
                $table->string('completion_photo_name')->nullable()->after('completion_photo_path');
            }
            if (!Schema::hasColumn('tickets', 'completion_photo_type')) {
                $table->string('completion_photo_type')->nullable()->after('completion_photo_name');
            }
            if (!Schema::hasColumn('tickets', 'completion_reported_at')) {
                $table->timestamp('completion_reported_at')->nullable()->after('completion_photo_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $dropColumns = [];

            foreach ([
                'completion_note',
                'completion_photo_path',
                'completion_photo_name',
                'completion_photo_type',
                'completion_reported_at',
            ] as $column) {
                if (Schema::hasColumn('tickets', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if (!empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};

