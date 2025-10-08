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
		Schema::create('qr_tokens', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // link to voter
			$table->uuid('code')->unique();          // what the QR carries (opaque)
			$table->string('purpose')->index();      // e.g. 'voter_checkin'
			$table->timestamp('expires_at')->nullable();
			$table->timestamp('used_at')->nullable();
			$table->json('meta')->nullable();        // optional
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('qr_tokens');
	}
};
