-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: localhost:8889
-- Время создания: Мар 31 2025 г., 00:10
-- Версия сервера: 5.7.39
-- Версия PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `real_estate_rentals`
--

-- --------------------------------------------------------

--
-- Структура таблицы `action_logs`
--

CREATE TABLE `action_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `description` text,
  `action_time` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `action_logs`
--

INSERT INTO `action_logs` (`id`, `user_id`, `action_type`, `description`, `action_time`) VALUES
(1, 1, 'create_property', 'Создан новый объект недвижимости с ID 1', '2025-03-31 00:38:56'),
(2, 1, 'update_booking', 'Обновлен статус бронирования с ID 2 на \"confirmed\"', '2025-03-31 00:38:56');

-- --------------------------------------------------------

--
-- Структура таблицы `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `property_id`, `check_in`, `check_out`, `total_price`, `status`, `created_at`) VALUES
(1, 2, 1, '2025-05-10', '2025-05-15', '17500.00', 'confirmed', '2025-03-31 00:38:56'),
(2, 2, 2, '2025-06-20', '2025-06-25', '25000.00', 'pending', '2025-03-31 00:38:56'),
(3, 3, 1, '2025-03-31', '2025-04-01', '3500.00', 'pending', '2025-03-31 01:55:37'),
(4, 3, 1, '2025-03-31', '2025-04-01', '3500.00', 'confirmed', '2025-03-31 01:57:06'),
(5, 3, 1, '2025-03-31', '2025-04-02', '7000.00', 'confirmed', '2025-03-31 02:57:34');

-- --------------------------------------------------------

--
-- Структура таблицы `booking_guests`
--

CREATE TABLE `booking_guests` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `booking_guests`
--

INSERT INTO `booking_guests` (`id`, `booking_id`, `full_name`, `check_in`, `check_out`) VALUES
(1, 3, 'Гость 1', '2025-03-31', '2025-04-01'),
(2, 3, 'Гость 2', '2025-03-31', '2025-04-01'),
(3, 4, 'Гость 1', '2025-03-31', '2025-04-01'),
(4, 4, 'Гость 2', '2025-03-31', '2025-04-01'),
(5, 5, 'Лада Проститутка', '2025-03-31', '2025-04-02'),
(6, 5, 'Лада Красотка', '2025-03-31', '2025-04-02');

-- --------------------------------------------------------

--
-- Структура таблицы `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `city` varchar(100) NOT NULL,
  `district` varchar(100) DEFAULT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `locations`
--

INSERT INTO `locations` (`id`, `city`, `district`, `address`) VALUES
(1, 'Сочи', 'Центральный район', 'ул. Виноградная, д. 207'),
(2, 'Сочи', 'Микрорайон Мамайка', 'Крымская ул., д. 7Б'),
(3, 'Сочи', 'Микрорайон Бытха', 'Ясногорская ул., д. 16/6к6'),
(4, 'Сочи', 'Микрорайон Новый Сочи', 'ул. Искры, д. 88к3');

-- --------------------------------------------------------

--
-- Структура таблицы `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `payment_method` enum('card','cash','bank_transfer') NOT NULL,
  `status` enum('pending','paid','failed') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `amount`, `payment_date`, `payment_method`, `status`) VALUES
(1, 1, '17500.00', '2025-05-01 12:00:00', 'card', 'paid'),
(2, 2, '25000.00', '2025-06-10 14:30:00', 'bank_transfer', 'pending'),
(3, 4, '3500.00', '2025-03-31 01:57:41', 'card', 'paid'),
(4, 5, '7000.00', '2025-03-31 02:57:36', 'card', 'paid');

-- --------------------------------------------------------

--
-- Структура таблицы `properties`
--

CREATE TABLE `properties` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `price_per_day` decimal(10,2) NOT NULL,
  `max_guests` int(11) DEFAULT '1',
  `available_from` date DEFAULT NULL,
  `available_to` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `properties`
--

INSERT INTO `properties` (`id`, `owner_id`, `category_id`, `location_id`, `title`, `description`, `price_per_day`, `max_guests`, `available_from`, `available_to`, `created_at`, `is_active`) VALUES
(1, 2, 1, 1, 'Уютная квартира в центре Сочи', 'Квартира с евроремонтом, рядом с морем.', '3500.00', 4, '2025-04-01', '2025-12-31', '2025-03-31 00:38:56', 1),
(2, 2, 2, 2, 'Апартаменты с видом на море', 'Современные апартаменты с панорамным видом.', '5000.00', 2, '2025-04-01', '2025-12-31', '2025-03-31 00:38:56', 1),
(3, 2, 4, 3, 'Студия в районе Бытха', 'Студия с новым ремонтом и всей необходимой техникой.', '3000.00', 2, '2025-04-01', '2025-12-31', '2025-03-31 00:38:56', 1),
(4, 2, 1, 4, 'Квартира в Новом Сочи', 'Просторная квартира с балконом и видом на горы.', '4000.00', 4, '2025-04-01', '2025-12-31', '2025-03-31 00:38:56', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `property_categories`
--

CREATE TABLE `property_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `property_categories`
--

INSERT INTO `property_categories` (`id`, `name`) VALUES
(2, 'Апартаменты'),
(3, 'Дом'),
(1, 'Квартира'),
(4, 'Студия');

-- --------------------------------------------------------

--
-- Структура таблицы `property_photos`
--

CREATE TABLE `property_photos` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `photo_url` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `property_photos`
--

INSERT INTO `property_photos` (`id`, `property_id`, `photo_url`, `is_main`) VALUES
(1, 1, 'photos/2024-08-20-li-1200x850-b22.jpg', 1),
(2, 1, 'photos/2cccb7032aaeca03917905c4d7255df6.jpg', 0),
(3, 2, 'photos/285820269033120.jpg', 1),
(4, 3, 'photos/c4767644163fc60a2575a9d17f1b2623.jpg', 1),
(5, 4, 'photos/novostroyki-elitnie-01.jpg', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `rental_offers`
--

CREATE TABLE `rental_offers` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `special_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `rental_offers`
--

INSERT INTO `rental_offers` (`id`, `property_id`, `start_date`, `end_date`, `special_price`) VALUES
(1, 1, '2025-06-01', '2025-06-30', '3200.00'),
(2, 2, '2025-07-01', '2025-07-15', '4500.00'),
(3, 3, '2025-08-01', '2025-08-31', '2800.00');

-- --------------------------------------------------------

--
-- Структура таблицы `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `reviews`
--

INSERT INTO `reviews` (`id`, `property_id`, `user_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 5, 'Отличная квартира, все понравилось!', '2025-03-31 00:38:56'),
(2, 2, 2, 4, 'Хорошие апартаменты, но были небольшие проблемы с Wi-Fi.', '2025-03-31 00:38:56');

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'user');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `password_hash`, `phone`, `created_at`) VALUES
(1, 1, 'Администратор', 'admin@example.com', '$2y$10$zxdzBHKAsXDtFUXC99Ddgus.uqOwE/j1awUxmAS5zfU90v8GXJqgK', '+7 900 000-00-00', '2025-03-31 00:38:56'),
(2, 2, 'Иван Иванов', 'ivanov@example.com', '$2y$10$F0JcIk9qf8vQtvvD59YiU.yqG7.1PoXkA3uPfM2ZxQ/nMNgEJxNtu', '+7 901 123-45-67', '2025-03-31 00:38:56'),
(3, 1, 'Агзамова Марьям', 'test@email.com', '$2y$10$/qgnelAUKXrN/9xm5eA/W.YFWDm38wsOl67Tq79QtwqCkRc00oZ/K', NULL, '2025-03-31 01:33:43');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `action_logs`
--
ALTER TABLE `action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Индексы таблицы `booking_guests`
--
ALTER TABLE `booking_guests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Индексы таблицы `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Индексы таблицы `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`id`),
  ADD KEY `owner_id` (`owner_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Индексы таблицы `property_categories`
--
ALTER TABLE `property_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `property_photos`
--
ALTER TABLE `property_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_photos_ibfk_1` (`property_id`);

--
-- Индексы таблицы `rental_offers`
--
ALTER TABLE `rental_offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`);

--
-- Индексы таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `action_logs`
--
ALTER TABLE `action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `booking_guests`
--
ALTER TABLE `booking_guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `properties`
--
ALTER TABLE `properties`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `property_categories`
--
ALTER TABLE `property_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `property_photos`
--
ALTER TABLE `property_photos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `rental_offers`
--
ALTER TABLE `rental_offers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `action_logs`
--
ALTER TABLE `action_logs`
  ADD CONSTRAINT `action_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Ограничения внешнего ключа таблицы `booking_guests`
--
ALTER TABLE `booking_guests`
  ADD CONSTRAINT `booking_guests_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);

--
-- Ограничения внешнего ключа таблицы `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `properties_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `property_categories` (`id`),
  ADD CONSTRAINT `properties_ibfk_3` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Ограничения внешнего ключа таблицы `property_photos`
--
ALTER TABLE `property_photos`
  ADD CONSTRAINT `property_photos_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `rental_offers`
--
ALTER TABLE `rental_offers`
  ADD CONSTRAINT `rental_offers_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`);

--
-- Ограничения внешнего ключа таблицы `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
