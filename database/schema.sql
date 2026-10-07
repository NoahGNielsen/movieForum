-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: xxx
-- Generation Time: Oct 07, 2026 at 07:54 AM
-- Server version: 8.4.11-11
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `xxx`
--

-- --------------------------------------------------------

--
-- Table structure for table `Attachments`
--

CREATE TABLE `Attachments` (
  `attachmentId` int UNSIGNED NOT NULL,
  `userId` char(18) NOT NULL,
  `attachmentType` varchar(25) NOT NULL,
  `attachmentFile` longblob NOT NULL,
  `attachmentUploadTime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Channels`
--

CREATE TABLE `Channels` (
  `channelId` int NOT NULL,
  `isTopChannel` tinyint(1) NOT NULL,
  `ownerChannelId` int DEFAULT NULL,
  `channelName` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `channelDescription` varchar(255) DEFAULT NULL,
  `channelCreator` char(18) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Channels`
--

INSERT INTO `Channels` (`channelId`, `isTopChannel`, `ownerChannelId`, `channelName`, `channelDescription`, `channelCreator`) VALUES
(1, 1, NULL, 'Science Fiction', NULL, NULL),
(2, 1, NULL, 'Action', 'Her er der Action film, fx. Mission Imposible og Fast & Furious', NULL),
(3, 1, NULL, 'Adventure', NULL, NULL),
(4, 1, NULL, 'Comedy', NULL, NULL),
(5, 1, NULL, 'Crime and mystery', NULL, NULL),
(6, 1, NULL, 'Fantasy', NULL, NULL),
(7, 1, NULL, 'Historical', NULL, NULL),
(8, 1, NULL, 'Horror', NULL, NULL),
(9, 1, NULL, 'Satire', NULL, NULL),
(10, 1, NULL, 'Thriller', NULL, NULL),
(11, 1, NULL, 'Other', NULL, NULL),
(12, 0, 1, 'Star Wars', NULL, NULL),
(13, 1, NULL, 'Romance', NULL, NULL),
(14, 1, NULL, 'Documentary', NULL, NULL),
(15, 1, NULL, 'Information', 'Information omkring Pellicula Film Forum, herunder FAQ', NULL),
(16, 0, 18, 'HANS', 'HANSENSENS', 'SzgMDMNv_222_29233'),
(18, 1, NULL, 'Sjov og Spas', 'Dette er en hygge chat til alt og intet på samme tid :)', 'SHyZRXdL_843_65137'),
(19, 0, 18, 'dette er min', 'Ja så dette er så en mega fed test', 'SHyZRXdL_843_65137'),
(20, 0, 7, 'Anden Verdenskrig', 'Dette omhandler anden verdenskrig', 'SHyZRXdL_843_65137'),
(22, 0, 15, 'Release The Studietur Files!', 'Kan Noah jonglere?', 'bYGvCPgf_635_17797'),
(23, 0, 15, 'Spørgsmål til Noah', 'Her kan man stille spørgsmål til Noah', 'PBOVBpOj_394_19612');

-- --------------------------------------------------------

--
-- Table structure for table `Comments`
--

CREATE TABLE `Comments` (
  `commentId` int NOT NULL,
  `userId` char(18) NOT NULL,
  `ownerPostId` int NOT NULL,
  `messageContent` text NOT NULL,
  `timeStamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Comments`
--

INSERT INTO `Comments` (`commentId`, `userId`, `ownerPostId`, `messageContent`, `timeStamp`) VALUES
(1, 'SHyZRXdL_843_65137', 1, 'test comment', '2026-10-04 21:42:21'),
(2, 'KNFxfAXU_117_83331', 5, 'HVa satatan din Ænglænder, du er sku iksseg JEg er meget bedre end deig', '2026-10-05 06:17:22'),
(3, 'KNFxfAXU_117_83331', 5, 'æ syns du e r æ gay', '2026-10-05 06:22:49'),
(4, 'KNFxfAXU_117_83331', 5, '.how to not get beskeder slettet', '2026-10-05 06:23:15'),
(6, 'KNFxfAXU_117_83331', 5, 'Æ noah elsker æ børnehave', '2026-10-05 06:26:24'),
(7, 'KNFxfAXU_117_83331', 5, 'æ komme der æ tit', '2026-10-05 06:26:31'),
(8, 'KNFxfAXU_117_83331', 5, 'Jeg kan høre der er allegations', '2026-10-05 06:27:13'),
(9, 'KNFxfAXU_117_83331', 5, 'det ligeesom en hypotese', '2026-10-05 06:27:35'),
(10, 'KNFxfAXU_117_83331', 5, 'de skal be eller afkræftes', '2026-10-05 06:27:45'),
(11, 'KNFxfAXU_117_83331', 5, 'hva ser æ møk tema æ grimt', '2026-10-05 06:28:40'),
(12, 'KNFxfAXU_117_83331', 5, 'æ noah er = grim', '2026-10-05 06:28:55'),
(13, 'SzgMDMNv_222_29233', 5, 'Bruh', '2026-10-05 06:37:13'),
(14, 'KNFxfAXU_117_83331', 5, 'æ NOha elsker æ børn æ Nordstejernen', '2026-10-05 06:37:22'),
(15, 'SzgMDMNv_222_29233', 5, 'Nej, wtf bro', '2026-10-05 06:37:33'),
(16, 'KNFxfAXU_117_83331', 5, 'Æ Defend?', '2026-10-05 06:37:42'),
(17, 'KNFxfAXU_117_83331', 5, 'NÆÆÆÆ', '2026-10-05 06:37:45'),
(18, 'SzgMDMNv_222_29233', 5, 'JA sku da, jeg kan ik lide børn.....', '2026-10-05 06:38:00'),
(19, 'SzgMDMNv_222_29233', 5, 'Ik på den måde', '2026-10-05 06:38:05'),
(20, 'KNFxfAXU_117_83331', 5, 'Æ Noha kan lide æ Gratis arbejdskræft', '2026-10-05 06:38:49'),
(21, 'SzgMDMNv_222_29233', 5, 'Ja sku da, hvorfor tror du vi var slave handlere tilbage i tiden? Og vi var faktisk rigtig gode til det :)', '2026-10-05 06:39:22'),
(22, 'KNFxfAXU_117_83331', 5, 'True mi friend\nGODE GAMLE TIDER', '2026-10-05 06:40:07'),
(23, 'KNFxfAXU_117_83331', 5, 'la os sætte klokken tilbake', '2026-10-05 06:40:24'),
(24, 'SzgMDMNv_222_29233', 5, 'My man :)', '2026-10-05 06:40:49'),
(25, 'SzgMDMNv_222_29233', 5, 'Remember, great minds - think alike', '2026-10-05 06:40:59'),
(26, 'KNFxfAXU_117_83331', 5, 'KIrsten/Chresten = Brian^2', '2026-10-05 06:42:16'),
(27, 'SzgMDMNv_222_29233', 5, '😂🤣😂', '2026-10-05 06:42:54'),
(28, 'KNFxfAXU_117_83331', 5, 'gg', '2026-10-05 06:43:02'),
(29, 'SzgMDMNv_222_29233', 5, 'Omg bro, selfølgelig siger du det', '2026-10-05 06:43:07'),
(30, 'KNFxfAXU_117_83331', 5, 'Netanyahu _TobiasJ', '2026-10-05 06:43:23'),
(31, 'KNFxfAXU_117_83331', 5, 'HVA SATAN ER DET HER FOR ET INDLEDNING DER HEDDER \"NAME\"', '2026-10-05 06:44:07'),
(32, 'KNFxfAXU_117_83331', 5, '@gay', '2026-10-05 06:44:27'),
(33, 'SzgMDMNv_222_29233', 5, 'bro, det er den title jeg har givet postet. Kig på hvad teksten er på postet, ik bare title', '2026-10-05 06:44:35'),
(34, 'KNFxfAXU_117_83331', 5, 'titel*', '2026-10-05 06:45:01'),
(35, 'SzgMDMNv_222_29233', 5, 'luk røven', '2026-10-05 06:45:10'),
(36, 'KNFxfAXU_117_83331', 5, 'Æ noha er Æ uren\nÆ noha er ik Æ sønderjyde', '2026-10-05 06:46:26'),
(37, 'SzgMDMNv_222_29233', 5, 'Hva? Jeg bor mere mere sydpå end dig? Jeg bor legit lige over den gamle grænse...', '2026-10-05 06:47:22'),
(38, 'SzgMDMNv_222_29233', 5, 'Vi har faktisk den 3. ælste kirke i dk :)', '2026-10-05 06:49:49'),
(39, 'SzgMDMNv_222_29233', 5, 'Det der er med det er at, teknisk set må vi ik bruge chat. Dog kommer de ikke til at se vores kode MEEENNNN. Hvis vores forum kan ALT, så ved de det godt.....', '2026-10-05 07:04:28'),
(40, 'KNFxfAXU_117_83331', 5, 'Kommer der ik snart en billedopdatering????\ndet er ret kedeligt kun med tekst', '2026-10-05 07:05:32'),
(41, 'KNFxfAXU_117_83331', 5, '\"sideeye\"', '2026-10-05 07:05:44'),
(42, 'SzgMDMNv_222_29233', 5, 'yea iknow, men skal lige finde ud af om det er en god ide😅', '2026-10-05 07:06:13'),
(43, 'KNFxfAXU_117_83331', 5, 'Bare \"spørg ind til det\" og så find ud af det og så \"ej så laver vi billeder sammen med\"', '2026-10-05 07:07:01'),
(44, 'SzgMDMNv_222_29233', 5, 'true', '2026-10-05 07:07:11'),
(45, 'SzgMDMNv_222_29233', 5, 'idk meaby, skal lige snakke med gruppen i tek fag imorgen', '2026-10-05 07:07:24'),
(46, 'SzgMDMNv_222_29233', 5, 'Det kommer an på om folk har lyst', '2026-10-05 07:07:34'),
(47, 'KNFxfAXU_117_83331', 5, 'y=Chresten^2', '2026-10-05 07:07:39'),
(48, 'KNFxfAXU_117_83331', 5, 'y´=2Chresten', '2026-10-05 07:07:54'),
(49, 'KNFxfAXU_117_83331', 5, 'y´´=2', '2026-10-05 07:08:04'),
(50, 'KNFxfAXU_117_83331', 5, 'y´´´=0', '2026-10-05 07:08:11'),
(51, 'SzgMDMNv_222_29233', 5, 'fred=(junker^2+brian^2)/chresten', '2026-10-05 07:08:30'),
(52, 'SzgMDMNv_222_29233', 5, 'lol😂😂😂', '2026-10-05 07:08:49'),
(53, 'SzgMDMNv_222_29233', 5, 'bro bliv difrenceret væk....', '2026-10-05 07:08:57'),
(54, 'KNFxfAXU_117_83331', 5, 'selfølgelig', '2026-10-05 07:10:03'),
(55, 'KNFxfAXU_117_83331', 5, 'min yndlingsaktivitet med lærere', '2026-10-05 07:10:12'),
(56, 'KNFxfAXU_117_83331', 5, '💕💕💕', '2026-10-05 07:10:35'),
(57, 'SzgMDMNv_222_29233', 5, 'omg bro😂🤣😂\n\nos Brian?', '2026-10-05 07:10:38'),
(58, 'KNFxfAXU_117_83331', 5, 'hvorfor ik opdatere automatisk???', '2026-10-05 07:11:12'),
(59, 'SzgMDMNv_222_29233', 5, 'faktisk god ide', '2026-10-05 07:11:26'),
(60, 'KNFxfAXU_117_83331', 5, 'Jeg kræver SVARR', '2026-10-05 07:11:51'),
(61, 'KNFxfAXU_117_83331', 5, 'Åh sheit', '2026-10-05 07:12:05'),
(62, 'KNFxfAXU_117_83331', 5, 'Noha lav lige min Dansk opgave', '2026-10-05 07:12:29'),
(63, 'KNFxfAXU_117_83331', 5, 'Carrier et hold jeg ikke engang er på', '2026-10-05 07:12:50'),
(64, 'KNFxfAXU_117_83331', 5, 'bare average mig', '2026-10-05 07:12:56'),
(65, 'SzgMDMNv_222_29233', 5, 'Hva, wtf bro. Nej, jeg laver fandme ik din opgave', '2026-10-05 07:13:21'),
(66, 'KNFxfAXU_117_83331', 5, 'Æ komme med alle Æ gode idéer', '2026-10-05 07:13:35'),
(67, 'SzgMDMNv_222_29233', 5, 'Men, jo tak fred😘', '2026-10-05 07:13:38'),
(68, 'KNFxfAXU_117_83331', 5, '😘😘😘', '2026-10-05 07:14:02'),
(69, 'SzgMDMNv_222_29233', 5, 'Im back bitch!', '2026-10-05 07:18:05'),
(70, 'KNFxfAXU_117_83331', 5, 'Jeg ik bitch jeg er king\ndu er min queen og deltidsbitch', '2026-10-05 07:19:21'),
(71, 'SzgMDMNv_222_29233', 5, 'aaauuughwwww😍😍😍😍\n\nEr jeg den deltidsbitch?😘😘😘', '2026-10-05 07:19:51'),
(72, 'SzgMDMNv_222_29233', 5, 'Love u tooo <3', '2026-10-05 07:20:06'),
(73, 'KNFxfAXU_117_83331', 5, '╰(*°▽°*)╯(*/ω＼*)(^///^)', '2026-10-05 07:20:22'),
(74, 'KNFxfAXU_117_83331', 5, '❤️❤️❤️💕😍😍😘😘', '2026-10-05 07:20:44'),
(75, 'SzgMDMNv_222_29233', 5, '(●\'◡\'●)╰(*°▽°*)╯(^///^)', '2026-10-05 07:21:00'),
(76, 'KNFxfAXU_117_83331', 5, 'Hvorfor ser æ mærk tema stadig æ dårligt ud???', '2026-10-05 07:22:07'),
(77, 'SzgMDMNv_222_29233', 5, 'æ fordi æ stender ik æææhh har lavet det æææ nu', '2026-10-05 07:22:29'),
(78, 'SzgMDMNv_222_29233', 5, 'ham med det hvide hår.... We like white :)', '2026-10-05 07:23:32'),
(79, 'KNFxfAXU_117_83331', 5, 'True White = godt\nAlt andet = Ikke godt', '2026-10-05 07:24:31'),
(80, 'SzgMDMNv_222_29233', 5, '👀😂', '2026-10-05 07:24:50'),
(81, 'KNFxfAXU_117_83331', 5, 'Men det jo true', '2026-10-05 07:25:00'),
(82, 'SzgMDMNv_222_29233', 5, 'det er dig der siger det, ik mig👀', '2026-10-05 07:25:15'),
(83, 'KNFxfAXU_117_83331', 5, 'det fleste ville være æ enig', '2026-10-05 07:25:38'),
(84, 'SzgMDMNv_222_29233', 5, 'Kan vi lige få VAR check på hvorfor alle 5 er kvinder?', '2026-10-05 07:26:17'),
(85, 'KNFxfAXU_117_83331', 5, 'Racisme eler noget diskrimination', '2026-10-05 07:26:33'),
(86, 'SzgMDMNv_222_29233', 5, 'Like don\'t get me wrong, men ik kom og sig top 5 forskere i dk alle er women', '2026-10-05 07:26:50'),
(87, 'KNFxfAXU_117_83331', 5, '\"unbiased\" Jury\nja totalt', '2026-10-05 07:27:07'),
(88, 'SzgMDMNv_222_29233', 5, 'like, umugligt der ikke bare er en hankøn', '2026-10-05 07:27:14'),
(89, 'SzgMDMNv_222_29233', 5, '\"\"unbiased\" Jury\"\nDet jo det😂🤣', '2026-10-05 07:27:33'),
(90, 'KNFxfAXU_117_83331', 5, 'hva snakker han om???', '2026-10-05 07:27:50'),
(91, 'SzgMDMNv_222_29233', 5, '<b>test</b>', '2026-10-05 07:27:57'),
(92, 'SzgMDMNv_222_29233', 5, ':(', '2026-10-05 07:28:03'),
(93, 'SzgMDMNv_222_29233', 5, '\"hva snakker han om???\"\n\npas, ingen ide', '2026-10-05 07:28:14'),
(94, 'KNFxfAXU_117_83331', 5, 'jeg ville give gråhår min stemme\nog en anden mulighed\nmen det er i et andet rum', '2026-10-05 07:28:58'),
(95, 'SzgMDMNv_222_29233', 5, '😂😂', '2026-10-05 07:29:27'),
(96, 'KNFxfAXU_117_83331', 5, 'GayNation', '2026-10-05 07:29:36'),
(97, 'SzgMDMNv_222_29233', 5, 'hallå, skal vi arbejde med det her efter?', '2026-10-05 07:29:46'),
(98, 'SzgMDMNv_222_29233', 5, 'Altså, burde vi følge med', '2026-10-05 07:30:02'),
(99, 'KNFxfAXU_117_83331', 5, 'ingen idé', '2026-10-05 07:30:22'),
(100, 'SzgMDMNv_222_29233', 5, 'tager jeg som et nej så', '2026-10-05 07:30:49'),
(101, 'KNFxfAXU_117_83331', 5, 'elsker lyd- og billedkvaliteten', '2026-10-05 07:31:09'),
(102, 'SzgMDMNv_222_29233', 5, 'true, det er fantastisk billede', '2026-10-05 07:31:48'),
(103, 'SzgMDMNv_222_29233', 5, 'Men øm, hvorfor blir de coatchet??', '2026-10-05 07:32:02'),
(104, 'KNFxfAXU_117_83331', 5, 'hvor sku jeg vide æ fra', '2026-10-05 07:32:52'),
(105, 'SzgMDMNv_222_29233', 5, 'fuck det lyder som noget chat kunne have skrevet', '2026-10-05 07:32:56'),
(106, 'KNFxfAXU_117_83331', 5, 'True 😂😂😂', '2026-10-05 07:33:12'),
(107, 'KNFxfAXU_117_83331', 5, 'med tragtindlendingen og dte hele', '2026-10-05 07:33:22'),
(108, 'SzgMDMNv_222_29233', 5, 'det jo det, det lyder fucking meget som chat', '2026-10-05 07:33:55'),
(109, 'KNFxfAXU_117_83331', 5, '\"Jeg\" er vildt at sige så mange gange når hun har skrevet det med chat', '2026-10-05 07:34:43'),
(110, 'SzgMDMNv_222_29233', 5, 'det jo det, hun har virkelig cooket med mr. gpt', '2026-10-05 07:35:16'),
(111, 'KNFxfAXU_117_83331', 5, 'som om hun har været i guyana for at kigge på myre\nden er direkte taget med chat som har fundet en eller anden udenlansk hjemmeside', '2026-10-05 07:36:27'),
(112, 'SzgMDMNv_222_29233', 5, 'altså, hendes forskning er \"meaby\" real nok, men hendes tale er fandme chat og mer chat', '2026-10-05 07:37:12'),
(113, 'SzgMDMNv_222_29233', 5, 'hende der dommeren skal bare lukke røven', '2026-10-05 07:38:09'),
(114, 'SzgMDMNv_222_29233', 5, 'bro, det var ren chat', '2026-10-05 07:38:22'),
(115, 'KNFxfAXU_117_83331', 5, 'præcis´ mine år', '2026-10-05 07:38:33'),
(116, 'KNFxfAXU_117_83331', 5, 'hvorfor taler de om sløjfer nu???', '2026-10-05 07:38:43'),
(117, 'SzgMDMNv_222_29233', 5, 'aner det ik😭😭😭😭', '2026-10-05 07:39:05'),
(118, 'KNFxfAXU_117_83331', 5, 'det er noget for dig det her Noha', '2026-10-05 07:39:28'),
(119, 'KNFxfAXU_117_83331', 5, 'hun forsker for at løse din fedme', '2026-10-05 07:39:47'),
(120, 'SzgMDMNv_222_29233', 5, 'shut up bitch ass', '2026-10-05 07:39:48'),
(121, 'SzgMDMNv_222_29233', 5, 'jeg er ik fed - jeg er tyk :)', '2026-10-05 07:40:07'),
(122, 'KNFxfAXU_117_83331', 5, 'du er SMÆLLERFED', '2026-10-05 07:40:18'),
(123, 'SzgMDMNv_222_29233', 5, 'altså, jeg vejer jo faktisk kun 1 ton jo', '2026-10-05 07:40:27'),
(124, 'KNFxfAXU_117_83331', 5, 'åårrrhh er du nede med 1/2 ton\n😮😮😮', '2026-10-05 07:41:00'),
(125, 'SzgMDMNv_222_29233', 5, 'se, vi er 6 personer herinde der er bolle fede', '2026-10-05 07:41:08'),
(126, 'SzgMDMNv_222_29233', 5, '\"åårrrhh er du nede med 1/2 ton\"\n\nhmmm, jeg har faktisk tabt mig', '2026-10-05 07:41:30'),
(127, 'KNFxfAXU_117_83331', 5, 'hvem', '2026-10-05 07:41:31'),
(128, 'SzgMDMNv_222_29233', 5, 'idk, hun sagde 1/5 er bolle fed', '2026-10-05 07:41:49'),
(129, 'SzgMDMNv_222_29233', 5, 'og er vi ik 30?', '2026-10-05 07:41:59'),
(130, 'SzgMDMNv_222_29233', 5, 'cirka', '2026-10-05 07:42:03'),
(131, 'KNFxfAXU_117_83331', 5, 'jeg tror hun har taget gennemsnittet\nog du vejer gennemsnittet nok op herinde', '2026-10-05 07:42:17'),
(132, 'SzgMDMNv_222_29233', 5, 'stfu', '2026-10-05 07:42:36'),
(133, 'KNFxfAXU_117_83331', 5, 'average kom/Pro Noha børnelover', '2026-10-05 07:43:10'),
(134, 'SzgMDMNv_222_29233', 5, 'luk', '2026-10-05 07:43:46'),
(135, 'KNFxfAXU_117_83331', 5, '+ a sprinkle of GAY', '2026-10-05 07:43:57'),
(136, 'SzgMDMNv_222_29233', 5, '😘😘', '2026-10-05 07:44:42'),
(137, 'KNFxfAXU_117_83331', 5, '😮😮😮😮😮', '2026-10-05 07:44:50'),
(138, 'SzgMDMNv_222_29233', 5, 'hvorfor er der børn med nu????', '2026-10-05 07:45:48'),
(139, 'KNFxfAXU_117_83331', 5, 'kig væk Noha det er 6-klasser', '2026-10-05 07:45:59'),
(140, 'SzgMDMNv_222_29233', 5, 'hey, jeg er kun til 7. og up :)', '2026-10-05 07:46:17'),
(141, 'KNFxfAXU_117_83331', 5, 'nå jeg er til 7 og ned ;)', '2026-10-05 07:46:33'),
(142, 'KNFxfAXU_117_83331', 5, 'så vi kan godt splitte', '2026-10-05 07:46:40'),
(143, 'SzgMDMNv_222_29233', 5, '😂😂', '2026-10-05 07:46:44'),
(144, 'SzgMDMNv_222_29233', 5, 'Okay, jeg tager alle 7. klasses piger, så må du få alle 7. klasses drenge så', '2026-10-05 07:47:07'),
(145, 'KNFxfAXU_117_83331', 5, 'hun woke hende der', '2026-10-05 07:47:09'),
(146, 'SzgMDMNv_222_29233', 5, 'yea, fuck hende der', '2026-10-05 07:47:27'),
(147, 'SzgMDMNv_222_29233', 5, 'og fuck hendes forskning', '2026-10-05 07:47:35'),
(148, 'SzgMDMNv_222_29233', 5, 'hvorfor fuck har hun lavet et dict?!?!?!?!?', '2026-10-05 07:47:58'),
(149, 'KNFxfAXU_117_83331', 5, 'HUn er IK stephen hawking', '2026-10-05 07:48:17'),
(150, 'SzgMDMNv_222_29233', 5, 'bro, stepen hawking er crazyyyy', '2026-10-05 07:48:18'),
(151, 'KNFxfAXU_117_83331', 5, 'Hun er defekt i hjernen', '2026-10-05 07:48:35'),
(152, 'SzgMDMNv_222_29233', 5, 'fandme true', '2026-10-05 07:48:43'),
(153, 'SzgMDMNv_222_29233', 5, 'hun er præcis modsat af stephen', '2026-10-05 07:48:53'),
(154, 'SzgMDMNv_222_29233', 5, 'hjeren er defekt her', '2026-10-05 07:49:04'),
(155, 'KNFxfAXU_117_83331', 5, 'hun snakker Crazy lang tid', '2026-10-05 07:49:55'),
(156, 'KNFxfAXU_117_83331', 5, 'sluk nu for mikrofonen', '2026-10-05 07:50:04'),
(157, 'KNFxfAXU_117_83331', 5, 'Hun er et stort handikap', '2026-10-05 07:50:19'),
(158, 'SzgMDMNv_222_29233', 5, '🎤😔', '2026-10-05 07:51:02'),
(159, 'KNFxfAXU_117_83331', 5, 'tilbage i køkkenet🍳🍳', '2026-10-05 07:51:23'),
(160, 'SzgMDMNv_222_29233', 5, '💀😂🤣', '2026-10-05 07:51:44'),
(161, 'KNFxfAXU_117_83331', 5, 'Ren propagande tale goebbels style', '2026-10-05 07:52:03'),
(162, 'SzgMDMNv_222_29233', 5, 'bro, hvis den her chat blir leaked, så bli\'r vi canseled', '2026-10-05 07:52:10'),
(163, 'SzgMDMNv_222_29233', 5, '\"Ren propagande tale goebbels style\"\n\ntrue', '2026-10-05 07:52:21'),
(164, 'KNFxfAXU_117_83331', 5, 'bare rolig de ved ikke at du bor i taps og jeg bor i bramdrup', '2026-10-05 07:52:31'),
(165, 'SzgMDMNv_222_29233', 5, 'fuck du er dum jo🤣', '2026-10-05 07:52:49'),
(166, 'KNFxfAXU_117_83331', 5, 'og du arbejder på carls', '2026-10-05 07:53:20'),
(167, 'SzgMDMNv_222_29233', 5, 'alle elsker carls', '2026-10-05 07:53:28'),
(168, 'SzgMDMNv_222_29233', 5, 'som de selv siger, så er det \"noget andet\"🤣', '2026-10-05 07:53:50'),
(169, 'KNFxfAXU_117_83331', 5, 'alle dem der skal af med nogen elsker carls\nfor det er et sted hvor seriemordere lovligt kan afskaffe nogen med den mad der', '2026-10-05 07:54:11'),
(170, 'SzgMDMNv_222_29233', 5, 'wtf😶', '2026-10-05 07:54:48'),
(171, 'KNFxfAXU_117_83331', 5, 'true story\ntrue story', '2026-10-05 07:55:01'),
(172, 'KNFxfAXU_117_83331', 5, 'jeg bruger selv ordningen et par gange ugentligt', '2026-10-05 07:55:18'),
(173, 'SzgMDMNv_222_29233', 5, 'wait, er hun pregnat hende der?', '2026-10-05 07:55:21'),
(174, 'SzgMDMNv_222_29233', 5, 'crazyyy ngl', '2026-10-05 07:55:38'),
(175, 'KNFxfAXU_117_83331', 5, 'hun er fed eller fed med barn indeni', '2026-10-05 07:55:55'),
(176, 'SzgMDMNv_222_29233', 5, 'true', '2026-10-05 07:56:08'),
(177, 'SzgMDMNv_222_29233', 5, 'men hvorfor, ligner hun en der er gravid på klippet. Men nu er hun tynd som en strej?', '2026-10-05 07:56:44'),
(178, 'SzgMDMNv_222_29233', 5, 'eller wait, nvm', '2026-10-05 07:56:57'),
(179, 'KNFxfAXU_117_83331', 5, 'jeg er brandværm somd en der skovbrand', '2026-10-05 07:57:00'),
(180, 'KNFxfAXU_117_83331', 5, 'kirurgi', '2026-10-05 07:57:15'),
(181, 'SzgMDMNv_222_29233', 5, '🌲🔥', '2026-10-05 07:57:20'),
(182, 'KNFxfAXU_117_83331', 5, 'why no giff???', '2026-10-05 07:57:58'),
(183, 'SzgMDMNv_222_29233', 5, 'hun er programør? Eyyy yooo, meaby future wifi.', '2026-10-05 07:58:11'),
(184, 'SzgMDMNv_222_29233', 5, '\"why no giff???\"\n\ncause', '2026-10-05 07:58:25'),
(185, 'KNFxfAXU_117_83331', 5, 'hun blev nok bolled dagen før det første klip', '2026-10-05 07:59:37'),
(186, 'SzgMDMNv_222_29233', 5, 'true\n\nuhhh daddyyyy', '2026-10-05 08:00:13'),
(187, 'KNFxfAXU_117_83331', 5, 'hun er 30 år\ndouble it and give it to the next person', '2026-10-05 08:00:18'),
(188, 'SzgMDMNv_222_29233', 5, '😂🤣😂', '2026-10-05 08:01:10'),
(189, 'KNFxfAXU_117_83331', 5, 'håb staves \"h\" \"å\" \"b\"', '2026-10-05 08:02:18'),
(190, 'SzgMDMNv_222_29233', 5, 'hvad skrev de?', '2026-10-05 08:02:36'),
(191, 'KNFxfAXU_117_83331', 5, 'håb er noget man kan føle\nmen det er ik virkelighed', '2026-10-05 08:02:56'),
(192, 'SzgMDMNv_222_29233', 5, 'hmmmm!🙌', '2026-10-05 08:03:50'),
(193, 'KNFxfAXU_117_83331', 5, '\"kun den der tør skyde, rammer nogensinde plet\" -Fred_The_King', '2026-10-05 08:04:10'),
(194, 'KNFxfAXU_117_83331', 5, 'Hendes undergrad var sikkert økonomi', '2026-10-05 08:04:42'),
(195, 'SzgMDMNv_222_29233', 5, 'Hmmm, true. Så burde du skyde lidt oftorere', '2026-10-05 08:04:49'),
(196, 'KNFxfAXU_117_83331', 5, 'hun taler om banker og om at hæve penge', '2026-10-05 08:05:10'),
(197, 'KNFxfAXU_117_83331', 5, 'hvis man udvider sin målgruppe har man en større skydeskive', '2026-10-05 08:05:35'),
(198, 'SzgMDMNv_222_29233', 5, 'damm, hun er god til de æg det. Heldigvis så er hende den tilligere forsker ik helt så fucked som de piger hun snakker om', '2026-10-05 08:06:44'),
(199, 'KNFxfAXU_117_83331', 5, 'Truce Noha Gay', '2026-10-05 08:07:25'),
(200, 'jONxChXF_377_49315', 5, 'Fucking GOOOOONER', '2026-10-06 09:06:12'),
(201, 'SzgMDMNv_222_29233', 5, 'Eyyy', '2026-10-06 09:06:37'),
(202, 'SzgMDMNv_222_29233', 5, 'kommentar original', '2026-10-06 09:08:42'),
(203, 'jONxChXF_377_49315', 5, 'fedt man Døbefond', '2026-10-06 09:09:06'),
(204, 'SzgMDMNv_222_29233', 5, 'Yaassssss Queniii', '2026-10-06 09:09:52'),
(205, 'SzgMDMNv_222_29233', 5, 'test', '2026-10-06 09:51:42'),
(206, 'PBOVBpOj_394_19612', 5, 'Hello\nJeg elsker James Bald, han aer mit idol', '2026-10-06 11:37:03'),
(207, 'jONxChXF_377_49315', 6, 'BotnakBenny', '2026-10-06 12:23:00'),
(208, 'CFSPBldz_980_70494', 6, 'AA', '2026-10-07 07:07:05'),
(209, 'bYGvCPgf_635_17797', 7, 'Nu skal vi huske at han er fascineret af børnehaver så jeg tænker en mooncar', '2026-10-07 07:09:18'),
(210, 'pFUgnHjh_338_15996', 7, 'OMG JA, Lwk. Elsker at køre mooncar :)', '2026-10-07 07:10:12'),
(211, 'CFSPBldz_980_70494', 7, 'HIDWHAI=DJW=AJD=)WJAI=JDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDD\'', '2026-10-07 07:15:06'),
(212, 'PBOVBpOj_394_19612', 7, 'Fandt Noah\nhttps://www.youtube.com/watch?v=ivCNMXsPk5M', '2026-10-07 07:15:28'),
(213, 'PBOVBpOj_394_19612', 7, 'Fandt Noah\nhttps://www.youtube.com/watch?v=ivCNMXsPk5M', '2026-10-07 07:16:15'),
(214, 'bYGvCPgf_635_17797', 7, 'goon', '2026-10-07 07:17:58'),
(215, 'pFUgnHjh_338_15996', 7, 'Yea dude, vi læste det godt første gang :)😘', '2026-10-07 07:21:15'),
(216, 'CFSPBldz_980_70494', 6, 'AA', '2026-10-07 07:23:56'),
(217, 'CFSPBldz_980_70494', 5, 'AADWA\n\'', '2026-10-07 07:25:06'),
(218, 'CFSPBldz_980_70494', 6, 'ABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCvABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCABCA', '2026-10-07 07:41:25');

-- --------------------------------------------------------

--
-- Table structure for table `Interactions`
--

CREATE TABLE `Interactions` (
  `interactionId` int UNSIGNED NOT NULL,
  `interactionType` tinyint UNSIGNED NOT NULL COMMENT '1 upvote, 2 downvote',
  `postId` int NOT NULL,
  `userId` char(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Interactions`
--

INSERT INTO `Interactions` (`interactionId`, `interactionType`, `postId`, `userId`) VALUES
(2, 1, 7, 'pFUgnHjh_338_15996');

-- --------------------------------------------------------

--
-- Table structure for table `Posts`
--

CREATE TABLE `Posts` (
  `postId` int NOT NULL,
  `userId` char(18) NOT NULL,
  `channelId` int NOT NULL,
  `postTitle` varchar(60) NOT NULL DEFAULT 'Untitled',
  `postContent` text NOT NULL,
  `timeStamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Posts`
--

INSERT INTO `Posts` (`postId`, `userId`, `channelId`, `postTitle`, `postContent`, `timeStamp`) VALUES
(1, 'rZaakVLZ_842_37212', 5, 'Dette er en test', '', '2026-09-08 08:29:02'),
(2, 'SHyZRXdL_843_65137', 14, 'dette er en title', 'testsadasdasdassd', '2026-10-04 20:52:40'),
(3, 'SHyZRXdL_843_65137', 5, 'test', 'Dette er bare en lille test', '2026-10-04 22:08:39'),
(4, 'SHyZRXdL_843_65137', 5, 'sadasda', 'test vbkjashdjas', '2026-10-04 22:09:23'),
(5, 'SzgMDMNv_222_29233', 18, 'The name is Bond\r', 'Så fred, så er det herinde man skriver...  Igos', '2026-10-05 06:15:11'),
(6, 'WRUShLnN_485_32579', 2, 'Action', 'Testdummy ift. indlæg', '2026-10-06 10:02:46'),
(7, 'PBOVBpOj_394_19612', 23, 'Hvad er dit yndlings legetøj?', 'Jeg er bare lidt nysgerrig på hvad dit yndlings legetøj er, og det er helt ok hvis det er et voksent legetøj. Ingen hate eller Shame', '2026-10-07 07:08:38');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `userId` char(18) NOT NULL,
  `userName` varchar(50) NOT NULL,
  `lastSeen` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`userId`, `userName`, `lastSeen`) VALUES
('aTXHhdHa_184_50376', 'hejehj#50376', '2026-10-04 20:12:00'),
('bYGvCPgf_635_17797', 'dinmorENTOTRE#17797', '2026-10-07 07:53:00'),
('CFSPBldz_980_70494', 'TestdummyQ#70494', '2026-10-07 07:52:35'),
('GuMesbUw_804_11834', 'Simon#11834', '2026-09-07 10:17:06'),
('HLUzsTmy_923_82442', 'Noah#82442', '2026-09-07 08:48:26'),
('jONxChXF_377_49315', 'dinmorENTOTRE#49315', '2026-10-06 12:25:51'),
('KNFxfAXU_117_83331', 'Fred_The_King#83331', '2026-10-05 08:07:25'),
('noPXUrlK_546_34953', 'Dmytro#34953', '2026-09-22 07:15:53'),
('PBOVBpOj_394_19612', 'G-CharlieKirkTheKing#19612', '2026-10-07 07:45:15'),
('pFUgnHjh_338_15996', 'Noha_Gay#15996', '2026-10-07 07:42:35'),
('PiQVNKoY_281_42958', 'HansHansen#42958', '2026-10-05 09:34:09'),
('rZaakVLZ_842_37212', 'nonie#37212', '2026-09-21 11:01:34'),
('sFKIwfnM_989_44677', 'Testdummymobile#44677', '2026-10-07 07:20:28'),
('SHyZRXdL_843_65137', 'G-sadasdsa#65137', '2026-10-04 22:25:06'),
('SzgMDMNv_222_29233', 'Noha_GAYYY#29233', '2026-10-07 06:38:11'),
('ttxGNuhF_445_10491', 'ABC#10491', '2026-09-07 10:37:21'),
('VwWhYqkT_802_25054', 'CharlieKirkTheKing#25054', '2026-09-22 16:23:23'),
('WeNbCERt_399_62208', 'NIGGER#62208', '2026-09-22 10:08:44'),
('WRUShLnN_485_32579', 'Testdummy#32579', '2026-10-06 12:29:19'),
('wZwPCOKB_063_36140', 'Noah#36140', '2026-09-04 07:59:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Attachments`
--
ALTER TABLE `Attachments`
  ADD PRIMARY KEY (`attachmentId`),
  ADD KEY `attachmentType` (`attachmentType`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `Channels`
--
ALTER TABLE `Channels`
  ADD PRIMARY KEY (`channelId`),
  ADD KEY `fk_owner_channel` (`ownerChannelId`),
  ADD KEY `fk_channel_creator` (`channelCreator`),
  ADD KEY `channelName` (`channelName`);

--
-- Indexes for table `Comments`
--
ALTER TABLE `Comments`
  ADD PRIMARY KEY (`commentId`),
  ADD KEY `fk_comment_user` (`userId`),
  ADD KEY `fk_comment_post` (`ownerPostId`);

--
-- Indexes for table `Interactions`
--
ALTER TABLE `Interactions`
  ADD PRIMARY KEY (`interactionId`),
  ADD UNIQUE KEY `uniqueVotePerUser` (`postId`,`userId`),
  ADD KEY `interactionBy` (`userId`);

--
-- Indexes for table `Posts`
--
ALTER TABLE `Posts`
  ADD PRIMARY KEY (`postId`),
  ADD KEY `fk_post_user` (`userId`),
  ADD KEY `fk_post_channel` (`channelId`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`userId`),
  ADD KEY `userName` (`userName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Attachments`
--
ALTER TABLE `Attachments`
  MODIFY `attachmentId` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Channels`
--
ALTER TABLE `Channels`
  MODIFY `channelId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `Comments`
--
ALTER TABLE `Comments`
  MODIFY `commentId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `Interactions`
--
ALTER TABLE `Interactions`
  MODIFY `interactionId` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Posts`
--
ALTER TABLE `Posts`
  MODIFY `postId` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Attachments`
--
ALTER TABLE `Attachments`
  ADD CONSTRAINT `fk_attachments_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Channels`
--
ALTER TABLE `Channels`
  ADD CONSTRAINT `fk_channel_creator` FOREIGN KEY (`channelCreator`) REFERENCES `Users` (`userId`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_owner_channel` FOREIGN KEY (`ownerChannelId`) REFERENCES `Channels` (`channelId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `Comments`
--
ALTER TABLE `Comments`
  ADD CONSTRAINT `fk_comment_post` FOREIGN KEY (`ownerPostId`) REFERENCES `Posts` (`postId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comment_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Interactions`
--
ALTER TABLE `Interactions`
  ADD CONSTRAINT `fk_interactions_post` FOREIGN KEY (`postId`) REFERENCES `Posts` (`postId`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_interactions_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE;

--
-- Constraints for table `Posts`
--
ALTER TABLE `Posts`
  ADD CONSTRAINT `fk_post_channel` FOREIGN KEY (`channelId`) REFERENCES `Channels` (`channelId`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_post_user` FOREIGN KEY (`userId`) REFERENCES `Users` (`userId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
