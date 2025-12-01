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
		Schema::create('elections', function (Blueprint $table) {
			$table->id();
			$table->text('title');
			$table->text('date');
			$table->text('start_time');
			$table->text('end_time');
			$table->boolean('is_active')->default(false);
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('elections');
	}
};
