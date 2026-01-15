DROP DATABASE IF EXISTS pan_en_passie;
CREATE DATABASE pan_en_passie;
USE pan_en_passie;

-- ======================
-- ROLE
-- ======================
CREATE TABLE Role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description VARCHAR(255)
);

-- ======================
-- USERS
-- ======================
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(255),
    lastname VARCHAR(255),
    username VARCHAR(255) UNIQUE,
    email VARCHAR(255) UNIQUE,
    passwordhash VARCHAR(255),
    role_id INT,
    FOREIGN KEY (role_id) REFERENCES role(id)
);

-- ======================
-- CLASS
-- ======================
CREATE TABLE Class (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classname VARCHAR(255),
    description TEXT,
    maxstudents INT,
    createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    createrecipeperms BOOLEAN DEFAULT FALSE
);

-- ======================
-- STUDENT (junction)
-- ======================
CREATE TABLE Student (
    user_id INT,
    class_id INT,
    role_id INT,
    PRIMARY KEY (user_id, class_id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES class(id),
    FOREIGN KEY (role_id) REFERENCES role(id)
);

-- ======================
-- RECIPE
-- ======================
CREATE TABLE Recipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    class_id INT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    instructions TEXT,
    createdat TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (class_id) REFERENCES class(id)
);

-- ======================
-- INGREDIENT
-- ======================
CREATE TABLE Ingredient (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(255) NOT NULL
);

-- ======================
-- RECIPE INGREDIENT
-- ======================
CREATE TABLE Recipeingredient (
    Recipe_id INT,
    ingredient_id INT,
    pieces VARCHAR(50),
    grams VARCHAR(50),
    milliliters VARCHAR(50),
    ingredientrole VARCHAR(255),
    PRIMARY KEY (Recipe_id, ingredient_id),
    FOREIGN KEY (Recipe_id) REFERENCES recipe(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_id) REFERENCES ingredient(id)
);

TRUNCATE TABLE recipeingredient;

-- ======================
-- MATERIAL
-- ======================
CREATE TABLE Material (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- ======================
-- RECIPE MATERIAL
-- ======================
CREATE TABLE Recipematerial (
    Recipe_id INT,
    material_id INT,
    PRIMARY KEY (Recipe_id, material_id),
    FOREIGN KEY (Recipe_id) REFERENCES recipe(id) ON DELETE CASCADE,
    FOREIGN KEY (material_id) REFERENCES material(id)
);

TRUNCATE TABLE recipematerial;

-- ======================
-- AANVULLING
-- ======================
CREATE TABLE Aanvulling (
    id INT AUTO_INCREMENT PRIMARY KEY,
    Recipe_id INT NOT NULL,
    description TEXT NOT NULL,
    FOREIGN KEY (Recipe_id) REFERENCES recipe(id) ON DELETE CASCADE
);

CREATE TABLE Foto(
Id INT NOT NULL,

);

INSERT  INTO Ingredient (Name, Category) VALUES
('Kalfs ribeye', 'vlees'),
('Tijm', 'kruid'),
('Rozemarijn', 'kruid'),
('Olijfolie', 'olie'),
('Knoflook', 'groente'),
('Mosterd', 'smaakmaker'),
('Ei', 'ei'),
('Kappertjes', 'smaakmaker'),
('Sushi azijn', 'azijn'),
('Ansjovisfilet', 'vis'),
('Limoensap', 'smaakmaker'),
('Water', 'anders'),
('Zonnebloemolie', 'olie'),
('Peper', 'specerij'),
('Zout', 'specerij'),
('Worcestersaus', 'saus');

INSERT  INTO Material (Name) VALUES
('Snijplank'),
('Koksmes'),
('Koekenpan'),
('Grillpan'),
('Litermaat'),
('Staafmixer'),
('Slagerstouw');

-- Aanvullingen
INSERT  INTO Ingredient (Name, Category) VALUES
('Rode peper', 'specerij'),
('Harde geitenkaas', 'zuivel'),
('Komkommer', 'groente'),
('Citroensap', 'smaakmaker'),
('Bietensap', 'groente'),
('Grote rauwe rode bieten', 'groente'),
('Keukentouw/cocktailprikker', 'anders'),
('Peterselie', 'kruid'),
('Seru dashi', 'smaakmaker'),
('Witte wijn', 'wijn'),
('Sjalotten', 'groente'),
('Slagroom', 'zuivel'),
('Koude boter', 'zuivel'),
('Kikkoman sojasaus', 'saus'),
('Schorseneren', 'groente'),
('Biet millefeuille', 'gerecht'),
('Soja beurre blanc', 'gerecht'),
('Peterselie olie', 'gerecht'),
('Shiso green', 'kruid'),
('Chips van schorseneren', 'gerecht'),
('Maiskipfilet m/v a 150 gram', 'vlees'),
('Vadouvan kruiden', 'kruid'),
('Kastanjechampignons', 'groente'),
('Oesterzwammen', 'groente'),
('Shiitake', 'groente'),
('Sjalotjes', 'groente'),
('Witlof', 'groente'),
('Honing', 'smaakmaker'),
('Aceto balsamico', 'azijn'),
('Aardappelen', 'groente'),
('Boter', 'zuivel'),
('Jus de veau', 'smaakmaker'),
('Roomboter', 'zuivel'),
('Bananenpuree (Boiron)', 'smaakmaker'),
('Suiker', 'smaakmaker'),
('Eidooier', 'ei'),
('Gelatine', 'anders'),
('Passievruchtenpuree', 'smaakmaker'),
('Eigeel gepasteuriseerd', 'ei'),
('Mokka extract', 'smaakmaker'),
('Melk chocolade', 'smaakmaker'),
('Room', 'zuivel'),
('Bananen', 'fruit'),
('Kaneelpoeder', 'specerij'),
('Wonton velletjes', 'anders'),
('Amandelpoeder', 'noten'),
('Bloem', 'meel'),
('Cacaopoeder', 'smaakmaker'),
('Grof zeezout', 'specerij');

INSERT  INTO Material (Name) VALUES
('Bolzeef'),
('Bekken'),
('Garde'),
('Officemes'),
('Diepe gastronormbak'),
('Chinese mandoline'),
('Passeerdoek'),
('Thermoblender'),
('Spuitflesje'),
('Kookpan'),
('Dunschiller'),
('Vergiet'),
('Keukenpapier'),
('Röner'),
('Vacumeer zakken'),
('Koffiemolen'),
('Passe-vite'),
('Spatel'),
('Steelpan'),
('Sauspan'),
('Pollepel'),
('Temperatuurmeter'),
('Siliconen vorm'),
('Zeef'),
('Afruimbak'),
('Staafmixer'),
('Spuitzak'),
('Kitchen Aid'),
('Magic Mix');

INSERT INTO Recipe (`User_id`,`Class_id`,`id`, Name, Description, Instructions) VALUES
(NULL, NULL,1, 'Kalfs ribeye', 'Voorgerecht — Aantal personen: 20', '1. Verwarm de oven voor op 80°C.\n2. Smeer de rib-eye in met olijfolie.\n3. Pel en hak de knoflook. Smeer de knoflook op de rib-eye.\n4. Bind met slagerstouw de tijm en de rozemarijn om de rib-eye.\n5. Grill de rib-eye om en om in een grillpan. Gaar de rib-eye verder in de oven op kerntemperatuur 54°C.\n6. Laat het vlees afkoelen.'),
(NULL, NULL,2,'Ansjovis mayonaise', 'Voorgerecht — Aantal personen: 20', '1. Doe de mosterd, het ei, de sushi azijn, het limoensap, kappertjes, ansjovis en het water in een litermaat.\n2. Voeg de peper, het zout en de worcestersaus toe.\n3. Schenk langzaam de olie in de litermaat.\n4. Plaats de staafmixer langzaam in het mengsel en mix tot een gladde mayonaise.'),
(NULL, NULL,3,'Millefeuille van biet', 'Vegetarisch tussengerecht — Aantal personen: 20', '1. Schil de bieten. Snijd de bieten in dunne plakjes met behulp van een Chinese snijmachine. Leg een plakje op de werkbank en rol deze op. Bind ze vast met keukentouw. Herhaal dit tot je 20 pakketjes hebt.\n2. Leg de opgebonden bietenpakketjes rechtop in een diepe bak. Voeg bietensap toe tot de bieten half onder het sap staan. Breng aan de kook en laat ze ± 20 minuten op de plaat licht koken. Draai de bieten voorzichtig om en laat ze ± 20 minuten licht koken tot ze gaar zijn.\n3. Warm de bietjes voor de doorgifte op in het vocht.\n4. Verwijder de touwtjes en leg 1 pakketje op een bord.'),
(NULL, NULL,4, 'Komkommersalade met gemarineerde geitenkaas', 'Voorgerecht — Aantal personen: 20', '1. Halveer de rode peper. Verwijder de zaadlijst uit de peper. Snijd een helft van de peper zo fijn mogelijk. Meng de peper in een bekken samen met de mosterd en het zout. Roer de olie erdoor. Bewaar dit voor later gebruik.\n2. Was de komkommers. Haal de zaadlijst uit de komkommers. Snijd de komkommer in brunoise. Bestrooi de komkommer met zout en laat dit uitlekken op een bolzeef.\n3. Snijd de korsten van de kaas. Snijd de kaas in brunoise.\n4. Hak de kappertjes iets fijn. Schep de kappertjes en de kaas door de marinade. Laat deze op een koele plaats minstens 1 uur intrekken.\n5. Laat de salade uitlekken in een bolzeef. Meng de komkommer door de salade. Breng de salade op smaak met peper en zout en citroensap.'),
(NULL, NULL,5,'Peterselie olie', 'Vegetarisch tussengerecht — Aantal personen: 20', '1. Pluk de peterselie. Maal de peterselie fijn in de Thermoblender. Voeg de zonnebloemolie toe en maal dit enkele minuten op hoge snelheid goed door.\n2. Zet de snelheid op stand 5 en de temperatuur 80°C. Laat dit 5 minuten draaien. Zet de temperatuur uit en maal het nog 15 minuten door op stand 5.\n3. Passeer de olie door een doek. Vang de groene olie op in een bekken en laat deze afkoelen. Vul een spuitflesje met de olie. Zet de olie op je werkbank voor later gebruik.'),
(NULL, NULL,6, 'Soja beurre blanc', 'Vegetarisch tussengerecht — Aantal personen: 20', '1. Pel en snipper de sjalotten.\n2. Snij de boter in blokjes en leg deze koud weg voor later.\n3. Voeg de sjalotten, de dashi, wijn, de azijn, en het water bij elkaar en reduceer tot je een derde over hebt.\n4. Voeg de slagroom toe en kook dit in tot de helft. Zeef het geheel, en breng op smaak met de sojasaus.\n5. Warm voor de doorgifte de saus op en monteer deze met de koude roomboter. Blender de saus met de staafmixer. Breng de saus op smaak met peper en zout.'),
(NULL, NULL,7, 'Chips van schorseneren', 'Vegetarisch tussengerecht — Aantal personen: 20', '1. Laat de schorseneren goed weken in koud water. Boen de schorseneren goed schoon.\n2. Snijd de uiteinden van de schorseneer.\n3. Snijd dunne plakken in de lengte van de schorseneren op een Chinese mandoline. Was de plakken onder koud stromend water.\n4. Dep de plakken goed droog.\n5. Frituur de plakken schorseneer krokant in olie van 140˚C. Zout de chips na. Droog de plakken na in de warmkast.'),
(NULL, NULL,8, 'Opmaak tussengerecht biet', 'Vegetarisch tussengerecht — Aantal personen: 20', '1. Verwarm de beurre blanc, monteer deze af met de boter.\n2. Verwarm de bietpakketjes en snijd ze voorzichtig door de helft.\n3. Leg ½ pakket in een diep bord en schenk voorzichtig de saus in het bord en druppel er wat peterselieolie over.\n4. Garneer het bordje af met shiso green en chips van schorseneren.'),
(NULL, NULL,9, 'Gebakken maiskipfilet', 'Hoofdgerecht — Aantal personen: 20', '1. Vul de roner met water. Stel de temperatuur in op 63°C.\n2. Maal de Vadouvan kruiden fijn in de koffiemolen. Verwarm op laag vuur de olie. Voeg de Vadouvan kruiden toe. Verwarm dit voor 1 minuut. Laat de olie afkoelen.\n3. Verwijder de haasjes en pezen van de kipfilet.\n4. Vul een vacuümzak met 10 stuks kipfilet. Schenk een scheutje marinade erbij. Vacümeer de zakken.\n5. Gaar de kipfilet in de roner. Dit duurt minimaal 45 minuten.\n6. Haal de kip uit de vacuümzakken. Dep de filets droog met keukenpapier.\n7. Grill de filets vlak voor de doorgifte in een droge/hete grillpan. De filet is al gaar en warm, dus het gaat alleen om een mooie grillstreep.'),
(NULL, NULL,10, 'Mix van paddenstoelen', 'Hoofdgerecht — Aantal personen: 20', '1. Snijd de steeltjes van de Shiitake. Snijd de hoedjes in repen.\n2. Scheur de oesterzwammen in repen.\n3. Snijd de kastanjechampignons in plakjes.\n4. Pel en snipper de sjalotten. Pel en hak de knoflook. Pluk de peterselie en hak deze zeer fijn.\n5. Verwarm de olijfolie. Zweet hier in de sjalotjes en de knoflook aan. Voeg de paddenstoelen toe en bak ze gaar.\n6. Schep de paddenstoelen in vergiet. Voeg de gehakte peterselie toe. Breng de paddenstoelen op smaak met peper zout.\n7. Warm vlak voor doorgifte de paddenstoelen op in de oven op 180°C.'),
(NULL, NULL,11, 'Geroosterde witlof', 'Hoofdgerecht — Aantal personen: 20', '1. Verwijder de slechte blaadjes van de witlof. Snijd de onderkant kegelvormig in. Halveer de witlof in de lengte. Snijd het bittere gedeelte eruit. Leg de witlof in een grote gastronormbak met de binnenkant naar boven.\n2. Besprenkel/overgiet de witlof met wat honing, Aceto azijn en olijfolie. Bestrooi de witlof met zout en peper.\n3. Rooster de witlof in een hete oven op 175°C totdat de witlof gaar is. Koel de witlof terug.\n4. Warm voor de doorgifte de witlof op in de oven op 180°C.'),
(NULL, NULL,12, 'Gerookte aardappelpuree mousseline', 'Hoofdgerecht — Aantal personen: 20', '1. Schil de aardappelen. Snijd de aardappels in stukken. Spoel de aardappel af. Kook de aardappelen in ruim water met het zout gaar. Giet de aardappelen af. Stoom ze kort droog.\n2. Rook een derde van de aardappels 10 minuten in de rookoven.\n3. Verwarm ondertussen de slagroom met de boter.\n4. Haal de aardappels uit de rookoven. Pureer alle aardappels in de passe-vite. Meng de puree met de warme slagroom en boter. Breng de puree op smaak.\n5. Warm voor de doorgifte de puree au bain-marie op.'),
(NULL, NULL,13, 'Vandouvan jus', 'Hoofdgerecht — Aantal personen: 20', '1. Schil en snipper de sjalot.\n2. Maal de Vadouvan fijn in de koffiemaler.\n3. Verwarm de olie in een kookpan. Myoteer de sjalotjes en de Vandouvan kruiden op laag vuur in het pannetje. Blus af met witte wijn en laat het iets inkoken.\n4. Voeg de jus de veau toe. Breng het aan de kook. Kook de saus in tot de gewenste dikte.\n5. Monteer vlak voor doorgifte de saus af met koude roomboter.'),
(NULL, NULL,14, 'Bananenparfait', 'Nagerecht — Aantal personen: 20', '1. Week de blaadjes gelatine in koud water.\n2. Sla de slagroom lobbig.\n3. Sla de eidooier met de suiker au bain-marie op tot 70°C. Voeg de uitgeknepen gelatine toe en klop het mengsel koud in de Kitchen Aid.\n4. Meng de opgeklopte ei/suiker met de vruchtenpuree, spatel dit voorzichtig door elkaar. Spatel als laatste de lobbig geslagen slagroom er door en doe de compositie in de siliconen vormen.\n5. Vries de parfait in op -30°C in de blastchiller. Haal de parfait na circa 1 uur uit de blastchiller, druk de parfait direct uit de siliconen matten en zet ze op een plastic plateau met slagersfolie. Doe dit in de gewone vriezer.\n6. Serveer de parfait uit een ijsvriezer van -18°C. *Serveer de parfait op een crumble, dat voorkomt dat de parfait direct gaat smelten op het bord!'),
(NULL, NULL,15, 'Cremeux van passievrucht', 'Nagerecht — Aantal personen: 20', '1. Laat de boter op kamertemperatuur komen.\n2. Meng de passievruchtenpuree, suiker, eigeel door elkaar in een kookpan.\n3. Verwarm de massa onder voortdurend roeren op laag vuur tot 80°C.\n4. Haal van het vuur en voeg de gelatine toe.\n5. Laat afkoelen tot 38°C en mix met behulp van een staafmixer de boter in kleine klontjes door de compositie.\n6. Zet de cremeux in de koelkast.\n7. Klop de cremeux los met een vlinder in de Kitchen Aid en doe het in een spuitzak met een klein spuitmondje.\n8. Spuit de cremeux op het dessertbord.'),
(NULL, NULL,16, 'Mokka crème', 'Nagerecht — Aantal personen: 20', '1. Week de blaadjes gelatine in koud water.\n2. Sla de 2,5 dl slagroom lobbig.\n3. Verwarm de 100 gr room met de Mokka extract en los de uitgeknepen gelatine hierin op. Haal het van het vuur. Voeg de 200 gr chocolade toe en maak een emulsie, laat dit afkoelen tot 35°C.\n4. Spatel de lobbig geslagen slagroom door het chocolademengsel.\n5. Laat het mengsel opstijven in de koeling.\n6. Klop het mengsel in de Kitchen Aid luchtig voor gebruik.\n7. Spuit met behulp van een spuitzak de gewenste vorm op het bord.'),
(NULL, NULL,17, 'Bananenbuideltjes', 'Nagerecht — Aantal personen: 20', '1. Pel de bananen en snijd ze elk in 10 plakjes.\n2. Meng de suiker met de kaneel en vermeng dit met bananen plakjes.\n3. Smeer een kant van een wonton velletje in met water.\n4. Leg een plakje banaan in het wonton velletje en vouw dit als een buideltje.\n5. Leg ze op slagersfolie en vries ze aan in de vriezer.\n6. Bak ze net voor de doorgifte van het nagerecht een minuut in het olie van 180 graden in de frituur.\n7. Bestrooi met poedersuiker.'),
(NULL, NULL,18, 'Crumble', 'Nagerecht — Aantal personen: 20', '1. Meng de droge ingredienten (suiker, amandelpoeder, bloem, cacaopoeder, zout) in een bekken.\n2. Snijd de boter in kleine blokjes en voeg toe aan het mengsel.\n3. Wrijf de boter door het mengsel tot een kruimelige structuur ontstaat.\n4. Verdeel de crumble over een bakplaat en bak in een voorverwarmde oven op 180°C gedurende 15-20 minuten tot goudbruin.');

INSERT INTO RecipeIngredient (`Recipe_id`, `Ingredient_id`, Pieces, Grams, Milliliters, IngredientRole) VALUES
(1,(SELECT id FROM Ingredient WHERE Name='Kalfs ribeye'   LIMIT 1), NULL, '1200', NULL, 'hoofdingrediënt'),
(1,(SELECT id FROM Ingredient WHERE Name='Tijm'           LIMIT 1), '¼ bs', NULL, NULL, 'kruid'),
(1,(SELECT id FROM Ingredient WHERE Name='Rozemarijn'     LIMIT 1), '¼ bs', NULL, NULL, 'kruid'),
(1,(SELECT id FROM Ingredient WHERE Name='Olijfolie'      LIMIT 1), '*',   NULL, NULL, 'om in te smeren / naar smaak'),
(1,(SELECT id FROM Ingredient WHERE Name='Knoflook'       LIMIT 1), '2 tn',NULL, NULL, 'gehakt'),
(2,(SELECT id FROM Ingredient WHERE Name='Mosterd'        LIMIT 1), '¼ el',NULL, '0',  'smaakmaker'),
(2,(SELECT id FROM Ingredient WHERE Name='Ei'             LIMIT 1), '1 st',NULL, NULL, 'vers'),
(2,(SELECT id FROM Ingredient WHERE Name='Kappertjes'     LIMIT 1), NULL,  '8',  NULL, 'zoutig'),
(2,(SELECT id FROM Ingredient WHERE Name='Sushi azijn'    LIMIT 1), '1 el',NULL, NULL, 'azijn'),
(2,(SELECT id FROM Ingredient WHERE Name='Ansjovisfilet'  LIMIT 1), '2 st',NULL, NULL, 'vis'),
(2,(SELECT id FROM Ingredient WHERE Name='Limoensap'      LIMIT 1), '1 tl',NULL, NULL, 'zuur'),
(2,(SELECT id FROM Ingredient WHERE Name='Water'          LIMIT 1), '1 el',NULL, NULL, 'verdunning'),
(2,(SELECT id FROM Ingredient WHERE Name='Zonnebloemolie' LIMIT 1), NULL,  '150',NULL, 'olie'),
(2,(SELECT id FROM Ingredient WHERE Name='Peper'          LIMIT 1), '*',   NULL, NULL, 'naar smaak'),
(2,(SELECT id FROM Ingredient WHERE Name='Zout'           LIMIT 1), '*',   NULL, NULL, 'naar smaak'),
(3,(SELECT id FROM Ingredient WHERE Name='Bietensap' LIMIT 1),'3 fles',NULL,NULL,'sap'),
(3,(SELECT id FROM Ingredient WHERE Name='Grote rauwe rode bieten' LIMIT 1),'16 st',NULL,NULL,'hoofdingrediënt'),
(3,(SELECT id FROM Ingredient WHERE Name='Keukentouw/cocktailprikker' LIMIT 1),'*',NULL,NULL,'hulpmiddel'),
(4,(SELECT id FROM Ingredient WHERE Name='Rode peper' LIMIT 1),'½ st',NULL,NULL,'specerij'),
(4,(SELECT id FROM Ingredient WHERE Name='Olijfolie' LIMIT 1),NULL,NULL,'2 dl','olie'),
(4,(SELECT id FROM Ingredient WHERE Name='Knoflook' LIMIT 1),'2 tn',NULL,NULL,'gehakt'),
(4,(SELECT id FROM Ingredient WHERE Name='Harde geitenkaas' LIMIT 1),NULL,'400',NULL,'hoofdingrediënt'),
(4,(SELECT id FROM Ingredient WHERE Name='Kappertjes' LIMIT 1),'4 el',NULL,NULL,'smaakmaker'),
(4,(SELECT id FROM Ingredient WHERE Name='Komkommer' LIMIT 1),'2 st',NULL,NULL,'groente'),
(4,(SELECT id FROM Ingredient WHERE Name='Citroensap' LIMIT 1),'4 el',NULL,NULL,'zuur'),
(4,(SELECT id FROM Ingredient WHERE Name='Mosterd' LIMIT 1),NULL,NULL,NULL,'smaakmaker'),
(4,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(4,(SELECT id FROM Ingredient WHERE Name='Peper' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(5,(SELECT id FROM Ingredient WHERE Name='Zonnebloemolie' LIMIT 1),NULL,'250',NULL,'olie'),
(5,(SELECT id FROM Ingredient WHERE Name='Peterselie' LIMIT 1),'1 bos',NULL,NULL,'kruid'),
(6,(SELECT id FROM Ingredient WHERE Name='Seru dashi' LIMIT 1),NULL,'80',NULL,'smaakmaker'),
(6,(SELECT id FROM Ingredient WHERE Name='Sushi azijn' LIMIT 1),NULL,'200',NULL,'azijn'),
(6,(SELECT id FROM Ingredient WHERE Name='Witte wijn' LIMIT 1),NULL,'300',NULL,'wijn'),
(6,(SELECT id FROM Ingredient WHERE Name='Sjalotten' LIMIT 1),'3 st',NULL,NULL,'groente'),
(6,(SELECT id FROM Ingredient WHERE Name='Slagroom' LIMIT 1),NULL,NULL,'2 lt','zuivel'),
(6,(SELECT id FROM Ingredient WHERE Name='Koude boter' LIMIT 1),NULL,'400',NULL,'zuivel'),
(6,(SELECT id FROM Ingredient WHERE Name='Kikkoman sojasaus' LIMIT 1),'*',NULL,NULL,'saus'),
(6,(SELECT id FROM Ingredient WHERE Name='Peper' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(6,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(7,(SELECT id FROM Ingredient WHERE Name='Schorseneren' LIMIT 1),NULL,'500',NULL,'hoofdingrediënt'),
(7,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(8,(SELECT id FROM Ingredient WHERE Name='Biet millefeuille' LIMIT 1),'½ st',NULL,NULL,'component'),
(8,(SELECT id FROM Ingredient WHERE Name='Soja beurre blanc' LIMIT 1),'*',NULL,NULL,'saus'),
(8,(SELECT id FROM Ingredient WHERE Name='Peterselie olie' LIMIT 1),'*',NULL,NULL,'olie'),
(8,(SELECT id FROM Ingredient WHERE Name='Shiso green' LIMIT 1),NULL,NULL,NULL,'garnering'),
(8,(SELECT id FROM Ingredient WHERE Name='Chips van schorseneren' LIMIT 1),'*',NULL,NULL,'garnering'),
(9,(SELECT id FROM Ingredient WHERE Name='Maiskipfilet m/v a 150 gram' LIMIT 1),'20 st',NULL,NULL,'hoofdingrediënt'),
(9,(SELECT id FROM Ingredient WHERE Name='Zonnebloemolie' LIMIT 1),NULL,NULL,'2 dl','olie'),
(9,(SELECT id FROM Ingredient WHERE Name='Peper' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(9,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(9,(SELECT id FROM Ingredient WHERE Name='Vadouvan kruiden' LIMIT 1),'*',NULL,NULL,'kruid'),
(10,(SELECT id FROM Ingredient WHERE Name='Kastanjechampignons' LIMIT 1),NULL,'300',NULL,'hoofdingrediënt'),
(10,(SELECT id FROM Ingredient WHERE Name='Oesterzwammen' LIMIT 1),NULL,'300',NULL,'hoofdingrediënt'),
(10,(SELECT id FROM Ingredient WHERE Name='Shiitake' LIMIT 1),NULL,'300',NULL,'hoofdingrediënt'),
(10,(SELECT id FROM Ingredient WHERE Name='Sjalotjes' LIMIT 1),'2 st',NULL,NULL,'groente'),
(10,(SELECT id FROM Ingredient WHERE Name='Knoflook' LIMIT 1),'2 tn',NULL,NULL,'gehakt'),
(10,(SELECT id FROM Ingredient WHERE Name='Peterselie' LIMIT 1),'½ bs',NULL,NULL,'kruid'),
(10,(SELECT id FROM Ingredient WHERE Name='Olijfolie' LIMIT 1),'*',NULL,NULL,'olie'),
(10,(SELECT id FROM Ingredient WHERE Name='Peper' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(10,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(11,(SELECT id FROM Ingredient WHERE Name='Witlof' LIMIT 1),'30 st',NULL,NULL,'hoofdingrediënt'),
(11,(SELECT id FROM Ingredient WHERE Name='Honing' LIMIT 1),'*',NULL,NULL,'smaakmaker'),
(11,(SELECT id FROM Ingredient WHERE Name='Aceto balsamico' LIMIT 1),'*',NULL,NULL,'azijn'),
(11,(SELECT id FROM Ingredient WHERE Name='Olijfolie' LIMIT 1),'*',NULL,NULL,'olie'),
(11,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(11,(SELECT id FROM Ingredient WHERE Name='Peper' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(12,(SELECT id FROM Ingredient WHERE Name='Aardappelen' LIMIT 1),NULL,'1500',NULL,'hoofdingrediënt'),
(12,(SELECT id FROM Ingredient WHERE Name='Boter' LIMIT 1),NULL,'100',NULL,'zuivel'),
(12,(SELECT id FROM Ingredient WHERE Name='Slagroom' LIMIT 1),NULL,NULL,'500','zuivel'),
(12,(SELECT id FROM Ingredient WHERE Name='Zout' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(12,(SELECT id FROM Ingredient WHERE Name='Peper' LIMIT 1),'*',NULL,NULL,'naar smaak'),
(13,(SELECT id FROM Ingredient WHERE Name='Jus de veau' LIMIT 1),NULL,NULL,'500','smaakmaker'),
(13,(SELECT id FROM Ingredient WHERE Name='Vadouvan kruiden' LIMIT 1),NULL,'10',NULL,'kruid'),
(13,(SELECT id FROM Ingredient WHERE Name='Sjalotten' LIMIT 1),'1 st',NULL,NULL,'groente'),
(13,(SELECT id FROM Ingredient WHERE Name='Witte wijn' LIMIT 1),NULL,'50',NULL,'wijn'),
(13,(SELECT id FROM Ingredient WHERE Name='Roomboter' LIMIT 1),NULL,'50',NULL,'zuivel'),
(14,(SELECT id FROM Ingredient WHERE Name='Bananenpuree (Boiron)' LIMIT 1),NULL,'500',NULL,'smaakmaker'),
(14,(SELECT id FROM Ingredient WHERE Name='Suiker' LIMIT 1),NULL,'150',NULL,'smaakmaker'),
(14,(SELECT id FROM Ingredient WHERE Name='Eidooier' LIMIT 1),NULL,'75',NULL,'ei'),
(14,(SELECT id FROM Ingredient WHERE Name='Gelatine' LIMIT 1),'2 bl',NULL,NULL,'anders'),
(14,(SELECT id FROM Ingredient WHERE Name='Slagroom' LIMIT 1),NULL,'250',NULL,'zuivel'),
(15,(SELECT id FROM Ingredient WHERE Name='Passievruchtenpuree' LIMIT 1), NULL, '190', NULL, 'smaakmaker'),
(15,(SELECT id FROM Ingredient WHERE Name='Eigeel gepasteuriseerd' LIMIT 1), NULL, '90', NULL, 'ei'),
(15,(SELECT id FROM Ingredient WHERE Name='Suiker' LIMIT 1), NULL, '50', NULL, 'smaakmaker'),
(15,(SELECT id FROM Ingredient WHERE Name='Gelatine' LIMIT 1), '2 bl', NULL, NULL, 'anders'),
(15,(SELECT id FROM Ingredient WHERE Name='Roomboter' LIMIT 1), NULL, '65', NULL, 'zuivel'),
(16,(SELECT id FROM Ingredient WHERE Name='Room' LIMIT 1), NULL, '100', NULL, 'zuivel'),
(16,(SELECT id FROM Ingredient WHERE Name='Mokka extract' LIMIT 1), '2 tl', NULL, NULL, 'smaakmaker'),
(16,(SELECT id FROM Ingredient WHERE Name='Melk chocolade' LIMIT 1), NULL, '200', NULL, 'smaakmaker'),
(16,(SELECT id FROM Ingredient WHERE Name='Slagroom' LIMIT 1), NULL, NULL, '2.5 dl', 'zuivel'),
(16,(SELECT id FROM Ingredient WHERE Name='Gelatine' LIMIT 1), '3 bl', NULL, NULL, 'anders'),
(17,(SELECT id FROM Ingredient WHERE Name='Bananen' LIMIT 1), '2 st', NULL, NULL, 'fruit'),
(17,(SELECT id FROM Ingredient WHERE Name='Suiker' LIMIT 1), NULL, '50', NULL, 'smaakmaker'),
(17,(SELECT id FROM Ingredient WHERE Name='Kaneelpoeder' LIMIT 1), NULL, '3', NULL, 'specerij'),
(17,(SELECT id FROM Ingredient WHERE Name='Wonton velletjes' LIMIT 1), '1 pk', NULL, NULL, 'anders'),
(18,(SELECT id FROM Ingredient WHERE Name='Roomboter' LIMIT 1), NULL, '50', NULL, 'zuivel'),
(18,(SELECT id FROM Ingredient WHERE Name='Suiker' LIMIT 1), NULL, '50', NULL, 'smaakmaker'),
(18,(SELECT id FROM Ingredient WHERE Name='Amandelpoeder' LIMIT 1), NULL, '50', NULL, 'noten'),
(18,(SELECT id FROM Ingredient WHERE Name='Bloem' LIMIT 1), NULL, '45', NULL, 'meel'),
(18,(SELECT id FROM Ingredient WHERE Name='Cacaopoeder' LIMIT 1), NULL, '8', NULL, 'smaakmaker'),
(18,(SELECT id FROM Ingredient WHERE Name='Grof zeezout' LIMIT 1), NULL, '1', NULL, 'specerij');

INSERT  INTO RecipeMaterial (`Recipe_id`, `Material_id`) VALUES
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
(5,(SELECT id FROM Material WHERE Name='Spuitflesje' LIMIT 1)),
(6,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(6,(SELECT id FROM Material WHERE Name='Officemes' LIMIT 1)),
(6,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(6,(SELECT id FROM Material WHERE Name='Kookpan' LIMIT 1)),
(6,(SELECT id FROM Material WHERE Name='Staafmixer' LIMIT 1)),
(6,(SELECT id FROM Material WHERE Name='Bolzeef' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Dunschiller' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Vergiet' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Keukenpapier' LIMIT 1)),
(7,(SELECT id FROM Material WHERE Name='Chinese mandoline' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Röner' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Vacumeer zakken' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Koffiemolen' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Grillpan' LIMIT 1)),
(9,(SELECT id FROM Material WHERE Name='Keukenpapier' LIMIT 1)),
(10,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(10,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(10,(SELECT id FROM Material WHERE Name='Koekenpan' LIMIT 1)),
(10,(SELECT id FROM Material WHERE Name='Vergiet' LIMIT 1)),
(11,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(11,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(11,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(11,(SELECT id FROM Material WHERE Name='Garde' LIMIT 1)),
(11,(SELECT id FROM Material WHERE Name='Diepe gastronormbak' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Dunschiller' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Koksmes' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Bolzeef' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Passe-vite' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Kookpan' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Spatel' LIMIT 1)),
(12,(SELECT id FROM Material WHERE Name='Steelpan' LIMIT 1)),
(13,(SELECT id FROM Material WHERE Name='Sauspan' LIMIT 1)),
(13,(SELECT id FROM Material WHERE Name='Pollepel' LIMIT 1)),
(13,(SELECT id FROM Material WHERE Name='Garde' LIMIT 1)),
(13,(SELECT id FROM Material WHERE Name='Koffiemolen' LIMIT 1)),
(14,(SELECT id FROM Material WHERE Name='Kookpan' LIMIT 1)),
(14,(SELECT id FROM Material WHERE Name='Spatel' LIMIT 1)),
(14,(SELECT id FROM Material WHERE Name='Garde' LIMIT 1)),
(14,(SELECT id FROM Material WHERE Name='Temperatuurmeter' LIMIT 1)),
(14,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(14,(SELECT id FROM Material WHERE Name='Siliconen vorm' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Kookpan' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Spatel' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Garde' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Zeef' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Afruimbak' LIMIT 1)),
(15,(SELECT id FROM Material WHERE Name='Staafmixer' LIMIT 1)),
(16,(SELECT id FROM Material WHERE Name='Kookpan' LIMIT 1)),
(16,(SELECT id FROM Material WHERE Name='Spatel' LIMIT 1)),
(16,(SELECT id FROM Material WHERE Name='Kitchen Aid' LIMIT 1)),
(16,(SELECT id FROM Material WHERE Name='Spuitzak' LIMIT 1)),
(17,(SELECT id FROM Material WHERE Name='Snijplank' LIMIT 1)),
(17,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(17,(SELECT id FROM Material WHERE Name='Officemes' LIMIT 1)),
(18,(SELECT id FROM Material WHERE Name='Bekken' LIMIT 1)),
(18,(SELECT id FROM Material WHERE Name='Spatel' LIMIT 1)),
(18,(SELECT id FROM Material WHERE Name='Kitchen Aid' LIMIT 1)),
(18,(SELECT id FROM Material WHERE Name='Magic Mix' LIMIT 1));

INSERT INTO Aanvulling (Recipe_id, description) VALUES (14, 'Serveer de parfait op een crumble, dat voorkomt dat de parfait direct gaat smelten op het bord!');
INSERT INTO Aanvulling (Recipe_id, description) VALUES (12, 'Niet te lang mengen, anders wordt de puree taai!');
INSERT INTO Aanvulling (Recipe_id, description) VALUES (3, 'Denk erom dat ze niet aanbranden.');
INSERT INTO Aanvulling (Recipe_id, description) VALUES (2, 'Probeer de olie bovenop de bestanddelen te krijgen.');

COMMIT;
