USE no_more_waste;

CREATE TABLE competence (
    id_competence INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE benevole_competence (
    id_utilisateur INT NOT NULL,
    id_competence INT NOT NULL,

    PRIMARY KEY (id_utilisateur, id_competence),

    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE,

    FOREIGN KEY (id_competence)
        REFERENCES competence(id_competence)
        ON DELETE CASCADE
);

CREATE TABLE disponibilite_benevole (
    id_disponibilite INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    date_disponibilite DATE NOT NULL,
    heure_debut TIME,
    heure_fin TIME,

    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE
);

INSERT INTO competence (nom) VALUES
('Chauffeur'),
('Cuisine'),
('Plomberie'),
('Électricité'),
('Bricolage'),
('Logistique'),
('Manutention');