DROP DATABASE IF EXISTS pan_en_passie;
CREATE DATABASE pan_en_passie CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE pan_en_passie;

CREATE TABLE Role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255)
);

CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255),
    lastname VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    passwordhash VARCHAR(255),
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES Role(id)
);

CREATE TABLE Class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classname VARCHAR(255),
    description TEXT,
    maxstudents INT,
    createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createrecipeperms BOOLEAN DEFAULT FALSE
);

CREATE TABLE Student (
    user_id INT,
    class_id INT,
    role_id INT,
    PRIMARY KEY (user_id, class_id),
    FOREIGN KEY (user_id) REFERENCES Users(id),
    FOREIGN KEY (class_id) REFERENCES Class(id),
    FOREIGN KEY (role_id) REFERENCES Role(id)
);

CREATE TABLE Category (
    categoryID INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(255) NOT NULL
);

INSERT INTO Category (categoryID, category) VALUES
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


CREATE TABLE Ingredient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    categoryID INT NOT NULL,
    FOREIGN KEY (categoryID) REFERENCES Category(categoryID)
);

INSERT INTO Ingredient (name, categoryID) VALUES
('Kalfs ribeye', 1),
('Tijm', 12),
('Rozemarijn', 12),
('Olijfolie', 10),
('Knoflook', 2),
('Mosterd', 6),
('Ei', 8),
('Kappertjes', 6),
('Sushi azijn', 9),
('Ansjovisfilet', 3),
('Limoensap', 6),
('Water', 11),
('Zonnebloemolie', 10),
('Peper', 5),
('Zout', 5),
('Worcestersaus', 4),
('Rode peper', 5),
('Harde geitenkaas', 7),
('Komkommer', 2),
('Citroensap', 6),
('Bietensap', 2),
('Grote rauwe rode bieten', 2),
('Keukentouw/cocktailprikker', 11),
('Peterselie', 12),
('Seru dashi', 6),
('Witte wijn', 14),
('Sjalotten', 2),
('Slagroom', 7),
('Koude boter', 7),
('Kikkoman sojasaus', 4),
('Schorseneren', 2),
('Biet millefeuille', 13),
('Soja beurre blanc', 13),
('Peterselie olie', 13),
('Shiso green', 12),
('Chips van schorseneren', 13),
('Maiskipfilet m/v a 150 gram', 1),
('Vadouvan kruiden', 12),
('Kastanjechampignons', 2),
('Oesterzwammen', 2),
('Shiitake', 2),
('Sjalotjes', 2),
('Witlof', 2),
('Honing', 6),
('Aceto balsamico', 9),
('Aardappelen', 2),
('Boter', 7),
('Jus de veau', 6),
('Roomboter', 7),
('Bananenpuree (Boiron)', 6),
('Suiker', 6),
('Eidooier', 8),
('Gelatine', 11),
('Passievruchtenpuree', 6),
('Eigeel gepasteuriseerd', 8),
('Mokka extract', 6),
('Melk chocolade', 6),
('Room', 7),
('Bananen', 15),
('Kaneelpoeder', 5),
('Wonton velletjes', 11),
('Amandelpoeder', 16),
('Bloem', 17),
('Cacaopoeder', 6),
('Grof zeezout', 5);

CREATE TABLE Material (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL
);

INSERT INTO Material (Name) VALUES
('Bolzeef'),('Bekken'),('Garde'),('Officemes'),('Diepe gastronormbak'),
('Chinese mandoline'),('Passeerdoek'),('Thermoblender'),('Spuitflesje'),
('Kookpan'),('Dunschiller'),('Vergiet'),('Keukenpapier'),('Röner'),
('Vacumeer zakken'),('Koffiemolen'),('Passe-vite'),('Spatel'),('Steelpan'),
('Sauspan'),('Pollepel'),('Temperatuurmeter'),('Siliconen vorm'),('Zeef'),
('Afruimbak'),('Staafmixer'),('Spuitzak'),('Kitchen Aid'),('Magic Mix'),
('Snijplank'),('Koksmes'),('Koekenpan'),('Grillpan'),('Slagerstouw'),('Litermaat');

CREATE TABLE Recipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    User_id INT DEFAULT NULL,
    Class_id INT DEFAULT NULL,
    Name VARCHAR(255),
    Description TEXT,
    Instructions TEXT,
    Createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (User_id) REFERENCES Users(id),
    FOREIGN KEY (Class_id) REFERENCES Class(id)
);

INSERT INTO Recipe (User_id, Class_id, id, Name, Description, Instructions) VALUES
(NULL, NULL,1,'Kalfs ribeye','Voorgerecht — Aantal personen: 20','Verwarm de oven voor op 80°C.\nSmeer de rib-eye in met olijfolie.\nPel en hak de knoflook. Smeer de knoflook op de rib-eye.\nBind met slagerstouw de tijm en de rozemarijn om de rib-eye.\nGrill de rib-eye om en om in een grillpan. Gaar de rib-eye verder in de oven op kerntemperatuur 54°C.\nLaat het vlees afkoelen.'),
(NULL, NULL,2,'Ansjovis mayonaise','Voorgerecht — Aantal personen: 20','Doe de mosterd, het ei, de sushi azijn, het limoensap, kappertjes, ansjovis en het water in een litermaat.\nVoeg de peper, het zout en de worcestersaus toe.\nSchenk langzaam de olie in de litermaat.\nPlaats de staafmixer langzaam in het mengsel en mix tot een gladde mayonaise.'),
(NULL, NULL,3,'Millefeuille van biet','Vegetarisch tussengerecht — Aantal personen: 20','Schil de bieten. Snijd de bieten in dunne plakjes met behulp van een Chinese snijmachine. Leg een plakje op de werkbank en rol deze op. Bind ze vast met keukentouw. Herhaal dit tot je 20 pakketjes hebt.\nLeg de opgebonden bietenpakketjes rechtop in een diepe bak. Voeg bietensap toe tot de bieten half onder het sap staan. Breng aan de kook en laat ze ± 20 minuten op de plaat licht koken. Draai de bieten voorzichtig om en laat ze ± 20 minuten licht koken tot ze gaar zijn.\nWarm de bietjes voor de doorgifte op in het vocht.\nVerwijder de touwtjes en leg 1 pakketje op een bord.'),
(NULL, NULL,4,'Komkommersalade met gemarineerde geitenkaas','Voorgerecht — Aantal personen: 20','Halveer de rode peper. Verwijder de zaadlijst uit de peper. Snijd een helft van de peper zo fijn mogelijk. Meng de peper in een bekken samen met de mosterd en het zout. Roer de olie erdoor. Bewaar dit voor later gebruik.\nWas de komkommers. Haal de zaadlijst uit de komkommers. Snijd de komkommer in brunoise. Bestrooi de komkommer met zout en laat dit uitlekken op een bolzeef.\nSnijd de korsten van de kaas. Snijd de kaas in brunoise.\nHak de kappertjes iets fijn. Schep de kappertjes en de kaas door de marinade. Laat deze op een koele plaats minstens 1 uur intrekken.\nLaat de salade uitlekken in een bolzeef. Meng de komkommer door de salade. Breng de salade op smaak met peper en zout en citroensap.'),
(NULL, NULL,5,'Peterselie olie','Vegetarisch tussengerecht — Aantal personen: 20','Pluk de peterselie. Maal de peterselie fijn in de Thermoblender. Voeg de zonnebloemolie toe en maal dit enkele minuten op hoge snelheid goed door.\nZet de snelheid op stand 5 en de temperatuur 80°C. Laat dit 5 minuten draaien. Zet de temperatuur uit en maal het nog 15 minuten door op stand 5.\nPasseer de olie door een doek. Vang de groene olie op in een bekken en laat deze afkoelen. Vul een spuitflesje met de olie. Zet de olie op je werkbank voor later gebruik.'),
(NULL, NULL,6,'Soja beurre blanc','Vegetarisch tussengerecht — Aantal personen: 20','Pel en snipper de sjalotten.\nSnij de boter in blokjes en leg deze koud weg voor later.\nVoeg de sjalotten, de dashi, wijn, de azijn, en het water bij elkaar en reduceer tot je een derde over hebt.\nVoeg de slagroom toe en kook dit in tot de helft. Zeef het geheel, en breng op smaak met de sojasaus.\nWarm voor de doorgifte de saus op en monteer deze met de koude roomboter. Blender de saus met de staafmixer. Breng de saus op smaak met peper en zout.'),
(NULL, NULL,7,'Chips van schorseneren','Vegetarisch tussengerecht — Aantal personen: 20','Laat de schorseneren goed weken in koud water. Boen de schorseneren goed schoon.\nSnijd de uiteinden van de schorseneren.\nSnijd dunne plakken in de lengte van de schorseneren op een Chinese mandoline. Was de plakken onder koud stromend water.\nDep de plakken goed droog.\nFrituur de plakken schorseneer krokant in olie van 140˚C. Zout de chips na. Droog de plakken na in de warmkast.'),
(NULL, NULL,8,'Opmaak tussengerecht biet','Vegetarisch tussengerecht — Aantal personen: 20','Verwarm de beurre blanc, monteer deze af met de boter.\nVerwarm de bietpakketjes en snijd ze voorzichtig door de helft.\nLeg ½ pakket in een diep bord en schenk voorzichtig de saus in het bord en druppel er wat peterselieolie over.\nGarneer het bordje af met shiso green en chips van schorseneren.'),
(NULL, NULL,9,'Gebakken maiskipfilet','Hoofdgerecht — Aantal personen: 20','Vul de roner met water. Stel de temperatuur in op 63°C.\nMaal de Vadouvan kruiden fijn in de koffiemolen. Verwarm op laag vuur de olie. Voeg de Vadouvan kruiden toe. Verwarm dit voor 1 minuut. Laat de olie afkoelen.\nVerwijder de haasjes en pezen van de kipfilet.\nVul een vacuümzak met 10 stuks kipfilet. Schenk een scheutje marinade erbij. Vacümeer de zakken.\nGaar de kipfilet in de roner. Dit duurt minimaal 45 minuten.\nHaal de kip uit de vacuümzakken. Dep de filets droog met keukenpapier.\nGrill de filets vlak voor de doorgifte in een droge/hete grillpan. De filet is al gaar en warm, dus het gaat alleen om een mooie grillstreep.'),
(NULL, NULL,10,'Mix van paddenstoelen','Hoofdgerecht — Aantal personen: 20','Snijd de steeltjes van de Shiitake. Snijd de hoedjes in repen.\nScheur de oesterzwammen in repen.\nSnijd de kastanjechampignons in plakjes.\nPel en snipper de sjalotten. Pel en hak de knoflook. Pluk de peterselie en hak deze zeer fijn.\nVerwarm de olijfolie. Zweet hier in de sjalotjes en de knoflook aan. Voeg de paddenstoelen toe en bak ze gaar.\nSchep de paddenstoelen in vergiet. Voeg de gehakte peterselie toe. Breng de paddenstoelen op smaak met peper zout.\nWarm vlak voor doorgifte de paddenstoelen op in de oven op 180°C.'),
(NULL, NULL,11,'Geroosterde witlof','Hoofdgerecht — Aantal personen: 20','Verwijder de slechte blaadjes van de witlof. Snijd de onderkant kegelvormig in. Halveer de witlof in de lengte. Snijd het bittere gedeelte eruit. Leg de witlof in een grote gastronormbak met de binnenkant naar boven.\nBesprenkel/overgiet de witlof met wat honing, Aceto azijn en olijfolie. Bestrooi de witlof met zout en peper.\nRooster de witlof in een hete oven op 175°C totdat de witlof gaar is. Koel de witlof terug.\nWarm voor de doorgifte de witlof op in de oven op 180°C.'),
(NULL, NULL,12,'Gerookte aardappelpuree mousseline','Hoofdgerecht — Aantal personen: 20','Schil de aardappelen. Snijd de aardappels in stukken. Spoel de aardappel af. Kook de aardappelen in ruim water met het zout gaar. Giet de aardappelen af. Stoom ze kort droog.\nRook een derde van de aardappels 10 minuten in de rookoven.\nVerwarm ondertussen de slagroom met de boter.\nHaal de aardappels uit de rookoven. Pureer alle aardappels in de passe-vite. Meng de puree met de warme slagroom en boter. Breng de puree op smaak.\nWarm voor de doorgifte de puree au bain-marie op.'),
(NULL, NULL,13,'Vandouvan jus','Hoofdgerecht — Aantal personen: 20','Schil en snipper de sjalot.\nMaal de Vadouvan fijn in de koffiemaler.\nVerwarm de olie in een kookpan. Myoteer de sjalotjes en de Vadouvan kruiden op laag vuur in het pannetje. Blus af met witte wijn en laat het iets inkoken.\nVoeg de jus de veau toe. Breng het aan de kook. Kook de saus in tot de gewenste dikte.\nMonteer vlak voor doorgifte de saus af met koude roomboter.'),
(NULL, NULL,14,'Bananenparfait','Nagerecht — Aantal personen: 20','Week de blaadjes gelatine in koud water.\nSla de slagroom lobbig.\nSla de eidooier met de suiker au bain-marie op tot 70°C. Voeg de uitgeknepen gelatine toe en klop het mengsel koud in de Kitchen Aid.\nMeng de opgeklopte ei/suiker met de vruchtenpuree, spatel dit voorzichtig door elkaar. Spatel als laatste de lobbig geslagen slagroom er door en doe de compositie in de siliconen vormen.\nVries de parfait in op -30°C in de blastchiller. Haal de parfait na circa 1 uur uit de blastchiller, druk de parfait direct uit de siliconen matten en zet ze op een plastic plateau met slagersfolie. Doe dit in de gewone vriezer.\nServeer de parfait uit een ijsvriezer van -18°C. Serveer de parfait op een crumble, dat voorkomt dat de parfait direct gaat smelten op het bord!'),
(NULL, NULL,15,'Cremeux van passievrucht','Nagerecht — Aantal personen: 20','Laat de boter op kamertemperatuur komen.\nMeng de passievruchtenpuree, suiker, eigeel door elkaar in een kookpan.\nVerwarm de massa onder voortdurend roeren op laag vuur tot 80°C.\nHaal van het vuur en voeg de gelatine toe.\nLaat afkoelen tot 38°C en mix met behulp van een staafmixer de boter in kleine klontjes door de compositie.\nZet de cremeux in de koelkast.\nKlop de cremeux los met een vlinder in de Kitchen Aid en doe het in een spuitzak met een klein spuitmondje.\nSpuit de cremeux op het dessertbord.'),
(NULL, NULL,16,'Mokka crème','Nagerecht — Aantal personen: 20','Week de blaadjes gelatine in koud water.\nSla de 2,5 dl slagroom lobbig.\nVerwarm de 100 gr room met de Mokka extract en los de uitgeknepen gelatine hierin op. Haal het van het vuur. Voeg de 200 gr chocolade toe en maak een emulsie, laat dit afkoelen tot 35°C.\nSpatel de lobbig geslagen slagroom door het chocolademengsel.\nLaat het mengsel opstijven in de koeling.\nKlop het mengsel in de Kitchen Aid luchtig voor gebruik.\nSpuit met behulp van een spuitzak de gewenste vorm op het bord.'),
(NULL, NULL,17,'Bananenbuideltjes','Nagerecht — Aantal personen: 20','Pel de bananen en snijd ze elk in 10 plakjes.\nMeng de suiker met de kaneel en vermeng dit met bananen plakjes.\nSmeer een kant van een wonton velletje in met water.\nLeg een plakje banaan in het wonton velletje en vouw dit als een buideltje.\nLeg ze op slagersfolie en vries ze aan in de vriezer.\nBak ze net voor de doorgifte van het nagerecht een minuut in het olie van 180 graden in de frituur.\nBestrooi met poedersuiker.'),
(NULL, NULL,18,'Crumble','Nagerecht — Aantal personen: 20','Meng de droge ingredienten (suiker, amandelpoeder, bloem, cacaopoeder, zout) in een bekken.\nSnijd de boter in kleine blokjes en voeg toe aan het mengsel.\nWrijf de boter door het mengsel tot een kruimelige structuur ontstaat.\nVerdeel de crumble over een bakplaat en bak in een voorverwarmde oven op 180°C gedurende 15-20 minuten tot goudbruin.');


CREATE TABLE RecipeIngredient (
    Recipe_id INT,
    Ingredient_id INT,
    Aantal DECIMAL(10,2),
    Eenheid VARCHAR(50),
    IngredientRole VARCHAR(255),
    PRIMARY KEY (Recipe_id, Ingredient_id),
    FOREIGN KEY (Recipe_id) REFERENCES Recipe(id) ON DELETE CASCADE,
    FOREIGN KEY (Ingredient_id) REFERENCES Ingredient(id)
);

INSERT INTO RecipeIngredient (Recipe_id, Ingredient_id, Aantal, Eenheid, IngredientRole) VALUES
(1, 1, 1200.00, 'g', 'hoofdingrediënt'),
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
(3, 17, 16.00, 'st', 'hoofdingrediënt'),
(3, 18, NULL, '*', 'hulpmiddel'),
(4, 19, 0.50, 'st', 'specerij'),
(4, 4, 2.00, 'dl', 'olie'),
(4, 5, 2.00, 'tn', 'gehakt'),
(4, 20, 400.00, 'g', 'hoofdingrediënt'),
(4, 21, 4.00, 'el', 'smaakmaker'),
(4, 22, 2.00, 'st', 'groente'),
(4, 23, 4.00, 'el', 'zuur'),
(4, 6, NULL, '*', 'smaakmaker'),
(4, 14, NULL, '*', 'naar smaak'),
(4, 15, NULL, '*', 'naar smaak'),
(5, 13, 250.00, 'ml', 'olie'),
(5, 24, 1.00, 'bs', 'kruid'),
(6, 25, 80.00, 'g', 'smaakmaker'),
(6, 9, 200.00, 'ml', 'azijn'),
(6, 26, 300.00, 'ml', 'wijn'),
(6, 27, 3.00, 'st', 'groente'),
(6, 28, 2.00, 'lt', 'zuivel'),
(6, 29, 400.00, 'g', 'zuivel'),
(6, 4, NULL, '*', 'saus'),
(6, 14, NULL, '*', 'naar smaak'),
(6, 15, NULL, '*', 'naar smaak'),
(7, 29, 500.00, 'g', 'hoofdingrediënt'),
(7, 15, NULL, '*', 'naar smaak'),
(8, 30, 0.50, 'st', 'component'),
(8, 31, NULL, '*', 'saus'),
(8, 32, NULL, '*', 'olie'),
(8, 33, NULL, '*', 'garnering'),
(8, 34, NULL, '*', 'garnering'),
(9, 35, 20.00, 'st', 'hoofdingrediënt'),
(9, 13, NULL, '2 dl', 'olie'),
(9, 14, NULL, '*', 'naar smaak'),
(9, 15, NULL, '*', 'naar smaak'),
(9, 36, NULL, '*', 'kruid'),
(10, 40, 300.00, 'g', 'hoofdingrediënt'),
(10, 41, 300.00, 'g', 'hoofdingrediënt'),
(10, 42, 300.00, 'g', 'hoofdingrediënt'),
(10, 43, 2.00, 'st', 'groente'),
(10, 5, 2.00, 'tn', 'gehakt'),
(10, 24, 0.50, 'bs', 'kruid'),
(10, 4, NULL, '*', 'olie'),
(10, 14, NULL, '*', 'naar smaak'),
(10, 15, NULL, '*', 'naar smaak'),
(11, 44, 30.00, 'st', 'hoofdingrediënt'),
(11, 45, NULL, '*', 'smaakmaker'),
(11, 46, NULL, '*', 'azijn'),
(11, 4, NULL, '*', 'olie'),
(11, 14, NULL, '*', 'naar smaak'),
(11, 15, NULL, '*', 'naar smaak'),
(12, 47, 1500.00, 'g', 'hoofdingrediënt'),
(12, 48, 100.00, 'g', 'zuivel'),
(12, 28, 500.00, 'ml', 'zuivel'),
(12, 14, NULL, '*', 'naar smaak'),
(12, 15, NULL, '*', 'naar smaak'),
(13, 49, 500.00, 'ml', 'smaakmaker'),
(13, 24, 10.00, 'g', 'kruid'),
(13, 27, 1.00, 'st', 'groente'),
(13, 26, 50.00, 'ml', 'wijn'),
(13, 50, 50.00, 'g', 'zuivel'),
(14, 51, 500.00, 'g', 'smaakmaker'),
(14, 52, 150.00, 'g', 'smaakmaker'),
(14, 53, 75.00, 'g', 'ei'),
(14, 54, 2.00, 'bl', 'anders'),
(14, 28, 250.00, 'g', 'zuivel'),
(15, 55, 190.00, 'g', 'smaakmaker'),
(15, 56, 90.00, 'g', 'ei'),
(15, 52, 50.00, 'g', 'smaakmaker'),
(15, 54, 2.00, 'bl', 'anders'),
(15, 50, 65.00, 'g', 'zuivel'),
(16, 57, 100.00, 'g', 'zuivel'),
(16, 58, 2.00, 'tl', 'smaakmaker'),
(16, 59, 200.00, 'g', 'smaakmaker'),
(16, 28, 250.00, 'ml', 'zuivel'),
(16, 54, 3.00, 'bl', 'anders'),
(17, 60, 2.00, 'st', 'fruit'),
(17, 52, 50.00, 'g', 'smaakmaker'),
(17, 61, 3.00, 'g', 'specerij'),
(17, 62, 1.00, 'pk', 'anders'),
(18, 50, 50.00, 'g', 'zuivel'),
(18, 52, 50.00, 'g', 'smaakmaker'),
(18, 63, 50.00, 'g', 'noten'),
(18, 64, 45.00, 'g', 'meel'),
(18, 65, 8.00, 'g', 'smaakmaker'),
(18, 39, 1.00, 'g', 'specerij');


CREATE TABLE RecipeMaterial (
    Recipe_id INT,
    Material_id INT,
    PRIMARY KEY (Recipe_id, Material_id),
    FOREIGN KEY (Recipe_id) REFERENCES Recipe(id) ON DELETE CASCADE,
    FOREIGN KEY (Material_id) REFERENCES Material(id)
);

INSERT INTO RecipeMaterial (Recipe_id, Material_id) VALUES
(1,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(1,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(1,(SELECT id FROM Material WHERE Name='Koekenpan' LIMIT 1)),
(1,(SELECT id FROM Material WHERE Name='Grillpan' LIMIT 1)),
(1,(SELECT id FROM Material WHERE Name='Slagerstouw' LIMIT 1)),
(2,(SELECT id FROM Material WHERE Name='Litermaat' LIMIT 1)),
(2,(SELECT id FROM Material WHERE Name='Staafmixer' LIMIT 1)),
(3,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(3,(SELECT id FROM Material WHERE Name='Officemes' LIMIT 1)),
(3,(SELECT id FROM Material WHERE Name='Diepe gastronormbak' LIMIT 1)),
(3,(SELECT id FROM Material WHERE Name='Chinese mandoline' LIMIT 1)),
(4,(SELECT id FROM Material WHERE Name='Bolzeef' LIMIT 1)),
(4,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(4,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(4,(SELECT id FROM Material WHERE Name='Litermaat' LIMIT 1)),
(4,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(4,(SELECT id FROM Material WHERE Name='Garde' LIMIT 1)),
(5,(SELECT id FROM Material WHERE Name='Bolzeef' LIMIT 1)),
(5,(SELECT id FROM Material WHERE Name='Passeerdoek' LIMIT 1)),
(5,(SELECT id FROM Material WHERE Name='Thermoblender' LIMIT 1)),
(5,(SELECT id FROM Material WHERE Name='Spuitflesje' LIMIT 1));

CREATE TABLE Aanvulling (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Recipe_id INT NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (Recipe_id) REFERENCES Recipe(id) ON DELETE CASCADE
);

INSERT INTO Aanvulling (Recipe_id, description) VALUES
(14, 'Serveer de parfait op een crumble, dat voorkomt dat de parfait direct gaat smelten op het bord!'),
(12, 'Niet te lang mengen, anders wordt de puree taai!'),
(3, 'Denk erom dat ze niet aanbranden.'),
(2, 'Probeer de olie bovenop de bestanddelen te krijgen.');
