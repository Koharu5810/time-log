# time-log（勤怠管理アプリ）

企業開発の独自勤怠管理アプリ  
メール認証によって、認証済みユーザのみが操作を行える  
一般ユーザは勤怠申請、管理者ユーザは勤怠管理を行える

## 環境構築

**Docker ビルド**

1. アプリケーションをクローンするディレクトリに移動。
2. `git clone git@github.com:Koharu5810/time-log.git`
3. `cd time-log`
4. DockerDesktop アプリを立ち上げる（`open -a docker`）。
5. `docker-compose up -d --build`

**Laravel 環境構築**

1. `docker-compose exec php bash`
2. `composer install`
3. 「.env.example」ファイルを 「.env」ファイルに命名を変更。  
    または、新しく.env ファイルを作成（`cp .env.example .env`）。
4. .env に以下の環境変数を追加する。

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp
MAIL_ENCODING=UTF-8
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=test
MAIL_PASSWORD=pass
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=test@abc.com
MAIL_FROM_NAME="${APP_NAME}"
```

5. アプリケーションキーを作成し、キャッシュをクリア

```bash
php artisan key:generate

php artisan config:clear
php artisan cache:clear
```

6. マイグレーション・シーディングの実行

```bash
php artisan migrate --seed
```


**テスト用データベースの設定**

1. MySQLコンテナ内でコマンド実行

``` bash
mysql -u root -p
CREATE DATABASE test_database;
```

2. テスト用の環境変数ファイルを作成し、テスト用データベース設定に編集する。

``` bash
cp .env.example .env.testing


APP_ENV=test

DB_CONNECTION=mysql_test
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=test_database
DB_USERNAME=root
DB_PASSWORD=root
```

3. テスト用のアプリケーションキーを作成し、キャッシュクリア

``` bash
php artisan key:generate --env=testing

php artisan config:clear
php artisan cache:clear
```

4. テスト用データベースにマイグレーションとシーディングを適用する

``` bash
php artisan migrate --env=testing
php artisan db:seed --env=testing
```

<br>

**会員登録後のメール認証**

アプリケーションをブラウザで確認時に、会員登録画面で登録後メール認証を行うには  
http://localhost:8025  
へダイレクトし、本文記載の認証ボタンをクリックする。
<br><br>


## 使用技術(実行環境)

| 言語・フレームワーク | バージョン |
| :------------------- | :--------- |
| PHP                  | 8.3.13     |
| Laravel              | 8.83.27    |
| MySQL                | 9.0.1      |
| Stripe               | 9.9.0      |
| MailHog              |            |

## ER 図

![alt](erd.png)

## URL

- 開発環境 : http://localhost/
- 開発環境（管理者）：http://localhost/admin/login
  （AdminsTableSeederに記述のメールアドレス、パスワードを使用）
- phpMyAdmin : http://localhost:8080/
- MailHog : http://localhost:8025/
