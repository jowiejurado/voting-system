<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::dropIfExists('candidate_security_questions');
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// Intentionally empty: candidate security questions were moved to admin users.
	}
};
