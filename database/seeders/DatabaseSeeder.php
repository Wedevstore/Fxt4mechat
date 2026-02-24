<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Addon;
use Illuminate\Database\Seeder;
use Database\Seeders\ClientSeeder;
use Database\Seeders\ContactsListSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TimeZoneSeeder::class);
        $this->call(CurrencySeeder::class);
        $this->call(EmailTemplateSeeder::class);
        $this->call(FlagIconSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(ClientSeeder::class);
        $this->call(ContactsListSeeder::class);
        $this->call(SegmentsSeeder::class);
        $this->call(ContactsTableSeeder::class);
        // $this->call(SubscriptionSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(SubscriptionSeeder::class);
        $this->call(PageSeeder::class);
    }
}
