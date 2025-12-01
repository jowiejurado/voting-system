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
		Schema::create('candidates', function (Blueprint $table) {
			$table->id();
			$table->foreignId('election_id')->constrained('elections')->cascadeOnDelete();
			$table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
			$table->text('last_name');
			$table->text('first_name');
			$table->text('organization_name');
			$table->softDeletes();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('candidates');
	}
};
