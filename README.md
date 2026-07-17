# SES Match

SES企業内で、営業が保有する案件とSEの関心・マッチング状況、担当関係を管理するLaravel製MVPです。

営業は案件の登録・公開、「気になる！」への対応、SEへの担当勧誘を行えます。SEは募集中案件の閲覧、「気になる！」の送信、営業から届いた担当勧誘への回答を行えます。

## 公開環境

- URL: [https://web-production-b6c3a.up.railway.app](https://web-production-b6c3a.up.railway.app)
- 公開方式: Railway上のHTTPS + HTTP Basic認証 + アプリ内ログイン
- Basic認証情報: リポジトリには保存せず、管理者から別経路で共有
- ヘルスチェック: `/up`（Basic認証の対象外）

## 対象ユーザーと権限

| 機能 | 営業 | SE |
|---|:---:|:---:|
| ユーザー登録・ログイン | ○ | ○ |
| プロフィール編集 | ○ | ○ |
| 自分の案件の登録・閲覧・編集・削除 | ○ | - |
| 募集中案件の閲覧 | - | ○ |
| 「気になる！」の送信・履歴確認 | - | ○ |
| 受信した「気になる！」の確認・状態更新 | ○ | - |
| 送信済み「気になる！」の取り消し | - | ○ |
| 担当SEの一覧・プロフィール確認 | ○ | - |
| 担当営業の一覧・プロフィール確認 | - | ○ |
| SEへの担当勧誘 | ○ | - |
| 担当勧誘の承諾・拒否 | - | ○ |

営業用・SE用のURLはロールミドルウェアで分離されています。別ロールの画面へのアクセスは `403 Forbidden` になります。また、案件や「気になる！」はPolicyにより所有者以外からの更新を拒否します。

## 主な利用フロー

### 営業

1. 営業ロールで登録またはログインします。
2. 「案件管理」から案件を登録します。
3. 案件の状態を「公開中」にすると、応募期限内に限りSEの案件一覧へ表示されます。
4. SEから届いた「気になる！」を確認し、状況に応じてステータスを更新します。
5. 必要に応じて「SEを探す」から担当候補へ勧誘を送ります。
6. SEが勧誘を承諾すると、そのSEが担当SE一覧へ追加されます。

### SE

1. SEロールで登録またはログインします。
2. 「案件を探す」から募集中案件を閲覧します。
3. 希望する案件へ「気になる！」を送信します。
4. 営業がステータスを更新すると、送信履歴から現在の状況を確認できます。
5. 営業から担当勧誘が届いた場合は、承諾または拒否を選択します。
6. 承諾した営業は担当営業一覧に追加されます。複数の営業と担当関係を持てます。

## 機能仕様

### 認証・プロフィール

- 新規登録時に `営業` または `SE` を選択
- 名前、メールアドレス、パスワードによるログイン
- 名前とプロフィール文（最大5,000文字）を編集可能
- 未ログイン状態で業務画面へアクセスした場合はログイン画面へリダイレクト
- デプロイ環境では任意でアプリ全体にHTTP Basic認証を追加可能

### 案件管理

案件は作成した営業だけが閲覧・更新・削除できます。

| 項目 | 必須 | 制約 |
|---|:---:|---|
| 案件名 | ○ | 255文字以内 |
| 案件概要 | ○ | テキスト |
| 必須スキル | ○ | テキスト |
| 歓迎スキル | - | テキスト |
| 担当工程 | ○ | 255文字以内 |
| 勤務地 | ○ | 255文字以内 |
| 勤務形態 | ○ | `出社` / `ハイブリッド` / `リモート` |
| 最低単価 | ○ | 0以上の整数 |
| 最高単価 | - | 最低単価以上の整数 |
| 募集人数 | ○ | 1以上の整数 |
| 開始日 | - | 日付 |
| 応募期限 | ○ | 登録・更新日以降 |
| 公開状態 | ○ | `下書き` / `公開中` / `募集終了` |

SEに表示されるのは、公開状態が `公開中` かつ応募期限が当日以降の案件だけです。募集対象外の案件詳細へSEが直接アクセスした場合は `404` になります。

### 「気になる！」とマッチング

SEは案件に対して定型メッセージ付きの「気になる！」を送信します。

- 同じSEから同じ案件へ送信できるのは1回のみ
- アプリ側の検証に加え、DBの複合ユニーク制約でも重複を防止
- `マッチ成立` の案件があるSEは、別案件へ新規送信不可
- マッチした案件が `案件終了` になると、別案件へ送信可能
- SEが取り消せるのは、自分が送信した `営業確認待ち` のデータだけ
- 取り消しは物理削除せず `キャンセル` へ変更
- 営業は自分の案件に届いたデータだけを更新可能

状態は次の6種類です。

| 内部値 | 表示名 | 用途 |
|---|---|---|
| `pending` | 営業確認待ち | SEが送信した直後 |
| `reviewing` | 確認中 | 営業が内容を確認中 |
| `matched` | マッチ成立 | 案件とのマッチが成立 |
| `rejected` | 見送り | 今回は見送り |
| `cancelled` | キャンセル | SEが送信を取り消し |
| `completed` | 案件終了 | マッチした案件が終了 |

### 担当勧誘

営業からSEへ、担当関係を結ぶための勧誘を送信できます。

- 既に自分の担当となっているSEは候補一覧に表示しない
- 同じ営業・SE間で回答待ちの勧誘を重複送信できない
- 勧誘送信時にメールを送信
- SE本人だけが承諾・拒否できる
- 回答済みの勧誘へ再回答できない
- 承諾時は営業・SE間の担当関係を追加
- SEは複数の営業と担当関係を持てる
- 拒否後は同じ営業から再勧誘可能

勧誘状態は `回答待ち`、`承諾`、`拒否` の3種類です。

## データモデル概要

```text
users
  ├─< projects
  │    └─< interests >─ users (SE)
  ├─< assignment_invitations >─ users (SE)
  └─< engineer_sales >─ users (SE)
```

- `users.role`: `sales` または `engineer`
- `projects.sales_user_id`: 案件を所有する営業
- `interests`: 案件とSEの「気になる！」を管理
- `assignment_invitations`: 営業からSEへの担当勧誘を管理
- `engineer_sales`: 営業とSEの多対多の担当関係を管理

ユーザー、案件などの親データ削除時は、関連する業務データも外部キー制約により削除されます。

## 技術構成

| 分類 | 技術 |
|---|---|
| バックエンド | PHP 8.2以上 / Laravel 12 |
| データベース | MySQL（ローカル: 8.4、Railway: Managed MySQL） |
| UI | Blade / Tailwind CSS CDN / Google Fonts |
| Webサーバー | NGINX + PHP-FPM |
| コンテナ | Docker / `serversideup/php:8.4-fpm-nginx` |
| テスト | PHPUnit 11 / Laravel Feature Test |
| ホスティング | Railway |
| 認可 | Middleware / Policy / Form Request |
| 状態管理 | PHP Enum |

フロントエンドはTailwind CSS CDNを使用しているため、通常の画面確認にNode.jsやアセットビルドは不要です。

## ローカル環境の構築

### 必要なもの

- PHP 8.2以上
- Composer
- Docker DesktopまたはDocker Engine
- PHP MySQL拡張

### 1. 依存関係と環境ファイル

```bash
git clone https://github.com/AyumuYamada-0515/ses_matching_app.git
cd ses_matching_app
composer install
cp .env.example .env
php artisan key:generate
```

### 2. MySQLの起動

```bash
docker compose up -d mysql
```

`.env` のDB設定を次のように変更します。

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ses_match
DB_USERNAME=ses_match
DB_PASSWORD=secret
```

### 3. テーブルとサンプルデータの作成

```bash
php artisan migrate:fresh --seed
```

`migrate:fresh` は既存テーブルを削除して作り直すため、保持すべきデータがある環境では使用しないでください。既存データを保持して更新する場合は `php artisan migrate` を使用します。

### 4. アプリケーションの起動

```bash
php artisan serve
```

[http://localhost:8000](http://localhost:8000) を開きます。

コンテナ内のNGINXを使う場合は、依存関係と `.env` を準備した後に次のコマンドでも起動できます。

```bash
docker compose up -d app mysql
```

この起動方法ではアプリコンテナからMySQLへ接続するため、`.env` の `DB_HOST=mysql` に変更してください。ホスト側で `php artisan serve` を使う場合は `DB_HOST=127.0.0.1` に戻します。

この場合は [http://localhost:8080](http://localhost:8080) を開きます。

## サンプルアカウント

`php artisan migrate:fresh --seed` の実行後、次のアカウントを利用できます。

| ロール | メールアドレス | パスワード |
|---|---|---|
| 営業 | `sales@example.com` | `password` |
| SE | `engineer@example.com` | `password` |
| SE | `engineer2@example.com` | `password` |

Seederはサンプル案件と営業・SE間の担当関係も作成します。

## メール

ローカル環境の初期設定では `MAIL_MAILER=log` を使用し、担当勧誘メールを実送信せずログへ出力します。

```bash
tail -f storage/logs/laravel.log
```

実際にメールを送信する場合は、`.env` の `MAIL_*` を利用するメールサービスに合わせて設定してください。

## 限定公開の設定

`DEPLOYMENT_ACCESS_USERNAME` と `DEPLOYMENT_ACCESS_PASSWORD` の両方を設定すると、`/up` を除くすべてのURLがHTTP Basic認証で保護されます。どちらかが未設定の場合、Basic認証は無効です。

```dotenv
DEPLOYMENT_ACCESS_USERNAME=preview
DEPLOYMENT_ACCESS_PASSWORD=<十分に長いランダムなパスワード>
```

本番用の値やパスワードは `.env`、Railway Variablesなどの秘密情報ストアで管理し、Gitへコミットしないでください。

## テスト

テストはMySQLの `ses_match_test` データベースを使用します。初回だけテストDBを作成します。

```bash
docker compose exec mysql mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS ses_match_test; GRANT ALL PRIVILEGES ON ses_match_test.* TO 'ses_match'@'%';"
php artisan test
```

現在のFeature Testでは、主に次を検証しています。

- 未ログイン・ロール別アクセス制御
- 案件所有者と「気になる！」所有者の認可
- 公開中かつ期限内の案件だけがSEへ表示されること
- 「気になる！」の重複防止
- マッチ中の新規送信禁止と案件終了後の解除
- pending状態だけを本人が取り消せること
- 営業・SEの新規登録
- 担当勧誘の送信、メール、承諾、拒否、重複回答防止
- 複数営業とSEの担当関係
- デプロイ用Basic認証
- クラウドプロキシ配下でHTTPS URLが維持されること

特定のテストだけを実行する例:

```bash
php artisan test tests/Feature/SesMatchTest.php
php artisan test --filter=test_engineer_cannot_send_duplicate_interest
```

## Railwayへのデプロイ

リポジトリ直下の `Dockerfile` と `railway.json` をRailwayが使用します。

- Webコンテナの待受ポート: `8080`
- ヘルスチェック: `/up`
- 起動時処理: Laravelキャッシュ生成、マイグレーション
- 再起動ポリシー: 失敗時に最大5回
- DB: Railway MySQLの内部ネットワークを使用

主なWebサービス環境変数:

```dotenv
APP_NAME="SES Match"
APP_ENV=production
APP_DEBUG=false
APP_KEY=<php artisan key:generate --show の値>
APP_URL=https://<railway-domain>
PORT=8080

DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

DEPLOYMENT_ACCESS_USERNAME=<basic-auth-user>
DEPLOYMENT_ACCESS_PASSWORD=<basic-auth-password>
```

Railway CLIで現在の作業ツリーをデプロイする場合:

```bash
railway login
railway link
railway up --service web --ci
```

デプロイ後は次を確認します。

1. Railwayのデプロイ状態が `SUCCESS`
2. `/up` が `200 OK`
3. Basic認証なしの `/login` が `401 Unauthorized`
4. Basic認証後の `/login` が `200 OK`
5. サンプルアカウントでログインし、ロール別ダッシュボードが表示される

## ディレクトリ構成

```text
app/
  Enums/               状態とロールのEnum
  Http/Controllers/    ロール別の画面・操作
  Http/Middleware/     ロール制御、デプロイ用Basic認証
  Http/Requests/       案件入力の検証
  Models/              Eloquentモデル
  Policies/            案件・「気になる！」の認可
database/
  migrations/          テーブル定義
  seeders/             サンプルデータ
resources/views/
  auth/                ログイン・登録
  engineer/            SE向け画面
  sales/               営業向け画面
routes/
  web.php              Webルート
tests/
  Feature/             業務ルール・認可の結合テスト
  Unit/                単体テスト
```

## 現在のMVP範囲

現時点では、次の機能は対象外です。

- メールアドレス認証
- パスワード再設定画面
- 管理者ロール・管理画面
- 案件検索、絞り込み、並び替え
- ファイルアップロード
- 通知センター
- API
- キューワーカーの常駐運用
- 監査ログ、論理削除
- 本番向けメールサービスの標準設定

## ライセンス

[MIT License](LICENSE)
