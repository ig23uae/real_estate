### 🏠 Real Estate — Веб-приложение для посуточной аренды недвижимости

Проект на PHP для агентства недвижимости с функционалом бронирования, управления объектами, загрузки фото, оплат и админ-панелью.

---

## 🚀 Установка и запуск проекта

### 1. 📦 Клонирование репозитория

```bash
git clone https://github.com/your-username/real_estate.git
cd real_estate
2. ⚙️ Настройка .env
Скопируйте пример:

cp .env.example .env
Откройте файл .env и укажите параметры подключения к базе данных:

DB_HOST=localhost
DB_NAME=real_estate_rentals
DB_USER=root
DB_PASS=
3. 🗃 Импорт базы данных
Импортируйте базу данных database.sql через phpMyAdmin или консоль:

mysql -u root -p real_estate_rentals < database.sql
📁 Файл database.sql находится в корне проекта или в папке /database/.
4. 📁 Папка storage/
Создайте папку для хранения фотографий:

mkdir storage
chmod 775 storage
💻 Страницы

Роль	Страница	Назначение
Все	/index.php	Главная с каруселью объектов
Пользователь	/user/register.php, /user/login.php	Регистрация и вход
Пользователь	/user/dashboard.php	Личный кабинет с бронированиями
Админ	/admin/dashboard.php	Админ-панель
Админ	/admin/bookings.php	Все бронирования
Админ	/admin/payments.php	Просмотр оплат
Админ	/admin/add_property.php	Добавление объекта
Админ	/admin/edit_property.php?id=ID	Редактирование объекта
Админ	/admin/properties.php	Список всех объектов
Админ	/admin/property_bookings.php?id=ID	Все заселения/выселения по объекту
Общая	/functions/download_booking.php?id=ID	PDF-чек бронирования
Админ	/admin/checkin_checkout.php	Акт заселения / выселения (PDF)
👤 Роли пользователей

Роль	Доступ к	Описание
admin	Панель администратора	Управление системой
user	Личный кабинет	Бронирование объектов
Гость	Главная, регистрация	Просмотр и регистрация
🔑 Тестовые аккаунты

Роль	Email	Пароль
Администратор	admin@example.com	test12345
Или зарегистрируйтесь вручную.
✅ Функциональность

🔐 Авторизация с ролями (admin/user)
🏡 Добавление и редактирование объектов
📷 Загрузка главного фото
📋 Бронирование с вводом гостей
💵 Расчёт стоимости с помощью JavaScript
💳 Симуляция оплаты
📄 Генерация PDF-документов:
Акт заселения
Акт выселения
Чек бронирования
🧾 Страница оплат
📊 Админ-панель с бронированиями и фильтрацией
📎 Стек технологий

PHP (без фреймворков)
MySQL
Bootstrap 5
FPDF для генерации PDF
Vanilla JavaScript (для динамики)
HTML/CSS
🛠 Требования

PHP 8.0+
MySQL 5.7+
Apache/Nginx
Локальный сервер: MAMP / XAMPP / OpenServer
📩 Связь

Если возникли вопросы — создавайте issue или пишите прямо в Telegram 😄

© <?= date('Y') ?> real_estate | Сделано с ❤️