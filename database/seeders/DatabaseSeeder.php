<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $sales = User::updateOrCreate(['email' => 'sales@example.com'], ['name' => '営業 太郎', 'password' => Hash::make('password'), 'role' => UserRole::Sales]);
        $engineer = User::updateOrCreate(['email' => 'engineer@example.com'], ['name' => 'SE 花子', 'password' => Hash::make('password'), 'role' => UserRole::Engineer, 'profile' => 'PHP / Laravel 3年、フルリモート希望']);
        $engineer2 = User::updateOrCreate(['email' => 'engineer2@example.com'], ['name' => 'SE 次郎', 'password' => Hash::make('password'), 'role' => UserRole::Engineer]);
        $sales->assignedEngineers()->syncWithoutDetaching([$engineer->id, $engineer2->id]);
        Project::updateOrCreate(['sales_user_id' => $sales->id, 'title' => 'ECサイト刷新 Laravelエンジニア'], ['description' => '既存ECサイトのリプレイスに伴う設計・開発です。', 'required_skills' => 'PHP、Laravel、MySQLでの開発経験', 'preferred_skills' => 'AWS、Docker', 'process' => '基本設計〜テスト', 'location' => '東京都', 'remote_type' => 'hybrid', 'min_price' => 65, 'max_price' => 80, 'recruitment_count' => 2, 'start_date' => today()->addMonth(), 'application_deadline' => today()->addMonth(), 'status' => ProjectStatus::Open]);
    }
}
