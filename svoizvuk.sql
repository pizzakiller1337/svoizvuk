-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: MySQL-8.0
-- Время создания: Июн 07 2026 г., 14:50
-- Версия сервера: 8.0.35
-- Версия PHP: 8.1.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `svoizvuk`
--

-- --------------------------------------------------------

--
-- Структура таблицы `categories`
--

CREATE TABLE `categories` (
  `category_id` int NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(11, 'classical'),
(10, 'folk'),
(8, 'hip-hop'),
(6, 'post-punk'),
(7, 'shoegaze'),
(9, 'soul/funk'),
(4, 'Инди/Альтернатива'),
(1, 'Интересный выбор'),
(2, 'Новинки'),
(3, 'Популярное'),
(5, 'Электро');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `order_number` varchar(40) NOT NULL,
  `user_id` int DEFAULT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(40) NOT NULL,
  `delivery_method` enum('courier','post','pickup') NOT NULL DEFAULT 'courier',
  `delivery_city` varchar(150) DEFAULT NULL,
  `delivery_zip` varchar(20) DEFAULT NULL,
  `delivery_address` varchar(500) DEFAULT NULL,
  `delivery_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `comment` text,
  `payment_method` enum('card','sbp','cash') NOT NULL DEFAULT 'card',
  `payment_card_last4` varchar(4) DEFAULT NULL,
  `payment_status` enum('pending','paid','failed') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('new','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'new',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`order_id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_method`, `delivery_city`, `delivery_zip`, `delivery_address`, `delivery_cost`, `comment`, `payment_method`, `payment_card_last4`, `payment_status`, `subtotal`, `total`, `status`, `created_at`, `updated_at`) VALUES
(1, 'SZ-20260508-80A37', NULL, 'sonic2012', 'vitalikwert25@gmail.com', '+7 (937) 952-95-99', 'post', '123', '123', '123', 0.00, '123', 'cash', NULL, 'pending', 4300.00, 4300.00, 'delivered', '2026-05-08 22:22:43', '2026-05-08 22:23:28'),
(2, 'SZ-20260516-6F87B', 5, 'admin', 'admin@svoizvuk.ru', '+7 (937) 952-95-99', 'pickup', '', '', '', 0.00, '', 'cash', NULL, 'pending', 3600.00, 3600.00, 'delivered', '2026-05-16 12:37:22', '2026-05-27 19:22:53'),
(3, 'SZ-20260516-E9296', 5, 'admin', 'admin@svoizvuk.ru', '+7 (937) 952-95-99', 'pickup', '', '', '', 0.00, '', 'cash', NULL, 'pending', 2900.00, 2900.00, 'shipped', '2026-05-16 12:58:26', '2026-05-27 19:23:00'),
(4, 'SZ-20260528-7D754', NULL, 'тест', 'vitaly.takmakov@yandex.ru', '+7 (937) 952-95-99', 'pickup', '', '', '', 0.00, '', 'cash', NULL, 'pending', 25900.00, 25900.00, 'new', '2026-05-28 21:55:39', '2026-05-28 21:55:39'),
(5, 'SZ-20260528-2DBC2', NULL, 'Vitaliy Takmakov', 'vitaly.takmakov@yandex.ru', '+7 (937) 952-95-99', 'pickup', '', '', '', 0.00, '', 'cash', NULL, 'pending', 2900.00, 2900.00, 'new', '2026-05-28 22:05:35', '2026-05-28 22:05:35'),
(6, 'SZ-20260607-E5219', NULL, 'Vitaliy Takmakov', 'vitaly.takmakov@yandex.ru', '+7 (937) 952-95-99', 'pickup', '', '', '', 0.00, '', 'cash', NULL, 'pending', 3600.00, 3600.00, 'new', '2026-06-07 14:34:02', '2026-06-07 14:34:02'),
(7, 'SZ-20260607-F4257', NULL, 'Vitaliy Takmakov', 'vitaly.takmakov@yandex.ru', '+7 (937) 952-95-99', 'pickup', '', '', '', 0.00, '', 'sbp', NULL, 'paid', 2900.00, 2900.00, 'new', '2026-06-07 14:43:45', '2026-06-07 14:43:45');

-- --------------------------------------------------------

--
-- Структура таблицы `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `title`, `artist`, `image_url`, `price`, `quantity`, `subtotal`) VALUES
(1, 1, 4, 'Grace', 'Jeff Buckley', '/assets/images/jeff_buckley.jpg', 4300.00, 1, 4300.00),
(2, 2, 5, 'Slowdive', 'Slowdive', '/assets/images/slowdive.jpg', 3600.00, 1, 3600.00),
(3, 3, 3, 'Daydream Nation', 'Sonic Youth', '/assets/images/sonic_youth.jpg', 2900.00, 1, 2900.00),
(4, 4, 4, 'Grace', 'Jeff Buckley', '/assets/images/jeff_buckley.jpg', 4300.00, 4, 17200.00),
(5, 4, 15, 'Souvlaki', 'Slowdive ', '/assets/images/souvlaki.jpg', 2900.00, 3, 8700.00),
(6, 5, 15, 'Souvlaki', 'Slowdive ', '/assets/images/souvlaki.jpg', 2900.00, 1, 2900.00),
(7, 6, 5, 'Slowdive', 'Slowdive', '/assets/images/slowdive.jpg', 3600.00, 1, 3600.00),
(8, 7, 3, 'Daydream Nation', 'Sonic Youth', '/assets/images/sonic_youth.jpg', 2900.00, 1, 2900.00);

-- --------------------------------------------------------

--
-- Структура таблицы `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `year` int NOT NULL,
  `label` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `catalog_number` varchar(50) DEFAULT NULL,
  `format` text NOT NULL,
  `price` int NOT NULL,
  `description` text NOT NULL,
  `image_url` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `products`
--

INSERT INTO `products` (`product_id`, `category_id`, `title`, `artist`, `year`, `label`, `catalog_number`, `format`, `price`, `description`, `image_url`) VALUES
(1, 1, 'American football', 'American football', 1999, 'Polyvinyl', '1', 'LP', 2580, 'Группа American Football слила инди-рок и эмо в одно целое. Братья Кинселла сменили свои шутки в стиле «Cap`n jazz» на группу, полную открытых чувств, с нежно переливающимися гитарами и тихими духовыми инструментами. Их дебютный альбом — абсолютный фаворит жанра, любимый многими за откровенное нытье. Идеально подходит для взрослых, которые все еще чувствуют себя подростками.\n', '/assets/images/american_football.png'),
(2, 1, 'loveless', 'my bloody valentine', 1989, 'Creation Records', '2', 'LP', 3500, '', '/assets/images/mbv.png'),
(3, 1, 'Daydream Nation', 'Sonic Youth', 1988, 'Enigma Records', '3', 'LP', 2900, '', '/assets/images/sonic_youth.jpg'),
(4, 1, 'Grace', 'Jeff Buckley', 1994, 'Columbia Records', '4', 'LP', 4300, '', '/assets/images/jeff_buckley.jpg'),
(5, 1, 'Slowdive', 'Slowdive', 2017, 'Dead Oceans', '5', 'LP', 3600, '', '/assets/images/slowdive.jpg'),
(6, 1, 'The Glow Pt. 2 ', 'The Microphones', 2001, 'K Records', '6', 'LP', 2700, '', '/assets/images/the_glow.webp'),
(7, 2, 'Private Music', 'Deftones', 2025, 'Reprise Records', '7', 'LP', 3700, 'Группа Deftones давно бросает вызов жанровым рамкам, создав уникальное звучание, сочетающее в себе ярость и атмосферу. За девять альбомов они сформировали неповторимый стиль — брутальный, но в то же время мечтательный — постоянно развивающийся, но при этом безошибочно узнаваемый.\n\nТеперь они представляют «Private Music» — новую, целенаправленную и масштабную главу в истории группы. Воссоединившись с продюсером Ником Раскулинецом, работавшим над альбомами Diamond Eyes (2010) и Koi No Yokan (2012), группа выпускает 11-трековый альбом, который одновременно кажется лаконичным и амбициозным. Помимо основных участников Чино Морено, Стивена Карпентера, Эйба Каннингема и Фрэнка Дельгадо, в записи также принял участие гастролирующий басист Фред Саблан.\n\nРазмышляя о красоте и опасностях природы, внутренней стойкости и потусторонних путешествиях, «Private Music» сочетает в себе захватывающую психоделию с сокрушительной интенсивностью. Это яркое заявление от группы, которая продолжает двигаться вперед, спустя десятилетия своей влиятельной карьеры.\n\n', '/assets/images/deftones.jpg'),
(8, 4, 'Kid A', 'Radiohead', 2000, 'Parlophone', '8', 'LP', 2900, 'Kid A по-прежнему остается самым сложным альбомом Radiohead и самым глубоким погружением группы в электронные текстуры, но это пошло им только на пользу. Группа переняла звучание таких исполнителей, как Autechre, Björk, Aphex Twin, Plaid, Boards of Canada и даже Alice Coltrane и Charles Mingus, применив эти эклектичные влияния к своему собственному звучанию. Это совершенно другой альбом по сравнению с OK Computer, но очень новаторский и важный, занимающий высокие места в списках лучших альбомов.\n', '/assets/images/kidA.jpg'),
(9, 4, 'In Rainbows', 'Radiohead ', 2007, 'XL Recordings', '9', 'LP', 3500, 'Седьмой студийный альбом Radiohead, In Rainbows, вероятно, наиболее известен своим новаторским цифровым релизом по принципу «плати сколько хочешь», предшествовавшим выходу физических изданий. Это решение вызвало неоднозначную реакцию, но имело огромное значение для будущего музыкальной индустрии. Музыка стала более мягкой и пасторальной, чем обычно: сочетание арт-рока и звуков, напоминающих вой кошки, пытающейся согреться. Теперь альбом доступен на виниле и CD, и мы говорим вам, сколько вы за него платите, так что не спорьте.\n', '/assets/images/inRainbows.jpg'),
(10, 3, 'OK Computer', 'Radiohead', 1997, 'XL Recordings', '10', 'LP', 4100, 'OK Computer — третий альбом Radiohead. Выпущенный в 1997 году, после их второго альбома The Bends, он изначально задумывался как нечто грандиозное, но они справились с задачей на отлично! Классика 1990-х, включающая синглы «Paranoid Android», «Karma Police» и «Airbag», считается одним из величайших альбомов всех времен.\n', '/assets/images/OKComputer.jpg'),
(11, 4, 'In Utero', 'Nirvana', 1993, 'Universal', '11', 'LP', 3800, 'Альбом Nirvana «In Utero», ставший знаменитым продолжением «Nevermind» и отличающийся более едким и резким звучанием, отличается эмоциональной и звуковой насыщенностью, особенно для пластинки, занявшей первое место в американских чартах. Включает в себя такие треки, как «Heart-Shaped Box», «Rape Me» и заглавную композицию, которая, по сути, достаточно поп-ориентирована, чтобы завлечь слушателей в агрессивную глубину альбома. Переиздан на виниле лейблом Back To Black.\n', '/assets/images/InUtero.jpg'),
(12, 5, 'Symbol', 'Susumu Yokota', 2004, 'Lo Recordings', '12', 'LP', 1900, 'Впервые на виниле благодаря лейблу Lo Recordings выходит альбом 2004 года покойного японского пионера электронной музыки Сусуму Йокоты. Возможно, это его лучшая работа, в которой искусно сочетаются сэмплы таких исполнителей, как Джон Кейдж, Мередит Монк и Дебюсси, создавая лоскутное одеяло чистой, экстатической музыкальной красоты.\n', '/assets/images/Symbol.jpg'),
(13, 3, 'Seventeen Seconds', 'The Cure', 1980, 'Universal', '13', 'LP', 3100, '«Seventeen Seconds» — второй альбом The Cure, первоначально выпущенный в 1980 году. Он оказался слишком мрачным для басиста Майкла Демпси, который покинул группу после прослушивания демо-записей Роберта Смита. В конце концов Смит пошел своим собственным веселым/несчастным путем, и альбом является прекрасным примером их тонкого прото-готического стиля. Он содержит один из лучших моментов группы — зловещий сингл «A Forest», который иллюстрирует мрачную природу материала на альбоме.\n', '/assets/images/seventeenSeconds.jpg'),
(14, 3, 'Either/Or', 'Elliott Smith', 1997, 'Universal', '14', 'LP', 4200, 'Третий сольный альбом Эллиотта и первый после распада Heatmiser. Важный релиз в его дискографии, Either/Or сохраняет лоу-фай интимность предыдущих работ Roman Candle и Elliott Smith, одновременно повышая сложность и амбициозность аранжировок. Miss Misery даже была номинирована на «Оскар»! Переиздан на 180-граммовой пластинке на Universal. Грустно!\n', '/assets/images/elliottsmith.jpg'),
(15, 3, 'Souvlaki', 'Slowdive ', 1993, 'Sony', '15', 'LP', 2900, 'Долгожданное переиздание второго альбома Slowdive, Souvlaki, вышедшего в 1993 году. Этот альбом, признанный их лучшим, впечатляет: здесь собраны классические хиты, которые заставят ваши глаза затуманиться, плечи опуститься, а всё тело погрузиться под одеяло, пока их блаженное, реверберирующее звучание, словно воздушный шар, мягко унесёт вас прочь. Лучше всего слушать рано утром, в темноте, после выпивки.\n', '/assets/images/souvlaki.jpg'),
(16, 3, 'Velvet Underground ', 'The Velvet Underground', 1969, 'UMC', '16', 'LP', 4200, 'Что еще можно сказать об этой классике? Пластинка, которая, по сути, положила начало всему, что стало инди, альтернативой, дрим-попом, панком, нью-вейвом и так далее. И это от группы, которую отчасти продюсировал свенгали Энди Уорхол, добавивший в состав чувственную певицу Нико и внесший множество предложений. Несмотря на это, оригинальность песен Лу Рида, а также его экспериментальный и разнообразный подход к созданию музыки, остаются актуальными.\n', '/assets/images/velvetUnderground.jpg'),
(17, 2, 'Xiu Mutha Xiu: Vol. 1', 'Xiu Xiu', 2026, 'Polyvinyl Record Co.\r\n', '17', 'LP', 4500, 'Xiu Xiu present Xiu Mutha Xiu Vol. 1, a collection of covers that reflect the songs which have shaped their work as listeners and songwriters. Curated from the band’s Bandcamp subscription series, the album gathers tracks that were previously available only to subscribers, now issued together for wider listening.\n\nJamie Stewart and Angela Seo approach each song with the band’s stark, idiosyncratic style, reshaping works such as ‘Psycho Killer’ by Talking Heads, ‘In Dreams’ by Roy Orbison, ‘Some Things Last A Long Time’ by Daniel Johnston, and ‘I Put a Spell on You’ by Screamin’ Jay Hawkins. Rather than straightforward tributes, these recordings function as both a personal acknowledgement of formative influences and an exploration of each song’s emotional and musical core.\n\n', '/assets/images/XiuXiu.jpg'),
(18, 5, 'Untrue', 'Burial ', 2007, 'Hyperdub', '18', 'CD', 2200, '', '/assets/images/Burial.jpg'),
(19, 5, 'Discovery', 'Daft Punk', 2001, 'Parlophone', '19', '2LP', 4900, '', '/assets/images/DaftPunk.jpg\r\n'),
(20, 5, 'Ambient 1', 'Brian Eno', 1978, 'Virgin', '20', 'LP', 3600, '', '/assets/images/BrianEno.jpg'),
(21, 3, 'The Rise and Fall ', 'David Bowie', 1972, 'Parlophone ', '21', 'LP', 4100, '', '/assets/images/DavidBowie.jpg'),
(22, 6, 'Unknown Pleasures', 'Joy Division', 1979, 'Factory', '0', 'LP', 3200, 'Дебютный альбом манчестерской группы — мрачное, индустриальное звучание и узнаваемая обложка с пульсарами CP 1919. Манифест целого направления.', '/assets/images/1779888964_6a16f34426c31.jpg'),
(23, 6, 'Disintegration', 'The Cure', 1989, 'Fiction Records', '0', 'LP', 3500, 'Восьмой студийный альбом группы — двойной винил, концентрат меланхолии и густой синтезаторной атмосферы. Один из главных готик-роковых релизов конца 80-х.', '/assets/images/1779887868_6a16eefce18e3.jpg'),
(24, 6, 'Remain in Light', 'Talking Heads', 1980, 'Sire', '0', 'LP', 3300, 'Четвёртый альбом группы, записанный с Брайаном Ино. Сложные ритмы афробита, петли и манифест нью-вейва — пластинка, изменившая представление о поп-музыке.', '/assets/images/1779887838_6a16eede91475.jpg'),
(27, 7, 'Heaven or Las Vegas', 'Cocteau Twins', 1990, '4AD', '0', 'LP', 3500, 'Шестой студийный альбом шотландского трио — самая доступная и при этом самая магическая запись группы. Эталон дрим-попа.', '/assets/images/1779887808_6a16eec07f9d5.jpg'),
(28, 8, 'Madvillainy', 'Madvillain', 2004, 'Stones Throw', '0', '2xLP', 3900, 'Союз MF DOOM и Madlib. 22 коротких трека, петли, сэмплы и плотные рифмы — самый влиятельный андеграундный хип-хоп альбом нулевых.', '/assets/images/1779887775_6a16ee9f33def.jpg'),
(29, 8, 'The Low End Theory', 'A Tribe Called Quest', 1991, 'Jive', '1418', 'LP', 3500, 'Второй альбом ATCQ — союз джазовых сэмплов и расслабленного нью-йоркского хип-хопа. Альбом, определивший звучание целой эпохи.', '/assets/images/1779887730_6a16ee72338a5.jpg'),
(30, 8, 'To Pimp a Butterfly', 'Kendrick Lamar', 2015, 'Top Dawg / Aftermath', '0', '2xLP', 4100, 'Третий альбом Кендрика — фанк, джаз, спокен-ворд и политика. Запись, после которой о хип-хопе нельзя говорить как раньше.', '/assets/images/1779887684_6a16ee44304ce.jpg'),
(31, 9, 'What\'s Going On', 'Marvin Gaye', 1971, 'Tamla', '0', 'LP', 3400, 'Концептуальный альбом Марвина Гэя о войне, бедности и экологии — момент, когда соул-музыка стала говорить о политике в полный голос.', '/assets/images/1779887632_6a16ee10492af.jpg'),
(32, 9, 'Super Fly', 'Curtis Mayfield', 1972, 'Curtom', '0', 'LP', 3500, 'Саундтрек к блэксплотейшн-фильму, ставший более влиятельным, чем сам фильм. Тёмный, кинематографичный соул-фанк с социальной критикой внутри.', '/assets/images/1779887586_6a16ede2a1d69.jpg'),
(33, 9, 'Voodoo', 'D\'Angelo', 2000, 'Virgin', '7243', '2xLP', 4300, 'Второй альбом Д\'Анджело — нео-соул, записанный с The Soulquarians. Плотный, физический грув, на который ушло пять лет работы.', '/assets/images/1779887541_6a16edb5762d5.jpg'),
(34, 10, 'Pink Moon', 'Nick Drake', 1972, 'Island Records', '0', 'LP', 3600, 'Третий и последний альбом Ника Дрейка — записан за две ночи, только голос и гитара. Тихая, ломкая красота, оцененная только после смерти автора.', '/assets/images/1779887468_6a16ed6cadc60.jpg'),
(35, 10, 'For Emma, Forever Ago', 'Bon Iver', 2007, 'Jagjaguwar', '0', 'LP', 3300, 'Дебютный альбом Джастина Вернона — записан в одиночестве зимой в хижине в Висконсине. Одинокий, многослойный фолк, ставший культовым.', '/assets/images/1779887426_6a16ed4250835.jpg'),
(36, 10, 'Carrie & Lowell', 'Sufjan Stevens', 2015, 'Asthmatic Kitty', '0', 'LP', 3400, 'Седьмой альбом Суфьяна Стивенса — самый личный и самый тихий. Воспоминания о матери и отчиме, минимум аранжировок, максимум интимности.', '/assets/images/1779887379_6a16ed13aa914.jpg'),
(37, 11, 'The Blue Notebooks', 'Max Richter', 2004, 'FatCat Records', '130701', 'LP', 3500, 'Второй альбом Макса Рихтера — неоклассика, отрывки из Кафки в исполнении Тильды Суинтон. Пластинка о войне в Ираке, ставшая универсальным антивоенным произведением.', '/assets/images/1779887345_6a16ecf113702.jpg'),
(38, 11, 'All Melody', 'Nils Frahm', 2018, 'Erased Tapes', '0', '2xLP', 3800, 'Седьмой студийный альбом Нильса Фрама — записан в построенной им самим студии Saal 3. Фортепиано, синтезаторы, орган и хор сплавляются в одно полотно.', '/assets/images/1779887291_6a16ecbb34d53.jpg');

-- --------------------------------------------------------

--
-- Структура таблицы `tracklist`
--

CREATE TABLE `tracklist` (
  `track_id` int NOT NULL,
  `product_id` int NOT NULL,
  `track_number` int NOT NULL,
  `title` text NOT NULL,
  `audio_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `tracklist`
--

INSERT INTO `tracklist` (`track_id`, `product_id`, `track_number`, `title`, `audio_url`) VALUES
(1, 1, 1, 'Never Meant', '\\assets\\audio\\American Football\\Never Meant.mp3'),
(2, 1, 2, 'The Summer Ends', '\\assets\\audio\\American Football\\The Summer Ends.mp3'),
(3, 1, 3, 'Honestly?', '\\assets\\audio\\American Football\\Honestly_.mp3'),
(4, 1, 4, 'For Sure', '\\assets\\audio\\American Football\\For Sure.mp3'),
(5, 1, 5, 'You Know I Should Be Leaving Soon', '\\assets\\audio\\American Football\\You Know I Should Be Leaving Soon.mp3'),
(6, 1, 6, 'But the Regrets Are Killing Me', '\\assets\\audio\\American Football\\But the Regrets Are Killing Me.mp3'),
(7, 1, 7, 'I ll See You When We re Both Not So Emotional', '\\assets\\audio\\American Football\\I\'ll See You When We\'re Both Not So Emotional.mp3'),
(8, 1, 8, 'Stay Home', '\\assets\\audio\\American Football\\Stay Home.mp3'),
(9, 1, 9, 'The One with the Wurlitzer', '\\assets\\audio\\American Football\\The One With The Wurlitzer.mp3'),
(11, 2, 1, 'blown a wish', '/assets/audio/2/001_blown_a_wish.mp3'),
(12, 2, 2, 'come in alone', '/assets/audio/2/002_come_in_alone.mp3'),
(13, 2, 3, 'i only said', '/assets/audio/2/003_i_only_said.mp3'),
(14, 2, 4, 'loomer', '/assets/audio/2/004_loomer.mp3'),
(15, 2, 5, 'only shallow', '/assets/audio/2/005_only_shallow.mp3'),
(16, 2, 6, 'sometimes', '/assets/audio/2/006_sometimes.mp3'),
(17, 2, 7, 'soon', '/assets/audio/2/007_soon.mp3'),
(18, 2, 8, 'to here knows when', '/assets/audio/2/008_to_here_knows_when.mp3'),
(19, 2, 9, 'touched', '/assets/audio/2/009_touched.mp3'),
(20, 2, 10, 'what you want', '/assets/audio/2/010_what_you_want.mp3'),
(21, 2, 11, 'when you sleep', '/assets/audio/2/011_when_you_sleep.mp3'),
(22, 3, 1, '\'Cross the Breeze (Album Version)', '/assets/audio/3/001__Cross_the_Breeze__Album_Version_.mp3'),
(23, 3, 2, 'A) the Wonder (Album Version)', '/assets/audio/3/002_A__the_Wonder__Album_Version_.mp3'),
(24, 3, 3, 'B) Hyperstation (Album Version)', '/assets/audio/3/003_B__Hyperstation__Album_Version_.mp3'),
(25, 3, 4, 'Candle', '/assets/audio/3/004_Candle.mp3'),
(26, 3, 5, 'Eric\'s Trip (Album Version)', '/assets/audio/3/005_Eric_s_Trip__Album_Version_.mp3'),
(27, 3, 6, 'Hey Joni (Album Version)', '/assets/audio/3/006_Hey_Joni__Album_Version_.mp3'),
(28, 3, 7, 'Kissability (Album Version)', '/assets/audio/3/007_Kissability__Album_Version_.mp3'),
(29, 3, 8, 'Providence', '/assets/audio/3/008_Providence.mp3'),
(30, 3, 9, 'Rain King (Album Version)', '/assets/audio/3/009_Rain_King__Album_Version_.mp3'),
(31, 3, 10, 'Silver Rocket (Album Version)', '/assets/audio/3/010_Silver_Rocket__Album_Version_.mp3'),
(32, 3, 11, 'Teen Age Riot (Album Version)', '/assets/audio/3/011_Teen_Age_Riot__Album_Version_.mp3'),
(33, 3, 12, 'The Sprawl (Album Version)', '/assets/audio/3/012_The_Sprawl__Album_Version_.mp3'),
(34, 3, 13, 'Total Trash (Album Version)', '/assets/audio/3/013_Total_Trash__Album_Version_.mp3'),
(35, 3, 14, 'Z) Eliminator, Jr.', '/assets/audio/3/014_Z__Eliminator__Jr_.mp3');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `user_id` int NOT NULL,
  `username` text NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `phone` int NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `created_at` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `phone`, `role`, `password`, `created_at`) VALUES
(1, 'ggg', '1234@123', 123, 'user', '123', '2026-01-16'),
(2, '1234', '12345@123', 123, 'user', '123', '2026-01-16'),
(3, '123', '123456@123', 123, 'user', '123', '2026-01-16'),
(4, '123', '123@123', 123, 'user', '123', '2026-01-17'),
(5, 'admin', 'admin@svoizvuk.ru', 1111, 'admin', '1111', '2026-02-12');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `uniq_category_name` (`name`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`),
  ADD KEY `idx_orders_user` (`user_id`);

--
-- Индексы таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_items_order` (`order_id`);

--
-- Индексы таблицы `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `uniq_title_artist` (`title`,`artist`),
  ADD KEY `category_id` (`category_id`) USING BTREE;

--
-- Индексы таблицы `tracklist`
--
ALTER TABLE `tracklist`
  ADD PRIMARY KEY (`track_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=220;

--
-- AUTO_INCREMENT для таблицы `tracklist`
--
ALTER TABLE `tracklist`
  MODIFY `track_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Ограничения внешнего ключа таблицы `tracklist`
--
ALTER TABLE `tracklist`
  ADD CONSTRAINT `tracklist_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
