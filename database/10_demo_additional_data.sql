SET NAMES utf8mb4;
USE no_more_waste;

INSERT INTO categorie_produit (nom, description)
SELECT 'Fruits', 'Fruits frais'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Fruits');

INSERT INTO categorie_produit (nom, description)
SELECT 'Légumes', 'Légumes frais'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Légumes');

INSERT INTO categorie_produit (nom, description)
SELECT 'Produits laitiers', 'Lait, yaourts, fromages'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Produits laitiers');

INSERT INTO categorie_produit (nom, description)
SELECT 'Boulangerie', 'Pain et viennoiseries'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Boulangerie');

INSERT INTO categorie_produit (nom, description)
SELECT 'Épicerie', 'Pâtes, riz, céréales et produits secs'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Épicerie');

INSERT INTO categorie_produit (nom, description)
SELECT 'Boissons', 'Eau, jus et boissons sans alcool'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Boissons');

INSERT INTO categorie_produit (nom, description)
SELECT 'Conserves', 'Produits alimentaires en conserve'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Conserves');

INSERT INTO categorie_produit (nom, description)
SELECT 'Plats préparés', 'Repas et préparations prêts à consommer'
WHERE NOT EXISTS (SELECT 1 FROM categorie_produit WHERE nom = 'Plats préparés');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Pommes Golden','Pommes françaises','376000001001',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Fruits'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001001');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Bananes','Bananes mûres à consommer rapidement','376000001002',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Fruits'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001002');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Oranges','Oranges de table','376000001003',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Fruits'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001003');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Poires Conférence','Poires fraîches','376000001004',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Fruits'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001004');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Tomates','Tomates fraîches','376000001005',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Légumes'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001005');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Carottes','Carottes en vrac','376000001006',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Légumes'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001006');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Courgettes','Courgettes fraîches','376000001007',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Légumes'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001007');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Pommes de terre','Pommes de terre en vrac','376000001008',c.id_categorie,'KG',1
FROM categorie_produit c
WHERE c.nom='Légumes'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001008');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Yaourts nature','Lot de yaourts nature','376000001009',c.id_categorie,'PIECE',0.5
FROM categorie_produit c
WHERE c.nom='Produits laitiers'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001009');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Fromage blanc','Pot de fromage blanc','376000001010',c.id_categorie,'PIECE',0.5
FROM categorie_produit c
WHERE c.nom='Produits laitiers'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001010');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Emmental râpé','Sachet d''emmental râpé','376000001011',c.id_categorie,'PIECE',0.2
FROM categorie_produit c
WHERE c.nom='Produits laitiers'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001011');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Baguette tradition','Baguette de pain','376000001012',c.id_categorie,'PIECE',0.25
FROM categorie_produit c
WHERE c.nom='Boulangerie'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001012');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Pain de mie','Paquet de pain de mie','376000001013',c.id_categorie,'PAQUET',0.5
FROM categorie_produit c
WHERE c.nom='Boulangerie'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001013');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Riz long','Paquet de riz 1 kg','376000001014',c.id_categorie,'PAQUET',1
FROM categorie_produit c
WHERE c.nom='Épicerie'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001014');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Spaghetti','Paquet de spaghetti 500 g','376000001015',c.id_categorie,'PAQUET',0.5
FROM categorie_produit c
WHERE c.nom='Épicerie'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001015');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Lentilles vertes','Paquet de lentilles 500 g','376000001016',c.id_categorie,'PAQUET',0.5
FROM categorie_produit c
WHERE c.nom='Épicerie'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001016');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Jus d''orange','Bouteille de jus d''orange 1 L','376000001017',c.id_categorie,'LITRE',1
FROM categorie_produit c
WHERE c.nom='Boissons'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001017');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Jus multifruits','Bouteille de jus multifruits 1 L','376000001018',c.id_categorie,'LITRE',1
FROM categorie_produit c
WHERE c.nom='Boissons'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001018');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Maïs en conserve','Boîte de maïs 300 g','376000001019',c.id_categorie,'PIECE',0.3
FROM categorie_produit c
WHERE c.nom='Conserves'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001019');

INSERT INTO produit
(nom, description, code_barre, id_categorie, unite, poids)
SELECT 'Ratatouille en conserve','Boîte de ratatouille 400 g','376000001020',c.id_categorie,'PIECE',0.4
FROM categorie_produit c
WHERE c.nom='Conserves'
AND NOT EXISTS (SELECT 1 FROM produit WHERE code_barre='376000001020');

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Atelier cuisine anti-gaspi','Préparation de recettes avec des invendus','Nantes',DATE_ADD(CURDATE(),INTERVAL 5 DAY),'09:00','12:00',20,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Atelier cuisine anti-gaspi' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Atelier réparation vélo','Réparation et entretien de vélos','Paris',DATE_ADD(CURDATE(),INTERVAL 8 DAY),'14:00','17:00',15,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Atelier réparation vélo' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Atelier couture','Réparer et prolonger la durée de vie des vêtements','Marseille',DATE_ADD(CURDATE(),INTERVAL 11 DAY),'14:00','16:30',12,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Atelier couture' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Initiation bricolage','Découverte des réparations simples du quotidien','Limoges',DATE_ADD(CURDATE(),INTERVAL 14 DAY),'10:00','12:30',14,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Initiation bricolage' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Cuisine zéro déchet','Apprendre à cuisiner sans gaspiller','Paris',DATE_ADD(CURDATE(),INTERVAL 17 DAY),'18:00','20:30',18,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Cuisine zéro déchet' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Conservation des aliments','Conseils pour mieux conserver les produits frais','Nantes',DATE_ADD(CURDATE(),INTERVAL 20 DAY),'10:00','12:00',25,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Conservation des aliments' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Réparation petit électroménager','Atelier de diagnostic et réparation','Marseille',DATE_ADD(CURDATE(),INTERVAL 23 DAY),'13:30','17:00',10,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Réparation petit électroménager' AND date_service >= CURDATE()
);

INSERT INTO service
(nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
SELECT 'Sensibilisation anti-gaspillage','Présentation des bonnes pratiques anti-gaspillage','Limoges',DATE_ADD(CURDATE(),INTERVAL 27 DAY),'09:30','11:30',30,'OUVERT'
WHERE NOT EXISTS (
SELECT 1 FROM service WHERE nom='Sensibilisation anti-gaspillage' AND date_service >= CURDATE()
);

INSERT INTO association_beneficiaire
(nom, adresse, ville, code_postal, telephone, email, responsable)
SELECT 'Les Paniers Solidaires','12 rue des Lilas','Paris','75012','0142001100','contact@paniers-solidaires.fr','Mme Robert'
WHERE NOT EXISTS (
SELECT 1 FROM association_beneficiaire WHERE email='contact@paniers-solidaires.fr'
);

INSERT INTO association_beneficiaire
(nom, adresse, ville, code_postal, telephone, email, responsable)
SELECT 'Main Tendue Marseille','31 boulevard National','Marseille','13001','0491002200','contact@maintendue-marseille.fr','M. Lopez'
WHERE NOT EXISTS (
SELECT 1 FROM association_beneficiaire WHERE email='contact@maintendue-marseille.fr'
);

INSERT INTO association_beneficiaire
(nom, adresse, ville, code_postal, telephone, email, responsable)
SELECT 'Solidarité 87','6 avenue Garibaldi','Limoges','87000','0555003300','contact@solidarite87.fr','Mme Morel'
WHERE NOT EXISTS (
SELECT 1 FROM association_beneficiaire WHERE email='contact@solidarite87.fr'
);

INSERT INTO association_beneficiaire
(nom, adresse, ville, code_postal, telephone, email, responsable)
SELECT 'Entraide Atlantique','45 rue de Strasbourg','Nantes','44000','0240004400','contact@entraide-atlantique.fr','M. Lefèvre'
WHERE NOT EXISTS (
SELECT 1 FROM association_beneficiaire WHERE email='contact@entraide-atlantique.fr'
);

INSERT INTO vehicule (immatriculation, marque, modele, capacite)
SELECT 'FF-111-FF','Renault','Trafic',1200
WHERE NOT EXISTS (
SELECT 1 FROM vehicule WHERE immatriculation='FF-111-FF'
);

INSERT INTO vehicule (immatriculation, marque, modele, capacite)
SELECT 'GG-222-GG','Peugeot','Expert',1100
WHERE NOT EXISTS (
SELECT 1 FROM vehicule WHERE immatriculation='GG-222-GG'
);

INSERT INTO vehicule (immatriculation, marque, modele, capacite)
SELECT 'HH-333-HH','Citroën','Jumpy',1150
WHERE NOT EXISTS (
SELECT 1 FROM vehicule WHERE immatriculation='HH-333-HH'
);

INSERT INTO stock (id_produit, quantite, emplacement)
SELECT p.id_produit, 25, CONCAT('DEMO-', p.id_produit)
FROM produit p
WHERE p.code_barre BETWEEN '376000001001' AND '376000001020'
AND NOT EXISTS (
SELECT 1 FROM stock s WHERE s.id_produit = p.id_produit
);
