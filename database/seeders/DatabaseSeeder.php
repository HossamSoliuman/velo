<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => config('velo.admin.email')],
            [
                'name' => config('velo.admin.name'),
                'password' => config('velo.admin.password'),
                'role' => UserRole::Admin,
                'is_active' => true,
            ],
        );

        $this->call([
            SiteSettingSeeder::class,
            PageSeeder::class,
            CatalogSeeder::class,
        ]);

        // Model events are muted while seeding, so the observers never clear these caches.
        Cache::forget(SiteSetting::CACHE_KEY);
        Cache::forget(Category::NAVIGATION_CACHE_KEY);
    }
}
