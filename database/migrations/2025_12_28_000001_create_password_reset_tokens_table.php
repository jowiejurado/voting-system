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
		$tableName = config('auth.passwords.users.table', 'password_reset_tokens');

		if (!Schema::hasTable($tableName)) {
			Schema::create($tableName, function (Blueprint $table) {
				$table->string('email')->primary();
				$table->string('token');
				$table->timestamp('created_at')->nullable();
			});
		}
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$tableName = config('auth.passwords.users.table', 'password_reset_tokens');
		Schema::dropIfExists($tableName);
	}
};


