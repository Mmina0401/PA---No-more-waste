    USE no_more_waste;

CREATE TABLE IF NOT EXISTS offre_benevole (
    id_offre INT AUTO_INCREMENT PRIMARY KEY,
    type_evenement ENUM('COLLECTE', 'SERVICE') NOT NULL,
    id_evenement INT NOT NULL,
    titre VARCHAR(150) NOT NULL,
    description TEXT,
    heure_debut TIME NOT NULL DEFAULT '08:00:00',
    heure_fin TIME NOT NULL DEFAULT '18:00:00',
    statut ENUM('OUVERTE', 'FERMEE') NOT NULL DEFAULT 'OUVERTE',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS offre_benevole_jour (
    id_jour INT AUTO_INCREMENT PRIMARY KEY,
    id_offre INT NOT NULL,
    date_jour DATE NOT NULL,

    CONSTRAINT fk_offre_jour
        FOREIGN KEY (id_offre)
        REFERENCES offre_benevole(id_offre)
        ON DELETE CASCADE,

    UNIQUE (id_offre, date_jour)
);

CREATE TABLE IF NOT EXISTS offre_benevole_reponse (
    id_reponse INT AUTO_INCREMENT PRIMARY KEY,
    id_offre INT NOT NULL,
    id_jour INT NOT NULL,
    id_utilisateur INT NOT NULL,
    date_reponse TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_reponse_offre
        FOREIGN KEY (id_offre)
        REFERENCES offre_benevole(id_offre)
        ON DELETE CASCADE,

    CONSTRAINT fk_reponse_jour
        FOREIGN KEY (id_jour)
        REFERENCES offre_benevole_jour(id_jour)
        ON DELETE CASCADE,

    CONSTRAINT fk_reponse_utilisateur
        FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE,

    UNIQUE (id_jour, id_utilisateur)
);