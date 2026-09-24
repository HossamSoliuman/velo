<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
                'role' => 'admin',
                'is_active' => true,
            ],
        );

        $this->call([
            SiteSettingSeeder::class,
            CatalogSeeder::class,
        ]);
    }
}
