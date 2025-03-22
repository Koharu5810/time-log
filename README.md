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
3. .env ファイルを作成（`cp .env.example .env`）し、以下の環境変数を修正する。

```text
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
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


## ログイン情報
**一般ユーザ会員登録後のメール認証**

アプリケーションをブラウザで確認時に、会員登録画面で登録後メール認証を行うには  
http://localhost:8025  
へアクセスし、本文記載の認証ボタンをクリックする。


**一般ユーザのログイン**

http://localhost/login へアクセス  
UsersTableSeederに記述のメールアドレス、パスワードを使用


**管理者ユーザのログイン**

http://localhost/admin/login へアクセス  
AdminsTableSeederに記述のメールアドレス、パスワードを使用


## 使用技術(実行環境)

| 言語・フレームワーク | バージョン |
| :------------------- | :--------- |
| PHP                  | 8.3.19     |
| Laravel              | 10.48.28   |
| MySQL                | 8.4.4      |
| MailHog              |            |

## ER 図

![alt](erd.png)

## URL

- 開発環境 : http://localhost/login
- 開発環境（管理者）：http://localhost/admin/login
- phpMyAdmin : http://localhost:8080/
- MailHog : http://localhost:8025/
