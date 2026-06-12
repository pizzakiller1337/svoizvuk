-- MySQL dump 10.13  Distrib 8.0.25-15, for Linux (x86_64)
--
-- Host: localhost    Database: u3530164_svoizvuk
-- ------------------------------------------------------
-- Server version	8.0.25-15

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*!50717 SELECT COUNT(*) INTO @rocksdb_has_p_s_session_variables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'performance_schema' AND TABLE_NAME = 'session_variables' */;
/*!50717 SET @rocksdb_get_is_supported = IF (@rocksdb_has_p_s_session_variables, 'SELECT COUNT(*) INTO @rocksdb_is_supported FROM performance_schema.session_variables WHERE VARIABLE_NAME=\'rocksdb_bulk_load\'', 'SELECT 0') */;
/*!50717 PREPARE s FROM @rocksdb_get_is_supported */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;
/*!50717 SET @rocksdb_enable_bulk_load = IF (@rocksdb_is_supported, 'SET SESSION rocksdb_bulk_load = 1', 'SET @rocksdb_dummy_bulk_load = 0') */;
/*!50717 PREPARE s FROM @rocksdb_enable_bulk_load */;
/*!50717 EXECUTE s */;
/*!50717 DEALLOCATE PREPARE s */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`category_id`),
  UNIQUE KEY `uniq_category_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (4,'Инди/Альтернатива'),(1,'Интересный выбор'),(11,'Классика'),(2,'Новинки'),(3,'Популярное'),(6,'Пост-панк'),(9,'Соул/фанк'),(10,'Фолк'),(8,'Хип-хоп'),(7,'Шугейз'),(5,'Электро');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `item_id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `product_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `idx_items_order` (`order_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES (1,1,4,'Grace','Jeff Buckley','/assets/images/jeff_buckley.jpg',4300.00,1,4300.00),(2,2,5,'Slowdive','Slowdive','/assets/images/slowdive.jpg',3600.00,1,3600.00),(3,3,3,'Daydream Nation','Sonic Youth','/assets/images/sonic_youth.jpg',2900.00,1,2900.00),(4,4,4,'Grace','Jeff Buckley','/assets/images/jeff_buckley.jpg',4300.00,4,17200.00),(5,4,15,'Souvlaki','Slowdive ','/assets/images/souvlaki.jpg',2900.00,3,8700.00),(6,5,15,'Souvlaki','Slowdive ','/assets/images/souvlaki.jpg',2900.00,1,2900.00),(7,6,4,'Grace','Jeff Buckley','/assets/images/jeff_buckley.jpg',4300.00,1,4300.00),(8,7,3,'Daydream Nation','Sonic Youth','/assets/images/sonic_youth.jpg',2900.00,2,5800.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `order_id` int NOT NULL AUTO_INCREMENT,
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
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `idx_orders_status` (`status`),
  KEY `idx_orders_created` (`created_at`),
  KEY `idx_orders_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES (1,'SZ-20260508-80A37',NULL,'sonic2012','vitalikwert25@gmail.com','+7 (937) 952-95-99','post','123','123','123',0.00,'123','cash',NULL,'pending',4300.00,4300.00,'delivered','2026-05-08 22:22:43','2026-05-08 22:23:28'),(2,'SZ-20260516-6F87B',5,'admin','admin@svoizvuk.ru','+7 (937) 952-95-99','pickup','','','',0.00,'','cash',NULL,'pending',3600.00,3600.00,'delivered','2026-05-16 12:37:22','2026-05-27 19:22:53'),(3,'SZ-20260516-E9296',5,'admin','admin@svoizvuk.ru','+7 (937) 952-95-99','pickup','','','',0.00,'','cash',NULL,'pending',2900.00,2900.00,'shipped','2026-05-16 12:58:26','2026-05-27 19:23:00'),(4,'SZ-20260528-7D754',NULL,'тест','vitaly.takmakov@yandex.ru','+7 (937) 952-95-99','pickup','','','',0.00,'','cash',NULL,'pending',25900.00,25900.00,'new','2026-05-28 21:55:39','2026-05-28 21:55:39'),(5,'SZ-20260528-2DBC2',NULL,'Vitaliy Takmakov','vitaly.takmakov@yandex.ru','+7 (937) 952-95-99','pickup','','','',0.00,'','cash',NULL,'pending',2900.00,2900.00,'new','2026-05-28 22:05:35','2026-05-28 22:05:35'),(6,'SZ-20260529-A729C',NULL,'Vitaliy Takmakov','vitaly.takmakov@yandex.ru','+7 (937) 952-95-99','pickup','','','',0.00,'','cash',NULL,'pending',4300.00,4300.00,'new','2026-05-29 00:12:53','2026-05-29 00:12:53'),(7,'SZ-20260610-A0F92',5,'admin','admin@svoizvuk.ru','+7 (937) 952-95-99','pickup','','','',0.00,'','sbp',NULL,'paid',5800.00,5800.00,'new','2026-06-10 23:55:50','2026-06-10 23:55:50');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `product_id` int NOT NULL AUTO_INCREMENT,
  `category_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) NOT NULL,
  `year` int NOT NULL,
  `label` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `catalog_number` varchar(50) DEFAULT NULL,
  `format` text NOT NULL,
  `price` int NOT NULL,
  `description` text NOT NULL,
  `image_url` text NOT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `uniq_title_artist` (`title`,`artist`),
  KEY `category_id` (`category_id`) USING BTREE,
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=220 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,1,'American football','American football',1999,'Polyvinyl','1','LP',2580,'Дебют из Иллинойса, который придумал, как должен звучать эмо без надрыва: переплетённые гитары в нечётных размерах, труба, ощущение последнего тёплого вечера перед отъездом. Дом с обложки давно стал местом паломничества. Тот случай, когда запись 99-го звучит свежее многих новых.','/assets/images/american_football.png'),(2,1,'loveless','my bloody valentine',1989,'Creation Records','2','LP',3500,'Кевин Шилдс потратил два года и едва не разорил лейбл, добиваясь, чтобы гитара звучала как прилив. Слои дисторшна, плывущие питч-бенды, голоса где-то под поверхностью — шугейз, по которому до сих пор сверяют все остальные. Слушать громко, целиком и не разбирая слов.','/assets/images/mbv.png'),(3,1,'Daydream Nation','Sonic Youth',1988,'Enigma Records','3','LP',2900,'Двойной альбом, на котором нью-йоркский нойз окончательно стал формой. Расстроенные гитары, долгие разгоны, переходящие в обвал, и песни, которые умеют быть и колючими, и почти поп. Запись, после которой инди девяностых вообще стало возможным.','/assets/images/sonic_youth.jpg'),(4,1,'Grace','Jeff Buckley',1994,'Columbia Records','4','LP',4300,'Единственный законченный альбом Бакли: голос в несколько октав, гитара на грани церковного хорала и рока. От «Lover, You Should\'ve Come Over» до кавера на «Hallelujah», который многие до сих пор считают главным. Дальше была река под Мемфисом и больше ничего — от этого пластинка звучит ещё острее.','/assets/images/jeff_buckley.jpg'),(5,1,'Slowdive','Slowdive',2017,'Dead Oceans','5','LP',3600,'Возвращение спустя 22 года после распада — и без единой фальшивой ноты. Те же бесконечные гитарные пространства, но уже с покоем людей, которым некому ничего доказывать. Тихое доказательство, что шугейз — это про звук, а не про молодость.','/assets/images/slowdive.jpg'),(6,1,'The Glow Pt. 2 ','The Microphones',2001,'K Records','6','LP',2700,'Фил Элверум записал это почти в одиночку в студии в Анакортесе, и слышно каждое движение: то шёпот, то стена перегруза из ниоткуда. Альбом про смерть, любовь и стихию, собранный из лоу-фая так, что звучит огромным. Одна из самых личных пластинок десятилетия.','/assets/images/the_glow.webp'),(7,2,'Private Music','Deftones',2025,'Reprise Records','7','LP',3700,'Девятый альбом, где тяжесть давно перестала быть про злость. Гитары Карпентера гудят, как органные трубы, голос Морено уходит то в крик, то в почти поп-мелодию. Deftones снова делают то, что умеют только они, — красиво и тревожно в одном звуке.','/assets/images/deftones.jpg'),(8,4,'Kid A','Radiohead',2000,'Parlophone','8','LP',2900,'После «OK Computer» группа выбросила гитары и собрала альбом из синтезаторов, джаза и помех. На релизе многие растерялись, сейчас — точка отсчёта нового века. Холодный снаружи, но если вслушаться — насквозь живой.','/assets/images/kidA.jpg'),(9,4,'In Rainbows','Radiohead ',2007,'XL Recordings','9','LP',3500,'Тот самый, что сперва выложили в сеть по принципу «плати сколько хочешь». За скандалом потерялось главное: это их самая тёплая и прямая запись. Ритмы, за которые держишься, и «Nude», от которой подкашивает.','/assets/images/inRainbows.jpg'),(10,3,'OK Computer','Radiohead',1997,'XL Recordings','10','LP',4100,'Альбом про тревогу конца века — машины, аэропорты, усталость, — записанный в старом особняке. «Paranoid Android», «No Surprises», «Karma Police» давно вошли в общий язык. Если в доме одна пластинка Radiohead, обычно это она.','/assets/images/OKComputer.jpg'),(11,4,'In Utero','Nirvana',1993,'Universal','11','LP',3800,'После того как «Nevermind» сделал их огромными, Кобейн позвал Стива Альбини и записал нарочно сырой, колючий альбом. Под шумом — одни из лучших его песен: «Heart-Shaped Box», «All Apologies». Прощание, которое тогда никто не хотел читать как прощание.','/assets/images/InUtero.jpg'),(12,5,'Symbol','Susumu Yokota',2004,'Lo Recordings','12','LP',1900,'Японский электронщик берёт сэмплы из классики и старого кино и пересобирает их в подводный мерцающий сон. Где-то между эмбиентом и коллажем, ближе к живописи, чем к танцполу. Пластинка для наушников и позднего вечера.','/assets/images/Symbol.jpg'),(13,3,'Seventeen Seconds','The Cure',1980,'Universal','13','LP',3100,'Второй альбом, на котором The Cure уходят в холод и пустоту. Минимум нот, много воздуха и «A Forest» — маленький фильм про то, как потеряться в лесу. Отсюда начинается та самая мрачная линия, по которой группу и узнают.','/assets/images/seventeenSeconds.jpg'),(14,3,'Either/Or','Elliott Smith',1997,'Universal','14','LP',4200,'Записан почти на коленке, а звучит как разговор шёпотом у тебя на кухне. Хрупкие двойные голоса, гитара, горечь без жалости к себе. Через год песни отсюда прозвучат на «Оскаре», но любят его не за это.','/assets/images/elliottsmith.jpg'),(15,3,'Souvlaki','Slowdive ',1993,'Sony','15','LP',2900,'Расставание, переведённое в звук: гитары тают, голоса Холстеда и Гозвелл переплетаются, к двум трекам приложил руку Брайан Ино. В 93-м пресса альбом разнесла, теперь это один из главных шугейз-релизов вообще. Начать стоит с «Souvlaki Space Station».','/assets/images/souvlaki.jpg'),(16,3,'Velvet Underground ','The Velvet Underground',1969,'UMC','16','LP',4200,'После ухода Джона Кейла группа сделала тихий, почти домашний альбом — никакого нойза, только песни. «Pale Blue Eyes», «Candy Says» — негромко и прямо в сердце. Любимая пластинка тех, кто думал, что уже знает Velvet Underground.','/assets/images/velvetUnderground.jpg'),(17,2,'Xiu Mutha Xiu: Vol. 1','Xiu Xiu',2026,'Polyvinyl Record Co.\r\n','17','LP',4500,'Сборник каверов, в которых Джейми Стюарт делает с чужими песнями то же, что обычно со своими, — выкручивает до нерва. Знакомое становится неуютным и новым. Для тех, кто любит, когда красиво и слегка не по себе.','/assets/images/XiuXiu.jpg'),(18,5,'Untrue','Burial ',2007,'Hyperdub','18','CD',2200,'Лондонский дабстеп, собранный из обрезков вокала, треска винила и дождя по асфальту. Звучит как ночной город из окна последнего автобуса. Альбом, который в одиночку задал настроение целому десятилетию электроники.','/assets/images/Burial.jpg'),(19,5,'Discovery','Daft Punk',2001,'Parlophone','19','2LP',4900,'Французы превратили детскую любовь к диско и роботам в почти идеальный праздник. «One More Time», «Digital Love», «Harder Better Faster Stronger» — а под всем этим неожиданная ностальгия. Танцевальная пластинка, от которой иногда щемит.','/assets/images/DaftPunk.jpg\r\n'),(20,5,'Ambient 1','Brian Eno',1978,'Virgin','20','LP',3600,'Ино придумал музыку, которую можно слушать, а можно и не слушать, — и от этого она не становится хуже. Медленные петли фортепиано и голосов, написанные, чтобы снять тревогу в зале ожидания. Собственно, с этой пластинки и начинается слово «эмбиент».','/assets/images/BrianEno.jpg'),(21,3,'The Rise and Fall ','David Bowie',1972,'Parlophone ','21','LP',4100,'Боуи придумал инопланетного рок-героя и сам им стал — на пару лет. Глэм, гитара Мика Ронсона, история звезды, которая сгорает на глазах. От «Starman» до «Rock \'n\' Roll Suicide» — цельный спектакль на две стороны.','/assets/images/DavidBowie.jpg'),(22,6,'Unknown Pleasures','Joy Division',1979,'Factory','0','LP',3200,'Манчестер, продюсер Мартин Ханнетт, записавший даже звук пустоты между нотами. Голос Кёртиса где-то на дне, бас впереди гитар, обложка, которую вы видели на сотне футболок. Дебют, мрачнее которого выходило мало что.','/assets/images/1779888964_6a16f34426c31.jpg'),(23,6,'Disintegration','The Cure',1989,'Fiction Records','0','LP',3500,'Роберт Смит к тридцати решил, что хватит синглов, и сделал огромный, медленный, тонущий в реверберации альбом. «Pictures of You», «Lovesong», «Lullaby» — сплошная красивая печаль. Многие считают это их вершиной, и спорить трудно.','/assets/images/1779887868_6a16eefce18e3.jpg'),(24,6,'Remain in Light','Talking Heads',1980,'Sire','0','LP',3300,'С Брайаном Ино группа собрала альбом из африканских ритмов, петель и паранойи большого города. «Once in a Lifetime» — про то, как однажды просыпаешься и не узнаёшь собственную жизнь. Танцевать и тревожиться одновременно — их фирменный фокус.','/assets/images/1779887838_6a16eede91475.jpg'),(27,7,'Heaven or Las Vegas','Cocteau Twins',1990,'4AD','0','LP',3500,'Голос Лиз Фрейзер, в котором не разобрать ни слова, и это совсем не мешает — наоборот. Гитары как блёстки, всё будто светится изнутри. Самый тёплый и доступный их альбом и при этом ни на что не похожий.','/assets/images/1779887808_6a16eec07f9d5.jpg'),(28,8,'Madvillainy','Madvillain',2004,'Stones Throw','0','2xLP',3900,'MF DOOM и Madlib заперлись в студии и сделали хип-хоп, который звучит как старая бобина, найденная на чердаке. Короткие треки без припевов, рифмы из-под маски, сэмплы из ниоткуда. Пластинка, которую андеграунд до сих пор пытается переплюнуть.','/assets/images/1779887775_6a16ee9f33def.jpg'),(29,8,'The Low End Theory','A Tribe Called Quest',1991,'Jive','1418','LP',3500,'Хип-хоп, по-настоящему подружившийся с джазом: живой бас Рона Картера, воздух, ум. Кью-Тип и Файф Дог читают легко, а под ними один из самых приятных на слух битов в жанре. Запись, к которой возвращаешься десятилетиями.','/assets/images/1779887730_6a16ee72338a5.jpg'),(30,8,'To Pimp a Butterfly','Kendrick Lamar',2015,'Top Dawg / Aftermath','0','2xLP',4100,'Фанк, джаз, спокен-ворд и очень много боли про то, каково это — быть чёрным в Америке. Плотный, временами тяжёлый альбом, который встретили как событие, и заслуженно. Не фон: с ним нужно сидеть.','/assets/images/1779887684_6a16ee44304ce.jpg'),(31,9,'What\'s Going On','Marvin Gaye',1971,'Tamla','0','LP',3400,'Гэй ушёл от любовных песен и спел про войну, бедность и экологию — на Motown сперва побоялись это выпускать. Получилась тёплая текучая сюита, где одна песня перетекает в следующую. Один из тех альбомов, после которых соул повзрослел.','/assets/images/1779887632_6a16ee10492af.jpg'),(32,9,'Super Fly','Curtis Mayfield',1972,'Curtom','0','LP',3500,'Саундтрек, оказавшийся умнее своего фильма: Мэйфилд поёт про улицу, дурь и выживание мягким фальцетом поверх тугого фанка. «Pusherman», «Freddie\'s Dead» — красиво и без иллюзий. Музыка из кино, которая живёт своей, куда более долгой жизнью.','/assets/images/1779887586_6a16ede2a1d69.jpg'),(33,9,'Voodoo','D\'Angelo',2000,'Virgin','7243','2xLP',4300,'Три года в Electric Lady с The Soulquarians — и нью-соул, который будто всё время чуть запаздывает, и в этом весь смак. Тёплый, грувовый, дышащий альбом про тело и душу. После него Д\'Анджело пропал на годы — оно того стоило.','/assets/images/1779887541_6a16edb5762d5.jpg'),(34,10,'Pink Moon','Nick Drake',1972,'Island Records','0','LP',3600,'Третий и последний прижизненный альбом: голос, гитара, полчаса и почти ничего больше. Дрейк записал его за две ночи и тихо отдал лейблу. Грустно, просто и совершенно — пластинка, которую при жизни почти никто не услышал.','/assets/images/1779887468_6a16ed6cadc60.jpg'),(35,10,'For Emma, Forever Ago','Bon Iver',2007,'Jagjaguwar','0','LP',3300,'Джастин Вернон уехал зимой в хижину в Висконсине и записал альбом про разрыв сам — одна гитара и многоголосье. Холод и тепло в одном звуке, фальцет, от которого пробирает. История записи стала легендой, но дело всё-таки в песнях.','/assets/images/1779887426_6a16ed4250835.jpg'),(36,10,'Carrie & Lowell','Sufjan Stevens',2015,'Asthmatic Kitty','0','LP',3400,'Самый тихий и личный альбом Стивенса — про мать, которая то появлялась, то исчезала, и про её смерть. Шёпот, акустика, ничего лишнего. Слушать в одиночестве и быть готовым, что зацепит.','/assets/images/1779887379_6a16ed13aa914.jpg'),(37,11,'The Blue Notebooks','Max Richter',2004,'FatCat Records','130701','LP',3500,'Современная классика на фоне читаемого вслух Кафки и стука печатной машинки — Рихтер записал это как тихий протест против войны. Струнные, фортепиано, много пауз. «On the Nature of Daylight» вы наверняка слышали в кино, даже не зная названия.','/assets/images/1779887345_6a16ecf113702.jpg'),(38,11,'All Melody','Nils Frahm',2018,'Erased Tapes','0','2xLP',3800,'Фрам построил студию в старом берлинском радиодоме и записал альбом, где живое фортепиано встречается с синтезаторами и хором так, что не видно швов. То интимно, как ночью наедине с инструментом, то огромно, как зал. Электроника, сделанная человеческими руками.','/assets/images/1779887291_6a16ecbb34d53.jpg');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tracklist`
--

DROP TABLE IF EXISTS `tracklist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tracklist` (
  `track_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `track_number` int NOT NULL,
  `title` text NOT NULL,
  `audio_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`track_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `tracklist_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=194 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tracklist`
--

LOCK TABLES `tracklist` WRITE;
/*!40000 ALTER TABLE `tracklist` DISABLE KEYS */;
INSERT INTO `tracklist` VALUES (1,1,1,'Never Meant','\\assets\\audio\\American Football\\Never Meant.mp3'),(2,1,2,'The Summer Ends','\\assets\\audio\\American Football\\The Summer Ends.mp3'),(3,1,3,'Honestly?','\\assets\\audio\\American Football\\Honestly_.mp3'),(4,1,4,'For Sure','\\assets\\audio\\American Football\\For Sure.mp3'),(5,1,5,'You Know I Should Be Leaving Soon','\\assets\\audio\\American Football\\You Know I Should Be Leaving Soon.mp3'),(6,1,6,'But the Regrets Are Killing Me','\\assets\\audio\\American Football\\But the Regrets Are Killing Me.mp3'),(7,1,7,'I ll See You When We re Both Not So Emotional','\\assets\\audio\\American Football\\I\'ll See You When We\'re Both Not So Emotional.mp3'),(8,1,8,'Stay Home','\\assets\\audio\\American Football\\Stay Home.mp3'),(9,1,9,'The One with the Wurlitzer','\\assets\\audio\\American Football\\The One With The Wurlitzer.mp3'),(11,2,1,'blown a wish','/assets/audio/2/001_blown_a_wish.mp3'),(12,2,2,'come in alone','/assets/audio/2/002_come_in_alone.mp3'),(13,2,3,'i only said','/assets/audio/2/003_i_only_said.mp3'),(14,2,4,'loomer','/assets/audio/2/004_loomer.mp3'),(15,2,5,'only shallow','/assets/audio/2/005_only_shallow.mp3'),(16,2,6,'sometimes','/assets/audio/2/006_sometimes.mp3'),(17,2,7,'soon','/assets/audio/2/007_soon.mp3'),(18,2,8,'to here knows when','/assets/audio/2/008_to_here_knows_when.mp3'),(19,2,9,'touched','/assets/audio/2/009_touched.mp3'),(20,2,10,'what you want','/assets/audio/2/010_what_you_want.mp3'),(21,2,11,'when you sleep','/assets/audio/2/011_when_you_sleep.mp3'),(22,3,1,'\'Cross the Breeze (Album Version)','/assets/audio/3/001__Cross_the_Breeze__Album_Version_.mp3'),(23,3,2,'A) the Wonder (Album Version)','/assets/audio/3/002_A__the_Wonder__Album_Version_.mp3'),(24,3,3,'B) Hyperstation (Album Version)','/assets/audio/3/003_B__Hyperstation__Album_Version_.mp3'),(25,3,4,'Candle','/assets/audio/3/004_Candle.mp3'),(26,3,5,'Eric\'s Trip (Album Version)','/assets/audio/3/005_Eric_s_Trip__Album_Version_.mp3'),(27,3,6,'Hey Joni (Album Version)','/assets/audio/3/006_Hey_Joni__Album_Version_.mp3'),(28,3,7,'Kissability (Album Version)','/assets/audio/3/007_Kissability__Album_Version_.mp3'),(29,3,8,'Providence','/assets/audio/3/008_Providence.mp3'),(30,3,9,'Rain King (Album Version)','/assets/audio/3/009_Rain_King__Album_Version_.mp3'),(31,3,10,'Silver Rocket (Album Version)','/assets/audio/3/010_Silver_Rocket__Album_Version_.mp3'),(32,3,11,'Teen Age Riot (Album Version)','/assets/audio/3/011_Teen_Age_Riot__Album_Version_.mp3'),(33,3,12,'The Sprawl (Album Version)','/assets/audio/3/012_The_Sprawl__Album_Version_.mp3'),(34,3,13,'Total Trash (Album Version)','/assets/audio/3/013_Total_Trash__Album_Version_.mp3'),(35,3,14,'Z) Eliminator, Jr.','/assets/audio/3/014_Z__Eliminator__Jr_.mp3'),(36,4,1,'Mojo Pin','/assets/audio/4/001_mojo_pin.mp3'),(37,4,2,'Grace','/assets/audio/4/002_grace.mp3'),(38,4,3,'Lilac Wine','/assets/audio/4/003_lilac_wine.mp3'),(39,4,4,'So Real','/assets/audio/4/004_so_real.mp3'),(40,4,5,'Hallelujah','/assets/audio/4/005_hallelujah.mp3'),(41,4,6,'Lover, You Should\'ve Come Over','/assets/audio/4/006_lover_you_should_ve_come_over.mp3'),(42,4,7,'Corpus Christi Carol','/assets/audio/4/007_corpus_christi_carol.mp3'),(43,4,8,'Eternal Life','/assets/audio/4/008_eternal_life.mp3'),(44,4,9,'Dream Brother','/assets/audio/4/009_dream_brother.mp3'),(45,4,10,'Forget Her','/assets/audio/4/010_forget_her.mp3'),(46,5,1,'Slomo','/assets/audio/5/001_slomo.mp3'),(47,5,2,'Star Roving','/assets/audio/5/002_star_roving.mp3'),(48,5,3,'Don\'t Know Why','/assets/audio/5/003_don_t_know_why.mp3'),(49,5,4,'Sugar For The Pill','/assets/audio/5/004_sugar_for_the_pill.mp3'),(50,5,5,'Everyone Knows','/assets/audio/5/005_everyone_knows.mp3'),(51,5,6,'No Longer Making Time','/assets/audio/5/006_no_longer_making_time.mp3'),(52,5,7,'Go Get It','/assets/audio/5/007_go_get_it.mp3'),(53,5,8,'Falling Ashes','/assets/audio/5/008_falling_ashes.mp3'),(54,6,1,'I Want Wind to Blow','/assets/audio/6/001_i_want_wind_to_blow.mp3'),(55,6,2,'The Glow, Pt. 2','/assets/audio/6/002_the_glow_pt_2.mp3'),(56,6,3,'The Moon','/assets/audio/6/003_the_moon.mp3'),(57,6,4,'Headless Horseman','/assets/audio/6/004_headless_horseman.mp3'),(58,6,5,'My Roots Are Strong and Deep','/assets/audio/6/005_my_roots_are_strong_and_deep.mp3'),(59,6,6,'Instrumental','/assets/audio/6/006_instrumental.mp3'),(60,6,7,'The Mansion','/assets/audio/6/007_the_mansion.mp3'),(61,6,8,'(Something)','/assets/audio/6/008_something.mp3'),(62,6,9,'(Something) - 1','/assets/audio/6/009_something_1.mp3'),(63,6,10,'I\'ll Not Contain You','/assets/audio/6/010_i_ll_not_contain_you.mp3'),(64,6,11,'The Gleam, Pt. 2','/assets/audio/6/011_the_gleam_pt_2.mp3'),(65,6,12,'Map','/assets/audio/6/012_map.mp3'),(66,6,13,'You\'ll Be in the Air','/assets/audio/6/013_you_ll_be_in_the_air.mp3'),(67,6,14,'I Want to Be Cold','/assets/audio/6/014_i_want_to_be_cold.mp3'),(68,6,15,'I Am Bored','/assets/audio/6/015_i_am_bored.mp3'),(69,6,16,'I Felt My Size','/assets/audio/6/016_i_felt_my_size.mp3'),(70,6,17,'Instrumental - 2','/assets/audio/6/017_instrumental_2.mp3'),(71,6,18,'I Felt Your Shape','/assets/audio/6/018_i_felt_your_shape.mp3'),(72,9,1,'15 Step','/assets/audio/9/001_15_step.mp3'),(73,9,2,'Bodysnatchers','/assets/audio/9/002_bodysnatchers.mp3'),(74,9,3,'Nude','/assets/audio/9/003_nude.mp3'),(75,9,4,'Weird Fishes / Arpeggi','/assets/audio/9/004_weird_fishes_arpeggi.mp3'),(76,9,5,'All I Need','/assets/audio/9/005_all_i_need.mp3'),(77,9,6,'Faust Arp','/assets/audio/9/006_faust_arp.mp3'),(78,9,7,'Reckoner','/assets/audio/9/007_reckoner.mp3'),(79,9,8,'House Of Cards','/assets/audio/9/008_house_of_cards.mp3'),(80,9,9,'Jigsaw Falling Into Place','/assets/audio/9/009_jigsaw_falling_into_place.mp3'),(81,9,10,'Videotape','/assets/audio/9/010_videotape.mp3'),(82,10,1,'Airbag','/assets/audio/10/001_airbag.mp3'),(83,10,2,'Paranoid Android','/assets/audio/10/002_paranoid_android.mp3'),(84,10,3,'Subterranean Homesick Alien','/assets/audio/10/003_subterranean_homesick_alien.mp3'),(85,10,4,'Exit Music (For A Film)','/assets/audio/10/004_exit_music_for_a_film.mp3'),(86,10,5,'Let Down','/assets/audio/10/005_let_down.mp3'),(87,10,6,'Karma Police','/assets/audio/10/006_karma_police.mp3'),(88,10,7,'Fitter Happier','/assets/audio/10/007_fitter_happier.mp3'),(89,10,8,'Electioneering','/assets/audio/10/008_electioneering.mp3'),(90,10,9,'Climbing Up the Walls','/assets/audio/10/009_climbing_up_the_walls.mp3'),(91,10,10,'No Surprises','/assets/audio/10/010_no_surprises.mp3'),(92,10,11,'Lucky','/assets/audio/10/011_lucky.mp3'),(93,10,12,'The Tourist','/assets/audio/10/012_the_tourist.mp3'),(94,13,1,'A Reflection','/assets/audio/13/001_a_reflection.mp3'),(95,13,2,'Play For Today','/assets/audio/13/002_play_for_today.mp3'),(96,13,3,'Secrets','/assets/audio/13/003_secrets.mp3'),(97,13,4,'In Your House','/assets/audio/13/004_in_your_house.mp3'),(98,13,5,'The Final Sound','/assets/audio/13/005_the_final_sound.mp3'),(99,13,6,'A Forest','/assets/audio/13/006_a_forest.mp3'),(100,13,7,'M','/assets/audio/13/007_m.mp3'),(101,13,8,'At Night','/assets/audio/13/008_at_night.mp3'),(102,13,9,'Seventeen Seconds','/assets/audio/13/009_seventeen_seconds.mp3'),(103,14,1,'Speed Trials','/assets/audio/14/001_speed_trials.mp3'),(104,14,2,'Alameda','/assets/audio/14/002_alameda.mp3'),(105,14,3,'Ballad Of Big Nothing','/assets/audio/14/003_ballad_of_big_nothing.mp3'),(106,14,4,'Between The Bars','/assets/audio/14/004_between_the_bars.mp3'),(107,14,5,'Pictures Of Me','/assets/audio/14/005_pictures_of_me.mp3'),(108,14,6,'No Name No. 5','/assets/audio/14/006_no_name_no_5.mp3'),(109,14,7,'Rose Parade','/assets/audio/14/007_rose_parade.mp3'),(110,14,8,'Punch And Judy','/assets/audio/14/008_punch_and_judy.mp3'),(111,14,9,'Angeles','/assets/audio/14/009_angeles.mp3'),(112,14,10,'Cupid\'s Trick','/assets/audio/14/010_cupid_s_trick.mp3'),(113,14,11,'Say Yes','/assets/audio/14/011_say_yes.mp3'),(114,15,1,'Alison','/assets/audio/15/001_alison.mp3'),(115,15,2,'Some Velvet Morning','/assets/audio/15/002_some_velvet_morning.mp3'),(116,15,3,'Machine Gun','/assets/audio/15/003_machine_gun.mp3'),(117,15,4,'So Tired - Single Version','/assets/audio/15/004_so_tired_single_version.mp3'),(118,15,5,'40 Days','/assets/audio/15/005_40_days.mp3'),(119,15,6,'Moussaka Chaos - Single Version','/assets/audio/15/006_moussaka_chaos_single_version.mp3'),(120,15,7,'In Mind - Single Version','/assets/audio/15/007_in_mind_single_version.mp3'),(121,15,8,'Sing','/assets/audio/15/008_sing.mp3'),(122,15,9,'Good Day Sunshine - Single Version','/assets/audio/15/009_good_day_sunshine_single_version.mp3'),(123,15,10,'Here She Comes','/assets/audio/15/010_here_she_comes.mp3'),(124,15,11,'Missing You - Single Version','/assets/audio/15/011_missing_you_single_version.mp3'),(125,15,12,'Souvlaki Space Station','/assets/audio/15/012_souvlaki_space_station.mp3'),(126,15,13,'Country Rain - Single Version','/assets/audio/15/013_country_rain_single_version.mp3'),(127,15,14,'When the Sun Hits','/assets/audio/15/014_when_the_sun_hits.mp3'),(128,15,15,'Altogether','/assets/audio/15/015_altogether.mp3'),(129,15,16,'In Mind - Bandulu Remix (Out Mind)','/assets/audio/15/016_in_mind_bandulu_remix_out_mind.mp3'),(130,15,17,'In Mind - Reload Remix (The 147 Take)','/assets/audio/15/017_in_mind_reload_remix_the_147_take.mp3'),(131,15,18,'Melon Yellow','/assets/audio/15/018_melon_yellow.mp3'),(132,15,19,'Dagger','/assets/audio/15/019_dagger.mp3'),(133,16,1,'Sunday Morning','/assets/audio/16/001_sunday_morning.mp3'),(134,16,2,'I\'m Waiting For The Man','/assets/audio/16/002_i_m_waiting_for_the_man.mp3'),(135,16,3,'Femme Fatale','/assets/audio/16/003_femme_fatale.mp3'),(136,16,4,'Venus In Furs','/assets/audio/16/004_venus_in_furs.mp3'),(137,16,5,'Run Run Run','/assets/audio/16/005_run_run_run.mp3'),(138,16,6,'All Tomorrow\'s Parties','/assets/audio/16/006_all_tomorrow_s_parties.mp3'),(139,16,7,'Heroin','/assets/audio/16/007_heroin.mp3'),(140,16,8,'There She Goes Again','/assets/audio/16/008_there_she_goes_again.mp3'),(141,16,9,'I\'ll Be Your Mirror','/assets/audio/16/009_i_ll_be_your_mirror.mp3'),(142,16,10,'The Black Angel\'s Death Song','/assets/audio/16/010_the_black_angel_s_death_song.mp3'),(143,16,11,'European Son','/assets/audio/16/011_european_son.mp3'),(144,22,1,'Disorder (2007 Remastered Version)','/assets/audio/22/001_disorder_2007_remastered_version.mp3'),(145,22,2,'Day Of The Lords (2007 Remastered Version)','/assets/audio/22/002_day_of_the_lords_2007_remastered_version.mp3'),(146,22,3,'Candidate (2007 Remastered Version)','/assets/audio/22/003_candidate_2007_remastered_version.mp3'),(147,22,4,'Insight (2007 Remastered Version)','/assets/audio/22/004_insight_2007_remastered_version.mp3'),(148,22,5,'New Dawn Fades (2007 Remastered Version)','/assets/audio/22/005_new_dawn_fades_2007_remastered_version.mp3'),(149,22,6,'She\'s Lost Control (2007 Remastered Version)','/assets/audio/22/006_she_s_lost_control_2007_remastered_version.mp3'),(150,22,7,'Shadowplay (2007 Remastered Version)','/assets/audio/22/007_shadowplay_2007_remastered_version.mp3'),(151,22,8,'Wilderness (2007 Remastered Version)','/assets/audio/22/008_wilderness_2007_remastered_version.mp3'),(152,22,9,'Interzone (2007 Remastered Version)','/assets/audio/22/009_interzone_2007_remastered_version.mp3'),(153,22,10,'I Remember Nothing (2007 Remastered Version)','/assets/audio/22/010_i_remember_nothing_2007_remastered_version.mp3'),(154,23,1,'Plainsong - Remastered','/assets/audio/23/001_plainsong_remastered.mp3'),(155,23,2,'Pictures Of You - Remastered','/assets/audio/23/002_pictures_of_you_remastered.mp3'),(156,23,3,'Closedown - Remastered 2010','/assets/audio/23/003_closedown_remastered_2010.mp3'),(157,23,4,'Lullaby - Remastered','/assets/audio/23/004_lullaby_remastered.mp3'),(158,23,5,'Fascination Street - Remastered','/assets/audio/23/005_fascination_street_remastered.mp3'),(159,23,6,'Prayers For Rain - Remastered','/assets/audio/23/006_prayers_for_rain_remastered.mp3'),(160,23,7,'The Same Deep Water As You - Remastered','/assets/audio/23/007_the_same_deep_water_as_you_remastered.mp3'),(161,23,8,'Disintegration - Remastered','/assets/audio/23/008_disintegration_remastered.mp3'),(162,27,1,'Cherry-coloured Funk','/assets/audio/27/001_cherry_coloured_funk.mp3'),(163,27,2,'Pitch the Baby','/assets/audio/27/002_pitch_the_baby.mp3'),(164,27,3,'Iceblink Luck','/assets/audio/27/003_iceblink_luck.mp3'),(165,27,4,'Fifty-fifty Clown','/assets/audio/27/004_fifty_fifty_clown.mp3'),(166,27,5,'Heaven or Las Vegas','/assets/audio/27/005_heaven_or_las_vegas.mp3'),(167,27,6,'I Wear Your Ring','/assets/audio/27/006_i_wear_your_ring.mp3'),(168,27,7,'Fotzepolitic','/assets/audio/27/007_fotzepolitic.mp3'),(169,27,8,'Wolf in the Breast','/assets/audio/27/008_wolf_in_the_breast.mp3'),(170,27,9,'Road, River and Rail','/assets/audio/27/009_road_river_and_rail.mp3'),(171,27,10,'Frou-frou Foxes in Midsummer Fires','/assets/audio/27/010_frou_frou_foxes_in_midsummer_fires.mp3'),(172,28,1,'The Illest Villains','/assets/audio/28/001_the_illest_villains.mp3'),(173,28,2,'Accordion','/assets/audio/28/002_accordion.mp3'),(174,28,3,'Meat Grinder','/assets/audio/28/003_meat_grinder.mp3'),(175,28,4,'Bistro','/assets/audio/28/004_bistro.mp3'),(176,28,5,'Raid (feat. MED)','/assets/audio/28/005_raid_feat_med.mp3'),(177,28,6,'America\'s Most Blunted (feat. Quasimoto)','/assets/audio/28/006_america_s_most_blunted_feat_quasimoto.mp3'),(178,28,7,'Sickfit (Instrumental)','/assets/audio/28/007_sickfit_instrumental.mp3'),(179,28,8,'Rainbows','/assets/audio/28/008_rainbows.mp3'),(180,28,9,'Curls','/assets/audio/28/009_curls.mp3'),(181,28,10,'Do Not Fire! (Instrumental)','/assets/audio/28/010_do_not_fire_instrumental.mp3'),(182,28,11,'Money Folder','/assets/audio/28/011_money_folder.mp3'),(183,28,12,'Shadows of Tomorrow (feat. Quasimoto)','/assets/audio/28/012_shadows_of_tomorrow_feat_quasimoto.mp3'),(184,28,13,'Operation Lifesaver aka Mint Test','/assets/audio/28/013_operation_lifesaver_aka_mint_test.mp3'),(185,28,14,'Figaro','/assets/audio/28/014_figaro.mp3'),(186,28,15,'Hardcore Hustle (feat. Wildchild)','/assets/audio/28/015_hardcore_hustle_feat_wildchild.mp3'),(187,28,16,'Strange Ways','/assets/audio/28/016_strange_ways.mp3'),(188,28,17,'Fancy Clown (feat. Viktor Vaughn)','/assets/audio/28/017_fancy_clown_feat_viktor_vaughn.mp3'),(189,28,18,'Eye (feat. Stacy Epps)','/assets/audio/28/018_eye_feat_stacy_epps.mp3'),(190,28,19,'Supervillain Theme (Instrumental)','/assets/audio/28/019_supervillain_theme_instrumental.mp3'),(191,28,20,'All Caps','/assets/audio/28/020_all_caps.mp3'),(192,28,21,'Great Day','/assets/audio/28/021_great_day.mp3'),(193,28,22,'Rhinestone Cowboy','/assets/audio/28/022_rhinestone_cowboy.mp3');
/*!40000 ALTER TABLE `tracklist` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `phone` int NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `password` varchar(255) NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'ggg','1234@123',123,'user','123','2026-01-16'),(2,'1234','12345@123',123,'user','123','2026-01-16'),(3,'123','123456@123',123,'user','123','2026-01-16'),(4,'123','123@123',123,'user','123','2026-01-17'),(5,'admin','admin@svoizvuk.ru',1111,'admin','$2y$10$zCUB2n9CZrBw.o9p3GbgNew3Gh/ESu6xFORf8LGR4btLDl0bwfi32','2026-02-12'),(6,'uexhdsltnh','tjqgsmzl@immenseignite.info',1,'user','qnjstyzjsgzv','2026-06-06');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!50112 SET @disable_bulk_load = IF (@is_rocksdb_supported, 'SET SESSION rocksdb_bulk_load = @old_rocksdb_bulk_load', 'SET @dummy_rocksdb_bulk_load = 0') */;
/*!50112 PREPARE s FROM @disable_bulk_load */;
/*!50112 EXECUTE s */;
/*!50112 DEALLOCATE PREPARE s */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-12 19:35:46
