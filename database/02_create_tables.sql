
/*table utilisateurs*/
CREATE TABLE utilisateur (

    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(100) NOT NULL,

    prenom VARCHAR(100) NOT NULL,

    email VARCHAR(150) NOT NULL UNIQUE,

    mot_de_passe VARCHAR(255) NOT NULL,

    telephone VARCHAR(20),

    adresse VARCHAR(255),

    ville VARCHAR(100),

    code_postal VARCHAR(10),

    role ENUM(
        'ADMIN',
        'RESPONSABLE',
        'COMMERCANT',
        'BENEVOLE'
    ) NOT NULL,

    actif BOOLEAN DEFAULT TRUE,

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    photo_profil VARCHAR(255),

    dernier_acces DATETIME

);

/*table adhesion*/
CREATE TABLE adhesion (

    id_adhesion INT AUTO_INCREMENT PRIMARY KEY,

    id_utilisateur INT NOT NULL,

    date_debut DATE NOT NULL,

    date_fin DATE NOT NULL,

    montant DECIMAL(10,2) NOT NULL,

    date_paiement DATE,

    mode_paiement ENUM(
        'CARTE',
        'ESPECES',
        'CHEQUE',
        'VIREMENT'
    ),

    statut ENUM(
        'ACTIVE',
        'EXPIREE',
        'EN_ATTENTE'
    ) DEFAULT 'ACTIVE',

    rappel_envoye BOOLEAN DEFAULT FALSE,

    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE

);

/*table collecte*/
CREATE TABLE collecte (

    id_collecte INT AUTO_INCREMENT PRIMARY KEY,

    id_utilisateur INT NOT NULL,

    id_vehicule INT,

    date_collecte DATE NOT NULL,

    heure_debut TIME NOT NULL,

    heure_fin TIME NOT NULL,

    adresse VARCHAR(255) NOT NULL,

    ville VARCHAR(100) NOT NULL,

    code_postal VARCHAR(10) NOT NULL,

    commentaire TEXT,

    statut ENUM(
        'EN_ATTENTE',
        'VALIDEE',
        'PLANIFIEE',
        'EN_COURS',
        'TERMINEE',
        'ANNULEE'
    ) DEFAULT 'EN_ATTENTE',

    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE

);

/*collecte-benevole*/
CREATE TABLE collecte_benevole (

    id_collecte INT NOT NULL,

    id_utilisateur INT NOT NULL,

    role_collecte ENUM(
        'CHAUFFEUR',
        'MANUTENTION',
        'RESPONSABLE'
    ) NOT NULL,

    heure_arrivee TIME,

    heure_depart TIME,

    PRIMARY KEY(id_collecte, id_utilisateur),

    FOREIGN KEY(id_collecte)
        REFERENCES collecte(id_collecte)
        ON DELETE CASCADE,

    FOREIGN KEY(id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE

);

/*categorie produit*/
CREATE TABLE categorie_produit (

    id_categorie INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(100) NOT NULL UNIQUE,

    description TEXT

);

/*produit*/
CREATE TABLE produit (

    id_produit INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(150) NOT NULL,

    description TEXT,

    code_barre VARCHAR(50) NOT NULL UNIQUE,

    id_categorie INT NOT NULL,

    unite ENUM(
        'PIECE',
        'KG',
        'LITRE',
        'PAQUET',
        'CARTON'
    ) NOT NULL,

    poids DECIMAL(8,2),

    actif BOOLEAN DEFAULT TRUE,

    FOREIGN KEY (id_categorie)
        REFERENCES categorie_produit(id_categorie)

);

/*detail collecte*/
CREATE TABLE detail_collecte (

    id_collecte INT NOT NULL,

    id_produit INT NOT NULL,

    quantite DECIMAL(10,2) NOT NULL CHECK (quantite > 0),

    date_dlc DATE NOT NULL,

    etat ENUM(
        'EXCELLENT',
        'BON',
        'MOYEN',
        'ABIME'
    ) DEFAULT 'BON',

    observation TEXT,

    PRIMARY KEY (id_collecte, id_produit, date_dlc),

    FOREIGN KEY (id_collecte)
        REFERENCES collecte(id_collecte)
        ON DELETE CASCADE,

    FOREIGN KEY (id_produit)
        REFERENCES produit(id_produit)
        ON DELETE CASCADE

);

/*stock*/
CREATE TABLE stock (

    id_stock INT AUTO_INCREMENT PRIMARY KEY,

    id_produit INT NOT NULL UNIQUE,

    quantite DECIMAL(10,2) NOT NULL DEFAULT 0,

    emplacement VARCHAR(50) NOT NULL,

    date_entree DATETIME DEFAULT CURRENT_TIMESTAMP,

    derniere_maj DATETIME DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (id_produit)
        REFERENCES produit(id_produit)

);

/*mouvement stock*/
CREATE TABLE mouvement_stock (

    id_mouvement INT AUTO_INCREMENT PRIMARY KEY,

    id_stock INT NOT NULL,

    id_utilisateur INT NOT NULL,

    type ENUM(
        'ENTREE',
        'SORTIE',
        'PERTE',
        'CORRECTION'
    ) NOT NULL,

    quantite DECIMAL(10,2) NOT NULL,

    motif VARCHAR(255),

    date_mouvement DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_stock)
        REFERENCES stock(id_stock),

    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)

);

/*vehicule*/
CREATE TABLE vehicule (

    id_vehicule INT AUTO_INCREMENT PRIMARY KEY,

    immatriculation VARCHAR(20) NOT NULL UNIQUE,

    marque VARCHAR(50) NOT NULL,

    modele VARCHAR(50) NOT NULL,

    capacite DECIMAL(10,2) NOT NULL,

    etat ENUM(
        'DISPONIBLE',
        'EN_SERVICE',
        'EN_REPARATION'
    ) DEFAULT 'DISPONIBLE'

);

/*association beneficiaire*/
CREATE TABLE association_beneficiaire (

    id_association INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(150) NOT NULL,

    adresse VARCHAR(255),

    ville VARCHAR(100),

    code_postal VARCHAR(10),

    telephone VARCHAR(20),

    email VARCHAR(150),

    responsable VARCHAR(100)

);

/*livraison*/
CREATE TABLE livraison (

    id_livraison INT AUTO_INCREMENT PRIMARY KEY,

    id_association INT NOT NULL,

    id_vehicule INT NOT NULL,

    date_livraison DATE NOT NULL,

    heure_depart TIME,

    heure_retour TIME,

    statut ENUM(
        'PLANIFIEE',
        'EN_COURS',
        'TERMINEE',
        'ANNULEE'
    ) DEFAULT 'PLANIFIEE',

    commentaire TEXT,

    FOREIGN KEY(id_association)
        REFERENCES association_beneficiaire(id_association),

    FOREIGN KEY(id_vehicule)
        REFERENCES vehicule(id_vehicule)

);

/*detail livraison*/
CREATE TABLE detail_livraison (

    id_livraison INT NOT NULL,

    id_produit INT NOT NULL,

    quantite DECIMAL(10,2) NOT NULL,

    PRIMARY KEY(id_livraison,id_produit),

    FOREIGN KEY(id_livraison)
        REFERENCES livraison(id_livraison)
        ON DELETE CASCADE,

    FOREIGN KEY(id_produit)
        REFERENCES produit(id_produit)

);

/*service*/
CREATE TABLE service (

    id_service INT AUTO_INCREMENT PRIMARY KEY,

    nom VARCHAR(150) NOT NULL,

    description TEXT,

    lieu VARCHAR(255),

    date_service DATE,

    heure_debut TIME,

    heure_fin TIME,

    capacite_max INT,

    statut ENUM(
        'OUVERT',
        'COMPLET',
        'ANNULE'
    ) DEFAULT 'OUVERT'

);

/*inscription service*/
CREATE TABLE inscription_service (

    id_service INT NOT NULL,

    id_utilisateur INT NOT NULL,

    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,

    statut ENUM(
        'INSCRIT',
        'CONFIRME',
        'ANNULE'
    ) DEFAULT 'INSCRIT',

    PRIMARY KEY(id_service,id_utilisateur),

    FOREIGN KEY(id_service)
        REFERENCES service(id_service)
        ON DELETE CASCADE,

    FOREIGN KEY(id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE

);



