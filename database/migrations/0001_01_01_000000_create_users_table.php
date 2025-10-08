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
		Schema::create('users', function (Blueprint $table) {
			$table->id();
			$table->string('admin_id')->nullable()->unique();
			$table->string('member_id')->nullable()->unique();
			$table->string('last_name');
			$table->string('first_name');
			$table->string('password');
			$table->string('phone_number');
			$table->string('organization_name')->nullable();
			$table->enum('type', ['voter', 'admin', 'system-admin'])->default('voter');
			$table->boolean('has_voted')->default(false);
			$table->boolean('is_active')->default(true);
			$table->datetime('last_signed_in')->nullable();
			$table->datetime('last_signed_out')->nullable();
			$table->rememberToken();
			$table->timestamps();
		});

		Schema::create('sessions', function (Blueprint $table) {
			$table->string('id')->primary();
			$table->foreignId('user_id')->nullable()->index();
			$table->string('ip_address', 45)->nullable();
			$table->text('user_agent')->nullable();
			$table->longText('payload');
			$table->integer('last_activity')->index();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('users');
		Schema::dropIfExists('sessions');
	}
};
