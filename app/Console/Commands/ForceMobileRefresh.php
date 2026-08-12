<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ForceMobileRefresh extends Command
{
    protected $signature = 'mobile:force-refresh {--all : Revoke token semua user} {--user_id= : Revoke token 1 user tertentu}';

    protected $description = 'Paksa refresh sesi mobile dengan mencabut token Sanctum dan membersihkan token FCM.';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $userId = $this->option('user_id');

        if (! $all && ! $userId) {
            $this->error('Gunakan --all atau --user_id=ID');

            return self::INVALID;
        }

        $query = User::query();
        if ($userId) {
            $query->whereKey($userId);
        }

        $userIds = $query->pluck('id');

        if ($userIds->isEmpty()) {
            $this->warn('User tidak ditemukan.');

            return self::SUCCESS;
        }

        $revokedTokenCount = DB::table('personal_access_tokens')
            ->whereIn('tokenable_id', $userIds)
            ->where('tokenable_type', User::class)
            ->delete();

        $updatedUserCount = User::query()
            ->whereIn('id', $userIds)
            ->update(['fcm_token' => null]);

        Cache::flush();

        $this->info('Force refresh mobile berhasil dijalankan.');
        $this->line('User terdampak: '.$updatedUserCount);
        $this->line('Token Sanctum dicabut: '.$revokedTokenCount);

        return self::SUCCESS;
    }
}
