
/*insertion  dans la table categorie_produit*/
INSERT INTO categorie_produit (nom, description) 
VALUES
('Produits laitiers','Lait, yaourts, fromage'),
('Fruits','Fruits frais'),
('Légumes','Légumes frais'),
('Conserves','Produits en conserve'),
('Boissons','Eau, jus, sodas'),
('Hygiène','Produits d''hygiène'),
('Boulangerie','Pain et viennoiseries');

/*insertion des données dans la table vehicule*/
INSERT INTO vehicule
(immatriculation,marque,modele,capacite_kg)
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
(nom,description,lieu,date_service,heure_debut,heure_fin,capacite_max)
VALUES
('Cuisine solidaire','Préparation de repas','Nantes','2026-08-01','09:00','12:00',20),
('Réparation vélo','Atelier réparation','Nantes','2026-08-05','14:00','17:00',15);

/*insertion  dans la table utilisateur*/
INSERT INTO utilisateur
(nom,prenom,email,mot_de_passe,role)

VALUES

('Admin','NoMoreWaste','admin@nomorewaste.fr','admin123','ADMIN'),
('Martin','Paul','paul@nomorewaste.fr','password','BENEVOLE'),
('Durand','Sophie','sophie@nomorewaste.fr','password','RESPONSABLE'),
('Carrefour','Nantes','carrefour@nomorewaste.fr','password','COMMERCANT');

/*insertion  dans la table produit*/
INSERT INTO produit
(nom,description,code_barre,id_categorie,unite,poids)

VALUES

('Yaourt Nature','Yaourt nature 125g','376000000001',1,'PIECE',0.125),
('Lait demi-écrémé','Brique 1L','376000000002',1,'LITRE',1),
('Pomme Gala','Pomme française','376000000003',2,'KG',1),
('Haricots verts','Conserve 400g','376000000004',4,'PIECE',0.4),
('Eau minérale','Bouteille 1.5L','376000000005',5,'LITRE',1.5);

/*insertion des données dans la table adhesion*/
INSERT INTO adhesion
(id_utilisateur,date_adhesion,date_expiration,statut)

VALUES
(2,'2026-01-01','2027-01-01','ACTIVE'),
(3,'2026-02-15','2027-02-15','ACTIVE');

/*insertion dans la table stock*/
INSERT INTO stock
(id_produit,quantite,code_emplacement)

VALUES

(1,25,'A1-01'),
(2,40,'A1-02'),
(3,18,'B2-03');