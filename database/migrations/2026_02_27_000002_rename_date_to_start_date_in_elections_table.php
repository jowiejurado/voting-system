<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('elections', function (Blueprint $table) {
            // Add new column as nullable to allow backfill without DBAL
            if (!Schema::hasColumn('elections', 'start_date')) {
                $table->text('start_date')->nullable()->after('title');
            }
        });

        // Backfill start_date from date (copy encrypted payload as-is)
        DB::table('elections')->select('id', 'date')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('elections')->where('id', $row->id)->update([
                    'start_date' => $row->date,
                ]);
            }
        });

        // Drop old column if exists
        Schema::table('elections', function (Blueprint $table) {
            if (Schema::hasColumn('elections', 'date')) {
                $table->dropColumn('date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate old column as nullable and backfill from start_date
        Schema::table('elections', function (Blueprint $table) {
            if (!Schema::hasColumn('elections', 'date')) {
                $table->text('date')->nullable()->after('title');
            }
        });

        DB::table('elections')->select('id', 'start_date')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('elections')->where('id', $row->id)->update([
                    'date' => $row->start_date,
                ]);
            }
        });

        Schema::table('elections', function (Blueprint $table) {
            if (Schema::hasColumn('elections', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};

