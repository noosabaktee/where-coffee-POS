<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $hasExistingData = DB::table('categories')->exists() || DB::table('expenses')->exists();

        Schema::table('categories', function (Blueprint $table): void {
            $table->string('type', 20)->default('product')->after('id');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropUnique(['name']);
            $table->unique(['type', 'code']);
            $table->unique(['type', 'name']);
            $table->index(['type', 'is_active']);
        });

        if (! $hasExistingData) {
            return;
        }

        $expenseNames = DB::table('expenses')
            ->whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->pluck('category')
            ->merge([
                'Bahan Baku',
                'Utilitas',
                'Gaji Karyawan',
                'Promosi & Marketing',
                'Perawatan',
                'Lain-lain',
            ])
            ->unique()
            ->values();

        $preferredCodes = [
            'Bahan Baku' => 'EXP-BHN',
            'Utilitas' => 'EXP-UTL',
            'Gaji Karyawan' => 'EXP-GAJ',
            'Promosi & Marketing' => 'EXP-MKT',
            'Perawatan' => 'EXP-PRW',
            'Lain-lain' => 'EXP-LLN',
        ];

        foreach ($expenseNames as $index => $name) {
            if (DB::table('categories')->where('type', 'expense')->where('name', $name)->exists()) {
                continue;
            }

            $baseCode = $preferredCodes[$name] ?? 'EXP-'.Str::upper(Str::slug((string) $name));
            $baseCode = substr($baseCode ?: 'EXP-BIAYA', 0, 30);
            $code = $baseCode;
            $suffix = 2;

            while (DB::table('categories')->where('type', 'expense')->where('code', $code)->exists()) {
                $suffixText = '-'.$suffix++;
                $code = substr($baseCode, 0, 30 - strlen($suffixText)).$suffixText;
            }

            DB::table('categories')->insert([
                'type' => 'expense',
                'code' => $code,
                'name' => $name,
                'icon' => 'bx-receipt',
                'is_active' => true,
                'sort_order' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('categories')->where('type', 'expense')->delete();

        Schema::table('categories', function (Blueprint $table): void {
            $table->dropUnique(['type', 'code']);
            $table->dropUnique(['type', 'name']);
            $table->dropIndex(['type', 'is_active']);
            $table->dropColumn('type');
        });

        Schema::table('categories', function (Blueprint $table): void {
            $table->unique('code');
            $table->unique('name');
        });
    }
};
