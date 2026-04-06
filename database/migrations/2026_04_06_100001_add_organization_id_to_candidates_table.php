<?php

use App\Models\Organization;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('position_id')
                ->constrained('organizations')
                ->cascadeOnDelete();
        });

        $decryptLegacy = function (?string $raw): string {
            if ($raw === null || $raw === '') {
                return '';
            }
            try {
                $value = Crypt::decryptString($raw);
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                    return $decoded;
                }

                return is_string($value) ? $value : '';
            } catch (\Throwable $e) {
                return $raw;
            }
        };

        $nameToId = [];
        $defaultOrg = Organization::create(['name' => 'Unassigned']);
        $nameToId[''] = $defaultOrg->id;

        foreach (DB::table('candidates')->orderBy('id')->cursor() as $row) {
            $name = trim($decryptLegacy($row->organization_name));

            if ($name === '') {
                $orgId = $defaultOrg->id;
            } else {
                if (! isset($nameToId[$name])) {
                    $org = Organization::create(['name' => $name]);
                    $nameToId[$name] = $org->id;
                }
                $orgId = $nameToId[$name];
            }

            DB::table('candidates')->where('id', $row->id)->update(['organization_id' => $orgId]);
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropColumn('organization_name');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('candidates', function (Blueprint $table) {
            $table->text('organization_name')->after('first_name');
        });

        foreach (DB::table('candidates')->orderBy('id')->cursor() as $row) {
            $plain = '';
            if (! empty($row->organization_id)) {
                $org = Organization::find($row->organization_id);
                $plain = $org?->name ?? '';
            }
            DB::table('candidates')->where('id', $row->id)->update([
                'organization_name' => Crypt::encryptString($plain),
            ]);
        }

        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
