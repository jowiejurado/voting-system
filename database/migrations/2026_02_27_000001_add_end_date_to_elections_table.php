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
		Schema::table('elections', function (Blueprint $table) {
			// Keep type consistent with existing encrypted text columns
			$table->text('end_date')->nullable()->after('date');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('elections', function (Blueprint $table) {
			$table->dropColumn('end_date');
		});
	}
};
