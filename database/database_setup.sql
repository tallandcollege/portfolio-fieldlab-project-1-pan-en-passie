DROP DATABASE IF EXISTS pan_en_passie;
CREATE DATABASE pan_en_passie 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE pan_en_passie;


CREATE TABLE `aanvulling` (
  `id` int(11) NOT NULL,
  `Recipe_id` int(11) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `aanvulling`
--

INSERT INTO `aanvulling` (`id`, `Recipe_id`, `description`) VALUES
(1, 14, 'Serveer de parfait op een crumble, dat voorkomt dat de parfait direct gaat smelten op het bord!'),
(2, 12, 'Niet te lang mengen, anders wordt de puree taai!'),
(3, 3, 'Denk erom dat ze niet aanbranden.'),
(4, 2, 'Probeer de olie bovenop de bestanddelen te krijgen.');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `category`
--

CREATE TABLE `category` (
  `categoryID` int(11) NOT NULL,
  `category` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `category`
--

INSERT INTO `category` (`categoryID`, `category`) VALUES
(1, 'vlees'),
(2, 'groente'),
(3, 'vis'),
(4, 'saus'),
(5, 'specerij'),
(6, 'smaakmaker'),
(7, 'zuivel'),
(8, 'ei'),
(9, 'azijn'),
(10, 'olie'),
(11, 'anders'),
(12, 'kruid'),
(13, 'gerecht'),
(14, 'wijn'),
(15, 'fruit'),
(16, 'noten'),
(17, 'meel'),
(18, 'invullen');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `class`
--

CREATE TABLE `class` (
  `id` int(11) NOT NULL,
  `classname` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `maxstudents` int(11) DEFAULT NULL,
  `createdat` timestamp NOT NULL DEFAULT current_timestamp(),
  `createrecipeperms` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `class`
--

INSERT INTO `class` (`id`, `classname`, `description`, `maxstudents`, `createdat`, `createrecipeperms`) VALUES
(1, 'aeito23sd', 'hele coole klas', 20, '2026-01-29 16:07:39', 1),
(2, 'Zieke klas!', 'dit is wel de coolste klas', 50, '2026-01-30 09:25:10', 1),
(3, 'Len klas', 'wow', 4, '2026-01-30 10:40:19', 1),
(4, 'test', 'test', 2, '2026-01-30 10:44:44', 0),
(5, 'Abdis rubberen boot', 'mooi', 24, '2026-02-06 12:07:33', 1),
(6, 'test klas', 'abdi test 1', 2147483647, '2026-02-06 12:08:04', 0),
(7, 'abdi test 2', '', 100000, '2026-02-06 12:09:56', 0),
(8, 'abdi test 3', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque accumsan et ex sit amet ullamcorper. Sed sem sapien, sollicitudin molestie velit eu, malesuada mollis tellus. Integer maximus augue libero, ac vehicula nulla viverra non. Nullam vitae magna et justo tempor lacinia. Fusce tortor lacus, ornare eu tempus ut, placerat vitae libero. Quisque luctus, dui sit amet suscipit porttitor, risus dui commodo eros, vitae eleifend diam lectus ut justo. Mauris interdum magna in erat congue fringilla.\r\n\r\nDonec venenatis pretium tortor, commodo semper erat commodo sit amet. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Maecenas eu arcu volutpat, ullamcorper ante nec, lobortis lacus. Donec commodo nulla neque. Curabitur sed lectus laoreet, vestibulum turpis vel, iaculis nisl. Maecenas dapibus ullamcorper massa sit amet maximus. Integer et orci ullamcorper, mattis dolor nec, suscipit enim. Quisque nec rutrum elit. Aliquam erat volutpat.\r\n\r\nInteger non purus tellus. Cras sit amet purus non mauris vehicula ullamcorper sed id tellus. Mauris non felis sed purus placerat pharetra. Praesent varius est ac sodales ornare. Sed sed metus elementum, vestibulum tellus et, ullamcorper neque. Phasellus id turpis ac lectus tincidunt ullamcorper nec nec metus. Etiam scelerisque tempus eros, vel volutpat ligula fringilla id. Proin condimentum, nisi a facilisis accumsan, purus lacus ornare massa, at ullamcorper ipsum ipsum a dolor. Ut est ante, volutpat venenatis ullamcorper at, pretium quis nisi. Integer mattis sagittis enim, vitae tempus ligula imperdiet vel. Nam in maximus felis. Fusce dapibus, velit et dictum mattis, lectus sem rutrum ipsum, sed luctus augue eros quis sem. Nullam accumsan ligula libero. Integer vitae nibh sed mi fringilla rhoncus. In ac vestibulum est, sit amet sodales turpis.\r\n\r\nNulla dapibus justo sem, ut varius justo porta et. Nam eget euismod leo. Proin a efficitur libero, vehicula dictum nisi. Nulla a augue et lectus varius blandit ac et nisi. Nullam sed vestibulum sem. Pellentesque eu ex dictum, rutrum odio a, blandit nulla. Nunc lobortis tincidunt arcu ultricies mollis. Donec blandit neque a enim tempus, sit amet laoreet turpis ornare. Aliquam et tincidunt nulla. Etiam non ex sed nibh luctus tempor in eget tellus. Nulla sed euismod dolor. Aenean vehicula varius mattis. Vivamus finibus aliquet ante, at finibus est finibus sed. Donec non mi nisl. Nam mattis turpis gravida rhoncus cursus.\r\n\r\nVestibulum scelerisque, ipsum sed tincidunt tincidunt, ipsum justo faucibus libero, eu interdum lorem diam id lectus. Sed nec justo commodo felis pellentesque lobortis vitae a mi. Etiam ullamcorper blandit risus, eget posuere nisl dictum sit amet. Donec tempor mattis mi. Sed suscipit massa et augue condimentum pharetra. Vestibulum congue, dolor feugiat ultricies semper, elit dui pellentesque massa, sed rhoncus mauris metus sit amet diam. Aliquam sit amet tortor non justo fermentum lacinia eu in diam. Integer maximus volutpat enim, a scelerisque dolor imperdiet sed. Nam condimentum ac diam ut maximus. Nunc malesuada ante justo, eget pellentesque erat maximus vel. Integer lobortis vitae est eu sollicitudin. Cras cursus turpis nisi, vel congue ante pharetra a. Curabitur congue leo et nisi eleifend sodales. Cras luctus pellentesque felis vel lobortis.', 999, '2026-02-06 12:11:52', 0),
(9, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque accumsan et ex sit amet ullamcorper. Sed sem sapien, sollicitudin molestie velit eu, malesuada mollis tellus. Integer maximus augue libero, ac vehicula nulla viverra non. Nullam vitae magna ', 'hele lange nasam', 999, '2026-02-06 12:12:15', 0),
(10, 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Quisque accumsan et ex sit amet ullamcorper. Sed sem sapien, sollicitudin molestie velit eu, malesuada mollis tellus. Integer maximus augue libero, ac vehicula nulla viverra non. Nullam vitae magna ', 'wow', 24, '2026-02-06 12:15:52', 0);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `ingredient`
--

CREATE TABLE `ingredient` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `ingredient`
--

INSERT INTO `ingredient` (`id`, `name`, `categoryID`) VALUES
(1, 'Kalfs ribeye', 1),
(2, 'Tijm', 12),
(3, 'Rozemarijn', 12),
(4, 'Olijfolie', 10),
(5, 'Knoflook', 2),
(6, 'Mosterd', 6),
(7, 'Ei', 8),
(8, 'Kappertjes', 6),
(9, 'Sushi azijn', 9),
(10, 'Ansjovisfilet', 3),
(11, 'Limoensap', 6),
(12, 'Water', 11),
(13, 'Zonnebloemolie', 10),
(14, 'Peper', 5),
(15, 'Zout', 5),
(16, 'Worcestersaus', 4),
(17, 'Rode peper', 5),
(18, 'Harde geitenkaas', 7),
(19, 'Komkommer', 2),
(20, 'Citroensap', 6),
(21, 'Bietensap', 2),
(22, 'Grote rauwe rode bieten', 2),
(23, 'Keukentouw/cocktailprikker', 11),
(24, 'Peterselie', 12),
(25, 'Seru dashi', 6),
(26, 'Witte wijn', 14),
(27, 'Sjalotten', 2),
(28, 'Slagroom', 7),
(29, 'Koude boter', 7),
(30, 'Kikkoman sojasaus', 4),
(31, 'Schorseneren', 2),
(32, 'Biet millefeuille', 13),
(33, 'Soja beurre blanc', 13),
(34, 'Peterselie olie', 13),
(35, 'Shiso green', 12),
(36, 'Chips van schorseneren', 13),
(37, 'Maiskipfilet m/v a 150 gram', 1),
(38, 'Vadouvan kruiden', 12),
(39, 'Kastanjechampignons', 2),
(40, 'Oesterzwammen', 2),
(41, 'Shiitake', 2),
(42, 'Sjalotjes', 2),
(43, 'Witlof', 2),
(44, 'Honing', 6),
(45, 'Aceto balsamico', 9),
(46, 'Aardappelen', 2),
(47, 'Boter', 7),
(48, 'Jus de veau', 6),
(49, 'Roomboter', 7),
(50, 'Bananenpuree (Boiron)', 6),
(51, 'Suiker', 6),
(52, 'Eidooier', 8),
(53, 'Gelatine', 11),
(54, 'Passievruchtenpuree', 6),
(55, 'Eigeel gepasteuriseerd', 8),
(56, 'Mokka extract', 6),
(57, 'Melk chocolade', 6),
(58, 'Room', 7),
(59, 'Bananen', 15),
(60, 'Kaneelpoeder', 5),
(61, 'Wonton velletjes', 11),
(62, 'Amandelpoeder', 16),
(63, 'Bloem', 17),
(64, 'Cacaopoeder', 6),
(65, 'Grof zeezout', 5);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `material`
--

CREATE TABLE `material` (
  `id` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `material`
--

INSERT INTO `material` (`id`, `Name`) VALUES
(1, 'Bolzeef'),
(2, 'Bekken'),
(3, 'Garde'),
(4, 'Officemes'),
(5, 'Diepe gastronormbak'),
(6, 'Chinese mandoline'),
(7, 'Passeerdoek'),
(8, 'Thermoblender'),
(9, 'Spuitflesje'),
(10, 'Kookpan'),
(11, 'Dunschiller'),
(12, 'Vergiet'),
(13, 'Keukenpapier'),
(14, 'RÃ¶ner'),
(15, 'Vacumeer zakken'),
(16, 'Koffiemolen'),
(17, 'Passe-vite'),
(18, 'Spatel'),
(19, 'Steelpan'),
(20, 'Sauspan'),
(21, 'Pollepel'),
(22, 'Temperatuurmeter'),
(23, 'Siliconen vorm'),
(24, 'Zeef'),
(25, 'Afruimbak'),
(26, 'Staafmixer'),
(27, 'Spuitzak'),
(28, 'Kitchen Aid'),
(29, 'Magic Mix'),
(30, 'Snijplank'),
(31, 'Koksmes'),
(32, 'Koekenpan'),
(33, 'Grillpan'),
(34, 'Slagerstouw'),
(35, 'Litermaat');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `nan_account`
--

CREATE TABLE `nan_account` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `UserKey` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `nan_account`
--

INSERT INTO `nan_account` (`id`, `firstname`, `lastname`, `username`, `email`, `UserKey`, `role_id`) VALUES
(1, 'Lennard', 'Straks', 'Lennard.Straks', 'ls.straks@gmail.com', '828f14b54d0d61f139efebe24578ef4b504913845988a3e07308120d43394567', 1),
(2, 'Leonard', 'Straks', 'Leonard.Straks', 'ls.strak@gmail.com', '700f788cc7c67de53f3d8ae3e011e3d050cd181123a26485348ffc103439c125', 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `recipe`
--

CREATE TABLE `recipe` (
  `id` int(11) NOT NULL,
  `User_id` int(11) DEFAULT NULL,
  `Class_id` int(11) DEFAULT NULL,
  `Name` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Instructions` text DEFAULT NULL,
  `Createdat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `recipe`
--

INSERT INTO `recipe` (`id`, `User_id`, `Class_id`, `Name`, `Description`, `Instructions`, `Createdat`) VALUES
(1, NULL, NULL, 'Kalfs ribeye', 'Voorgerecht: Aantal personen: 20', 'Verwarm de oven voor op 80Â°C.\nSmeer de rib-eye in met olijfolie.\nPel en hak de knoflook. Smeer de knoflook op de rib-eye.\nBind met slagerstouw de tijm en de rozemarijn om de rib-eye.\nGrill de rib-eye om en om in een grillpan. Gaar de rib-eye verder in de oven op kerntemperatuur 54Â°C.\nLaat het vlees afkoelen.', '2026-01-15 15:46:28'),
(2, NULL, NULL, 'Ansjovis mayonaise', 'Voorgerecht: Aantal personen: 20', 'Doe de mosterd, het ei, de sushi azijn, het limoensap, kappertjes, ansjovis en het water in een litermaat.\nVoeg de peper, het zout en de worcestersaus toe.\nSchenk langzaam de olie in de litermaat.\nPlaats de staafmixer langzaam in het mengsel en mix tot een gladde mayonaise.', '2026-01-15 15:46:28'),
(3, NULL, NULL, 'Millefeuille van biet', 'Vegetarisch tussengerecht: Aantal personen: 20', 'Schil de bieten. Snijd de bieten in dunne plakjes met behulp van een Chinese snijmachine. Leg een plakje op de werkbank en rol deze op. Bind ze vast met keukentouw. Herhaal dit tot je 20 pakketjes hebt.\nLeg de opgebonden bietenpakketjes rechtop in een diepe bak. Voeg bietensap toe tot de bieten half onder het sap staan. Breng aan de kook en laat ze Â± 20 minuten op de plaat licht koken. Draai de bieten voorzichtig om en laat ze Â± 20 minuten licht koken tot ze gaar zijn.\nWarm de bietjes voor de doorgifte op in het vocht.\nVerwijder de touwtjes en leg 1 pakketje op een bord.', '2026-01-15 15:46:28'),
(4, NULL, NULL, 'Komkommersalade met gemarineerde geitenkaas', 'Voorgerecht: Aantal personen: 20', 'Halveer de rode peper. Verwijder de zaadlijst uit de peper. Snijd een helft van de peper zo fijn mogelijk. Meng de peper in een bekken samen met de mosterd en het zout. Roer de olie erdoor. Bewaar dit voor later gebruik.\nWas de komkommers. Haal de zaadlijst uit de komkommers. Snijd de komkommer in brunoise. Bestrooi de komkommer met zout en laat dit uitlekken op een bolzeef.\nSnijd de korsten van de kaas. Snijd de kaas in brunoise.\nHak de kappertjes iets fijn. Schep de kappertjes en de kaas door de marinade. Laat deze op een koele plaats minstens 1 uur intrekken.\nLaat de salade uitlekken in een bolzeef. Meng de komkommer door de salade. Breng de salade op smaak met peper en zout en citroensap.', '2026-01-15 15:46:28'),
(5, NULL, NULL, 'Peterselie olie', 'Vegetarisch tussengerecht: Aantal personen: 20', 'Pluk de peterselie. Maal de peterselie fijn in de Thermoblender. Voeg de zonnebloemolie toe en maal dit enkele minuten op hoge snelheid goed door.\nZet de snelheid op stand 5 en de temperatuur 80Â°C. Laat dit 5 minuten draaien. Zet de temperatuur uit en maal het nog 15 minuten door op stand 5.\nPasseer de olie door een doek. Vang de groene olie op in een bekken en laat deze afkoelen. Vul een spuitflesje met de olie. Zet de olie op je werkbank voor later gebruik.', '2026-01-15 15:46:28'),
(6, NULL, NULL, 'Soja beurre blanc', 'Vegetarisch tussengerecht: Aantal personen: 20', 'Pel en snipper de sjalotten.\nSnij de boter in blokjes en leg deze koud weg voor later.\nVoeg de sjalotten, de dashi, wijn, de azijn, en het water bij elkaar en reduceer tot je een derde over hebt.\nVoeg de slagroom toe en kook dit in tot de helft. Zeef het geheel, en breng op smaak met de sojasaus.\nWarm voor de doorgifte de saus op en monteer deze met de koude roomboter. Blender de saus met de staafmixer. Breng de saus op smaak met peper en zout.', '2026-01-15 15:46:28'),
(7, NULL, NULL, 'Chips van schorseneren', 'Vegetarisch tussengerecht: Aantal personen: 20', 'Laat de schorseneren goed weken in koud water. Boen de schorseneren goed schoon.\nSnijd de uiteinden van de schorseneren.\nSnijd dunne plakken in de lengte van de schorseneren op een Chinese mandoline. Was de plakken onder koud stromend water.\nDep de plakken goed droog.\nFrituur de plakken schorseneer krokant in olie van 140ËšC. Zout de chips na. Droog de plakken na in de warmkast.', '2026-01-15 15:46:28'),
(8, NULL, NULL, 'Opmaak tussengerecht biet', 'Vegetarisch tussengerecht: Aantal personen: 20', 'Verwarm de beurre blanc, monteer deze af met de boter.\r\nVerwarm de bietpakketjes en snijd ze voorzichtig door de helft.\r\nLeg Â½ pakket in een diep bord en schenk voorzichtig de saus in het bord en druppel er wat peterselieolie over.\r\nGarneer het bordje af met shiso green en chips van schorseneren.', '2026-01-15 15:46:28'),
(9, NULL, NULL, 'Gebakken maiskipfilet', 'Hoofdgerecht: Aantal personen: 20', 'Vul de roner met water. Stel de temperatuur in op 63Â°C.\nMaal de Vadouvan kruiden fijn in de koffiemolen. Verwarm op laag vuur de olie. Voeg de Vadouvan kruiden toe. Verwarm dit voor 1 minuut. Laat de olie afkoelen.\nVerwijder de haasjes en pezen van de kipfilet.\nVul een vacuÃ¼mzak met 10 stuks kipfilet. Schenk een scheutje marinade erbij. VacÃ¼meer de zakken.\nGaar de kipfilet in de roner. Dit duurt minimaal 45 minuten.\nHaal de kip uit de vacuÃ¼mzakken. Dep de filets droog met keukenpapier.\nGrill de filets vlak voor de doorgifte in een droge/hete grillpan. De filet is al gaar en warm, dus het gaat alleen om een mooie grillstreep.', '2026-01-15 15:46:28'),
(10, NULL, NULL, 'Mix van paddenstoelen', 'Hoofdgerecht: Aantal personen: 20', 'Snijd de steeltjes van de Shiitake. Snijd de hoedjes in repen.\nScheur de oesterzwammen in repen.\nSnijd de kastanjechampignons in plakjes.\nPel en snipper de sjalotten. Pel en hak de knoflook. Pluk de peterselie en hak deze zeer fijn.\nVerwarm de olijfolie. Zweet hier in de sjalotjes en de knoflook aan. Voeg de paddenstoelen toe en bak ze gaar.\nSchep de paddenstoelen in vergiet. Voeg de gehakte peterselie toe. Breng de paddenstoelen op smaak met peper zout.\nWarm vlak voor doorgifte de paddenstoelen op in de oven op 180Â°C.', '2026-01-15 15:46:28'),
(11, NULL, NULL, 'Geroosterde witlof', 'Hoofdgerecht: Aantal personen: 20', 'Verwijder de slechte blaadjes van de witlof. Snijd de onderkant kegelvormig in. Halveer de witlof in de lengte. Snijd het bittere gedeelte eruit. Leg de witlof in een grote gastronormbak met de binnenkant naar boven.\nBesprenkel/overgiet de witlof met wat honing, Aceto azijn en olijfolie. Bestrooi de witlof met zout en peper.\nRooster de witlof in een hete oven op 175Â°C totdat de witlof gaar is. Koel de witlof terug.\nWarm voor de doorgifte de witlof op in de oven op 180Â°C.', '2026-01-15 15:46:28'),
(12, NULL, NULL, 'Gerookte aardappelpuree mousseline', 'Hoofdgerecht: Aantal personen: 20', 'Schil de aardappelen. Snijd de aardappels in stukken. Spoel de aardappel af. Kook de aardappelen in ruim water met het zout gaar. Giet de aardappelen af. Stoom ze kort droog.\nRook een derde van de aardappels 10 minuten in de rookoven.\nVerwarm ondertussen de slagroom met de boter.\nHaal de aardappels uit de rookoven. Pureer alle aardappels in de passe-vite. Meng de puree met de warme slagroom en boter. Breng de puree op smaak.\nWarm voor de doorgifte de puree au bain-marie op.', '2026-01-15 15:46:28'),
(13, NULL, NULL, 'Vandouvan jus', 'Hoofdgerecht: Aantal personen: 20', 'Schil en snipper de sjalot.\nMaal de Vadouvan fijn in de koffiemaler.\nVerwarm de olie in een kookpan. Myoteer de sjalotjes en de Vadouvan kruiden op laag vuur in het pannetje. Blus af met witte wijn en laat het iets inkoken.\nVoeg de jus de veau toe. Breng het aan de kook. Kook de saus in tot de gewenste dikte.\nMonteer vlak voor doorgifte de saus af met koude roomboter.', '2026-01-15 15:46:28'),
(14, NULL, NULL, 'Bananenparfait', 'Nagerecht: Aantal personen: 20', 'Week de blaadjes gelatine in koud water.\nSla de slagroom lobbig.\nSla de eidooier met de suiker au bain-marie op tot 70Â°C. Voeg de uitgeknepen gelatine toe en klop het mengsel koud in de Kitchen Aid.\nMeng de opgeklopte ei/suiker met de vruchtenpuree, spatel dit voorzichtig door elkaar. Spatel als laatste de lobbig geslagen slagroom er door en doe de compositie in de siliconen vormen.\nVries de parfait in op -30Â°C in de blastchiller. Haal de parfait na circa 1 uur uit de blastchiller, druk de parfait direct uit de siliconen matten en zet ze op een plastic plateau met slagersfolie. Doe dit in de gewone vriezer.\nServeer de parfait uit een ijsvriezer van -18Â°C. Serveer de parfait op een crumble, dat voorkomt dat de parfait direct gaat smelten op het bord!', '2026-01-15 15:46:28'),
(15, NULL, NULL, 'Cremeux van passievrucht', 'Nagerecht: Aantal personen: 20', 'Laat de boter op kamertemperatuur komen.\nMeng de passievruchtenpuree, suiker, eigeel door elkaar in een kookpan.\nVerwarm de massa onder voortdurend roeren op laag vuur tot 80Â°C.\nHaal van het vuur en voeg de gelatine toe.\nLaat afkoelen tot 38Â°C en mix met behulp van een staafmixer de boter in kleine klontjes door de compositie.\nZet de cremeux in de koelkast.\nKlop de cremeux los met een vlinder in de Kitchen Aid en doe het in een spuitzak met een klein spuitmondje.\nSpuit de cremeux op het dessertbord.', '2026-01-15 15:46:28'),
(16, NULL, NULL, 'Mokka crÃ¨me', 'Nagerecht: Aantal personen: 20', 'Week de blaadjes gelatine in koud water.\nSla de 2,5 dl slagroom lobbig.\nVerwarm de 100 gr room met de Mokka extract en los de uitgeknepen gelatine hierin op. Haal het van het vuur. Voeg de 200 gr chocolade toe en maak een emulsie, laat dit afkoelen tot 35Â°C.\nSpatel de lobbig geslagen slagroom door het chocolademengsel.\nLaat het mengsel opstijven in de koeling.\nKlop het mengsel in de Kitchen Aid luchtig voor gebruik.\nSpuit met behulp van een spuitzak de gewenste vorm op het bord.', '2026-01-15 15:46:28'),
(17, NULL, NULL, 'Bananenbuideltjes', 'Nagerecht: Aantal personen: 20', 'Pel de bananen en snijd ze elk in 10 plakjes.\r\nMeng de suiker met de kaneel en vermeng dit met bananen plakjes.\r\nSmeer een kant van een wonton velletje in met water.\r\nLeg een plakje banaan in het wonton velletje en vouw dit als een buideltje.\r\nLeg ze op slagersfolie en vries ze aan in de vriezer.\r\nBak ze net voor de doorgifte van het nagerecht een minuut in het olie van 180 graden in de frituur.\r\nBestrooi met poedersuiker.', '2026-01-15 15:46:28'),
(18, NULL, NULL, 'Crumble', 'Nagerecht: Aantal personen: 20', 'Meng de droge ingredienten (suiker, amandelpoeder, bloem, cacaopoeder, zout) in een bekken.\nSnijd de boter in kleine blokjes en voeg toe aan het mengsel.\nWrijf de boter door het mengsel tot een kruimelige structuur ontstaat.\nVerdeel de crumble over een bakplaat en bak in een voorverwarmde oven op 180Â°C gedurende 15-20 minuten tot goudbruin.', '2026-01-15 15:46:28');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `recipeingredient`
--

CREATE TABLE `recipeingredient` (
  `Recipe_id` int(11) NOT NULL,
  `Ingredient_id` int(11) NOT NULL,
  `Aantal` decimal(10,2) DEFAULT NULL,
  `Eenheid` varchar(50) DEFAULT NULL,
  `IngredientRole` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `recipeingredient`
--

INSERT INTO `recipeingredient` (`Recipe_id`, `Ingredient_id`, `Aantal`, `Eenheid`, `IngredientRole`) VALUES
(1, 1, 1200.00, 'g', 'hoofdingredient'),
(1, 2, 0.25, 'bs', 'kruid'),
(1, 3, 0.25, 'bs', 'kruid'),
(1, 4, NULL, '*', 'om in te smeren / naar smaak'),
(1, 5, 2.00, 'tn', 'gehakt'),
(2, 6, 0.25, 'el', 'smaakmaker'),
(2, 7, 1.00, 'st', 'vers'),
(2, 8, 8.00, 'g', 'zoutig'),
(2, 9, 1.00, 'el', 'azijn'),
(2, 10, 2.00, 'st', 'vis'),
(2, 11, 1.00, 'tl', 'zuur'),
(2, 12, 1.00, 'el', 'verdunning'),
(2, 13, 150.00, 'ml', 'olie'),
(2, 14, NULL, '*', 'naar smaak'),
(2, 15, NULL, '*', 'naar smaak'),
(3, 16, 3.00, 'fles', 'sap'),
(3, 17, 16.00, 'st', 'hoofdingredient'),
(3, 18, NULL, '*', 'hulpmiddel'),
(4, 4, 2.00, 'dl', 'olie'),
(4, 5, 2.00, 'tn', 'gehakt'),
(4, 6, NULL, '*', 'smaakmaker'),
(4, 14, NULL, '*', 'naar smaak'),
(4, 15, NULL, '*', 'naar smaak'),
(4, 19, 0.50, 'st', 'specerij'),
(4, 20, 400.00, 'g', 'hoofdingredient'),
(4, 21, 4.00, 'el', 'smaakmaker'),
(4, 22, 2.00, 'st', 'groente'),
(4, 23, 4.00, 'el', 'zuur'),
(5, 13, 250.00, 'ml', 'olie'),
(5, 24, 1.00, 'bs', 'kruid'),
(6, 4, NULL, '*', 'saus'),
(6, 9, 200.00, 'ml', 'azijn'),
(6, 14, NULL, '*', 'naar smaak'),
(6, 15, NULL, '*', 'naar smaak'),
(6, 25, 80.00, 'g', 'smaakmaker'),
(6, 26, 300.00, 'ml', 'wijn'),
(6, 27, 3.00, 'st', 'groente'),
(6, 28, 2.00, 'lt', 'zuivel'),
(6, 29, 400.00, 'g', 'zuivel'),
(7, 15, NULL, '*', 'naar smaak'),
(7, 29, 500.00, 'g', 'hoofdingredient'),
(8, 30, 0.50, 'st', NULL),
(8, 31, 0.00, '*', NULL),
(8, 32, 0.00, '*', NULL),
(8, 33, 0.00, '*', NULL),
(8, 34, 0.00, '*', NULL),
(9, 13, NULL, '2 dl', 'olie'),
(9, 14, NULL, '*', 'naar smaak'),
(9, 15, NULL, '*', 'naar smaak'),
(9, 35, 20.00, 'st', 'hoofdingredient'),
(9, 36, NULL, '*', 'kruid'),
(10, 4, NULL, '*', 'olie'),
(10, 5, 2.00, 'tn', 'gehakt'),
(10, 14, NULL, '*', 'naar smaak'),
(10, 15, NULL, '*', 'naar smaak'),
(10, 24, 0.50, 'bs', 'kruid'),
(10, 40, 300.00, 'g', 'hoofdingredient'),
(10, 41, 300.00, 'g', 'hoofdingredient'),
(10, 42, 300.00, 'g', 'hoofdingredient'),
(10, 43, 2.00, 'st', 'groente'),
(11, 4, NULL, '*', 'olie'),
(11, 14, NULL, '*', 'naar smaak'),
(11, 15, NULL, '*', 'naar smaak'),
(11, 44, 30.00, 'st', 'hoofdingredient'),
(11, 45, NULL, '*', 'smaakmaker'),
(11, 46, NULL, '*', 'azijn'),
(12, 14, NULL, '*', 'naar smaak'),
(12, 15, NULL, '*', 'naar smaak'),
(12, 28, 500.00, 'ml', 'zuivel'),
(12, 47, 1500.00, 'g', 'hoofdingredient'),
(12, 48, 100.00, 'g', 'zuivel'),
(13, 24, 10.00, 'g', 'kruid'),
(13, 26, 50.00, 'ml', 'wijn'),
(13, 27, 1.00, 'st', 'groente'),
(13, 49, 500.00, 'ml', 'smaakmaker'),
(13, 50, 50.00, 'g', 'zuivel'),
(14, 28, 250.00, 'g', 'zuivel'),
(14, 51, 500.00, 'g', 'smaakmaker'),
(14, 52, 150.00, 'g', 'smaakmaker'),
(14, 53, 75.00, 'g', 'ei'),
(14, 54, 2.00, 'bl', 'anders'),
(15, 50, 65.00, 'g', 'zuivel'),
(15, 52, 50.00, 'g', 'smaakmaker'),
(15, 54, 2.00, 'bl', 'anders'),
(15, 55, 190.00, 'g', 'smaakmaker'),
(15, 56, 90.00, 'g', 'ei'),
(16, 28, 250.00, 'ml', 'zuivel'),
(16, 54, 3.00, 'bl', 'anders'),
(16, 57, 100.00, 'g', 'zuivel'),
(16, 58, 2.00, 'tl', 'smaakmaker'),
(16, 59, 200.00, 'g', 'smaakmaker'),
(17, 52, 50.00, 'g', 'smaakmaker'),
(17, 60, 2.00, 'st', 'fruit'),
(17, 61, 3.00, 'g', 'specerij'),
(17, 62, 1.00, 'pk', 'anders'),
(18, 39, 1.00, 'g', 'specerij'),
(18, 50, 50.00, 'g', 'zuivel'),
(18, 52, 50.00, 'g', 'smaakmaker'),
(18, 63, 50.00, 'g', 'noten'),
(18, 64, 45.00, 'g', 'meel'),
(18, 65, 8.00, 'g', 'smaakmaker');

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `recipematerial`
--

CREATE TABLE `recipematerial` (
  `Recipe_id` int(11) NOT NULL,
  `Material_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `recipematerial`
--

INSERT INTO `recipematerial` (`Recipe_id`, `Material_id`) VALUES
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(2, 26),
(2, 35),
(3, 4),
(3, 5),
(3, 6),
(3, 30),
(4, 1),
(4, 2),
(4, 3),
(4, 30),
(4, 31),
(4, 35),
(5, 1),
(5, 7),
(5, 8),
(5, 9);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `role`
--

INSERT INTO `role` (`id`, `name`, `description`) VALUES
(1, 'student', NULL),
(2, 'docent', NULL),
(3, 'admin', NULL);

CREATE TABLE photo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  recipe_id INT,
  image LONGBLOB NOT NULL,
  mime_type VARCHAR(50),
  filename VARCHAR(255),
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;
-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `student`
--

CREATE TABLE `student` (
  `user_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `student`
--

INSERT INTO `student` (`user_id`, `class_id`, `role_id`) VALUES
(2, 1, 1),
(4, 1, 1),
(4, 4, 1),
(6, 4, 1);

-- --------------------------------------------------------

--
-- Tabelstructuur voor tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `passwordhash` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Gegevens worden geëxporteerd voor tabel `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `username`, `email`, `passwordhash`, `role_id`) VALUES
(1, 'admin', 'admin', 'admin', 'admin@admin.nl', '$2y$12$rKUNTeP5MPyo.fj/7e4K2unDJC1pp361F5HM3Ts3To/0F/CPq7gMe', 3),
(2, 'Lucas', 'Visser', 'L.Visser', '444444@student.talland.nl', '$2y$10$cO77V0O/Ensd9WdECPa83.YlOC9PM6du/fO12jkje6.VIG3tbLtXy', 1),
(4, 'Bart', 'Gielens', 'Bart.Gielens', 'b.gielens@talland.nl', '$2y$10$W3NDrOabT9I2alg3AIylBOkJBOBzcnaX5US5BHKEwPOBCdUyhCy8.', 2),
(6, 'Timo', 'Munts', 'Timo.Munts', 't.munts@talland.nl', '$2y$10$q4v8rY9lrvnu8nzyHxj1ROTNvv4Qvc.tJoc4IuN.buySwEr6/4Hv.', 2),
(8, 'Docent', 'Docent', 'Docent', 'docent@docent.nl', '$2y$10$PDPQt7qBvIWAwCSb2Wd.x.ELWJwhZR0Sbb5pHX9GTp936aSiEjriG', 2);

--
-- Indexen voor geëxporteerde tabellen
--

--
-- Indexen voor tabel `aanvulling`
--
ALTER TABLE `aanvulling`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Recipe_id` (`Recipe_id`);

--
-- Indexen voor tabel `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`categoryID`);

--
-- Indexen voor tabel `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `ingredient`
--
ALTER TABLE `ingredient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoryID` (`categoryID`);

--
-- Indexen voor tabel `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `nan_account`
--
ALTER TABLE `nan_account`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `UserKey` (`UserKey`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexen voor tabel `recipe`
--
ALTER TABLE `recipe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `User_id` (`User_id`),
  ADD KEY `Class_id` (`Class_id`);

--
-- Indexen voor tabel `recipeingredient`
--
ALTER TABLE `recipeingredient`
  ADD PRIMARY KEY (`Recipe_id`,`Ingredient_id`),
  ADD KEY `Ingredient_id` (`Ingredient_id`);

--
-- Indexen voor tabel `recipematerial`
--
ALTER TABLE `recipematerial`
  ADD PRIMARY KEY (`Recipe_id`,`Material_id`),
  ADD KEY `Material_id` (`Material_id`);

--
-- Indexen voor tabel `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexen voor tabel `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`user_id`,`class_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexen voor tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT voor geëxporteerde tabellen
--

--
-- AUTO_INCREMENT voor een tabel `aanvulling`
--
ALTER TABLE `aanvulling`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT voor een tabel `category`
--
ALTER TABLE `category`
  MODIFY `categoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT voor een tabel `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT voor een tabel `ingredient`
--
ALTER TABLE `ingredient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT voor een tabel `material`
--
ALTER TABLE `material`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT voor een tabel `nan_account`
--
ALTER TABLE `nan_account`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT voor een tabel `recipe`
--
ALTER TABLE `recipe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT voor een tabel `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT voor een tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Beperkingen voor geëxporteerde tabellen
--

--
-- Beperkingen voor tabel `aanvulling`
--
ALTER TABLE `aanvulling`
  ADD CONSTRAINT `aanvulling_ibfk_1` FOREIGN KEY (`Recipe_id`) REFERENCES `recipe` (`id`) ON DELETE CASCADE;

--
-- Beperkingen voor tabel `ingredient`
--
ALTER TABLE `ingredient`
  ADD CONSTRAINT `ingredient_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `category` (`categoryID`);

--
-- Beperkingen voor tabel `nan_account`
--
ALTER TABLE `nan_account`
  ADD CONSTRAINT `nan_account_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Beperkingen voor tabel `recipe`
--
ALTER TABLE `recipe`
  ADD CONSTRAINT `recipe_ibfk_1` FOREIGN KEY (`User_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recipe_ibfk_2` FOREIGN KEY (`Class_id`) REFERENCES `class` (`id`);

--
-- Beperkingen voor tabel `recipeingredient`
--
ALTER TABLE `recipeingredient`
  ADD CONSTRAINT `recipeingredient_ibfk_1` FOREIGN KEY (`Recipe_id`) REFERENCES `recipe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipeingredient_ibfk_2` FOREIGN KEY (`Ingredient_id`) REFERENCES `ingredient` (`id`);

--
-- Beperkingen voor tabel `recipematerial`
--
ALTER TABLE `recipematerial`
  ADD CONSTRAINT `recipematerial_ibfk_1` FOREIGN KEY (`Recipe_id`) REFERENCES `recipe` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipematerial_ibfk_2` FOREIGN KEY (`Material_id`) REFERENCES `material` (`id`);

--
-- Beperkingen voor tabel `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `student_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`),
  ADD CONSTRAINT `student_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Beperkingen voor tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

ALTER TABLE photo
ADD CONSTRAINT fk_photo_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
ADD CONSTRAINT fk_photo_recipe FOREIGN KEY (recipe_id) REFERENCES recipe(id) ON DELETE CASCADE;
COMMIT;

