-- Base de datos para ejecutar las pruebas contra MySQL (php artisan test con DB_DATABASE=rejas_test).
CREATE DATABASE IF NOT EXISTS rejas_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON rejas_test.* TO 'rejas'@'%';
