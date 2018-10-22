### Detailed guide how to install this app

---
### 1) Clone current repository:
```
git clone https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API.git [APP_NAME]
```
### 2) Navigate to the clonned derictory
```
cd [APP_NAME]
```
### 3) Install required Composer dependencies:
```
composer install
```
### 4) Rename environment config file:
```
cp .env.example .env
```
### 5) In .env file fill in correct data:
(*Default DB is PostgresSQL. If you want to use another DB just update below environment variables* )
```
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=postgres_db
DB_USERNAME=postgres
DB_PASSWORD=postgres
```
### 6) By default app uses [Redis](https://redis.io/) cache driver. **Make sure redis is available on your server**.
(*If you want to use other cache driver just update relevant variable in .env file*)
```
CACHE_DRIVER=redis
```
### 6) Install NodeJS dependencies and compile assets:
```
npm install
npm run dev
```
### 7) In order to make user sessions and other application encrypted data more secure generate new app key:
```
php artisan key:generate
```
### 8) In order to enable secure JWT token generation seet the **jwt-auth** secret key by running the following command:
```
php artisan jwt:secret
```
### 9) In order to be able to generate videothumbnails make sure that [FFmpeg](https://www.ffmpeg.org/) is installed on your server. In **.env** file set path to **ffmpeg** and **ffprobe** executable files:
```
FFMPEG="/usr/bin/ffmpeg"
FFPROBE="/usr/bin/ffprobe"
```
### 10) Generate required migrations and migrate it
```
php artisan migrate
```
### 10) Run Symfony built-in development server
```
php artisan server
```
### 11) Navigate in browser to [http://127.0.0.1:8000](http://127.0.0.1:8000) and enjoy the project!


---
