<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('iranian-banks.table', 'iranian_banks');

        if (! Schema::hasColumn($table, 'iban_code')) {
            return;
        }

        if (! Schema::hasColumn($table, 'iban_codes')) {
            Schema::table($table, function (Blueprint $blueprint): void {
                $blueprint->json('iban_codes')->nullable()->after('card_prefixes');
            });
        }

        foreach (DB::table($table)->orderBy('id')->get() as $bank) {
            $code = $bank->iban_code ?? null;

            DB::table($table)->where('id', $bank->id)->update([
                'iban_codes' => json_encode(filled($code) ? [$code] : []),
            ]);
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->dropUnique(['iban_code']);
            $blueprint->dropColumn('iban_code');
        });
    }

    public function down(): void
    {
        $table = config('iranian-banks.table', 'iranian_banks');

        if (Schema::hasColumn($table, 'iban_code') || ! Schema::hasColumn($table, 'iban_codes')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->string('iban_code', 3)->nullable()->after('card_prefixes');
        });

        foreach (DB::table($table)->orderBy('id')->get() as $bank) {
            $codes = json_decode($bank->iban_codes ?? '[]', true) ?: [];

            DB::table($table)->where('id', $bank->id)->update([
                'iban_code' => $codes[0] ?? null,
            ]);
        }

        $seen = [];

        foreach (DB::table($table)->orderBy('id')->get() as $bank) {
            if ($bank->iban_code === null) {
                continue;
            }

            if (isset($seen[$bank->iban_code])) {
                DB::table($table)->where('id', $bank->id)->update(['iban_code' => null]);
            } else {
                $seen[$bank->iban_code] = true;
            }
        }

        Schema::table($table, function (Blueprint $blueprint): void {
            $blueprint->unique('iban_code');
        });

    }
};
