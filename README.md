# SES Match

SES企業内で営業案件とSEの関心・マッチング状況を管理するLaravel 12製MVPです。

## 主な機能

- 営業 / SEのログインとロール別アクセス制御
- 営業による案件CRUD、公開状態管理
- SE向け公開中案件一覧・詳細
- 「気になる！」送信、複合ユニーク制約による重複防止
- matched 中の新規送信禁止と completed 後の解除
- 営業による申請ステータス更新
- Policy、Form Request、Enum、Eloquentリレーション
- MySQL用Docker ComposeとサンプルSeeder

## 起動方法

前提: PHP 8.2以上、Composer、Docker

    docker compose up -d mysql
    composer install
    php artisan migrate:fresh --seed
    php artisan serve

http://localhost:8000 へアクセスしてください。CSSはビルド不要のTailwind CDNを使用します。

### テストアカウント

| ロール | メール | パスワード |
|---|---|---|
| 営業 | sales@example.com | password |
| SE | engineer@example.com | password |
| SE | engineer2@example.com | password |

## テスト

テスト専用MySQL DBを初回のみ作成します。

    docker compose exec mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS ses_match_test; GRANT ALL PRIVILEGES ON ses_match_test.* TO 'ses_match'@'%';"
    php artisan test

権限制御、公開制御、重複送信防止、マッチ中の排他制御、完了後の解除をFeatureテストで検証します。
