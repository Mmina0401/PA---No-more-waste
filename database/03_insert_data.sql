SET NAMES utf8mb4;
USE no_more_waste;

INSERT INTO categorie_produit (nom, description)
VALUES
('Produits laitiers','Lait, yaourts, fromage'),
('Fruits','Fruits frais'),
('Légumes','Légumes frais'),
('Conserves','Produits en conserve'),
('Boissons','Eau, jus, sodas'),
('Hygiène','Produits d''hygiène'),
('Boulangerie','Pain et viennoiseries');

INSERT INTO vehicule

(immatriculation,marque,modele,capacite)
VALUES
('AA-123-AA','Renault','Kangoo',700),
('BB-456-BB','Peugeot','Partner',800),
('CC-789-CC','Renault','Master',1500);

INSERT INTO association_beneficiaire
(nom,adresse,ville,code_postal,telephone,email,responsable)
VALUES
('Croix Rouge Nantes','10 rue Victor Hugo','Nantes','44000','0240000000','contact@croixrouge.fr','Mme Martin'),
('Restos du Coeur Nantes','15 avenue République','Nantes','44000','0240000001','contact@restos.fr','M. Dupont'),
('Secours Populaire Nantes','20 boulevard de la Liberté','Nantes','44000','0240000002','contact@secourspopulaire.fr','M. Bernard');

INSERT INTO service
(nom,description,lieu,date_service,heure_debut,heure_fin,capacite_max,statut)
VALUES
(
'Cuisine solidaire',
'Atelier de préparation de repas anti-gaspillage',
'Nantes',
DATE_ADD(CURDATE(), INTERVAL 7 DAY),
'09:00',
'12:00',
20,
'OUVERT'
),
(
'Réparation vélo',
'Atelier pour apprendre à réparer et entretenir son vélo',
'Nantes',
DATE_ADD(CURDATE(), INTERVAL 10 DAY),
'14:00',
'17:00',
15,
'OUVERT'
),
(
'Atelier anti-gaspillage',
'Conseils pratiques pour réduire le gaspillage alimentaire au quotidien',
'Nantes',
DATE_ADD(CURDATE(), INTERVAL 14 DAY),
'10:00',
'12:00',
25,
'OUVERT'
),
(
'Initiation bricolage',
'Atelier pratique pour apprendre les bases du bricolage et de la réparation',
'Nantes',
DATE_ADD(CURDATE(), INTERVAL 18 DAY),
'13:30',
'16:30',
12,
'OUVERT'
),
(
'Atelier plomberie',
'Initiation aux petites réparations de plomberie du quotidien',
'Nantes',
DATE_ADD(CURDATE(), INTERVAL 21 DAY),
'09:30',
'11:30',
10,
'OUVERT'
);

INSERT INTO utilisateur
(nom,prenom,email,mot_de_passe,role)

VALUES

('Admin','NoMoreWaste','admin@nomorewaste.fr','admin123','ADMIN'),
('Martin','Paul','paul@nomorewaste.fr','password','BENEVOLE'),
('Parekh','Aditya','aditya@nomorewaste.fr','password','BENEVOLE'),
('Durand','Sophie','sophie@nomorewaste.fr','password','RESPONSABLE'),
('Carrefour','Nantes','carrefour@nomorewaste.fr','password','COMMERCANT');

INSERT INTO produit
(nom,description,code_barre,id_categorie,unite,poids)

VALUES

('Yaourt Nature','Yaourt nature 125g','376000000001',1,'PIECE',0.125),
('Lait demi-écrémé','Brique 1L','376000000002',1,'LITRE',1),
('Pomme Gala','Pomme française','376000000003',2,'KG',1),
('Haricots verts','Conserve 400g','376000000004',4,'PIECE',0.4),
('Eau minérale','Bouteille 1.5L','376000000005',5,'LITRE',1.5);

INSERT INTO adhesion
(id_utilisateur,date_debut,date_fin,montant,statut)

VALUES
(5,'2025-01-01','2025-12-31',50.00,'EXPIREE'),
(5,'2026-01-01','2026-12-31',50.00,'ACTIVE');

INSERT INTO stock
(id_produit,quantite,emplacement)

VALUES

(1,25,'A1-01'),
(2,40,'A1-02'),
(3,18,'B2-03');
