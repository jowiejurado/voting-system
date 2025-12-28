<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			// Nullable so existing rows are not broken; unique to prevent duplicates.
			if (!Schema::hasColumn('users', 'email')) {
				$table->string('email')->nullable()->unique()->after('member_id');
			}
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			if (Schema::hasColumn('users', 'email')) {
				$table->dropUnique(['email']);
				$table->dropColumn('email');
			}
		});
	}
};


