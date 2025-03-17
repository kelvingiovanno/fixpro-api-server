<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppConfigService 
{
    public static function generateAndStoreKey()
    {
        $randomKey = Str::random(32);
        $existing = DB::table('app_meta')->where('key', 'code')->first();

        if (!$existing) {
            DB::table('app_meta')->insert([
                'key' => 'code',
                'value' => $randomKey,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            echo "\nGenerated Key: \e[32m$randomKey\e[0m\n";
        } else {
            echo "\nExisting Key: \e[33m{$existing->value}\e[0m\n";
        }
    }
}
