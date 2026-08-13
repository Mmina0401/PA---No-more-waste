SET NAMES utf8mb4;
USE no_more_waste;

-- Les participants aux services sont les commerçants adhérents.
-- Les bénévoles qui animent/aident un service sont gérés séparément.
CREATE TABLE IF NOT EXISTS service_benevole (
    id_service INT NOT NULL,
    id_utilisateur INT NOT NULL,
    role_service VARCHAR(100) NOT NULL DEFAULT 'BENEVOLE',

    PRIMARY KEY (id_service, id_utilisateur),

    CONSTRAINT fk_service_benevole_service
        FOREIGN KEY (id_service)
        REFERENCES service(id_service)
        ON DELETE CASCADE,

    CONSTRAINT fk_service_benevole_utilisateur
        FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur)
        ON DELETE CASCADE
);

-- Migration des éventuelles anciennes "inscriptions" de bénévoles vers
-- leur vraie table d'affectation.
INSERT IGNORE INTO service_benevole (id_service, id_utilisateur, role_service)
SELECT i.id_service, i.id_utilisateur, 'BENEVOLE'
FROM inscription_service i
JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur
WHERE u.role = 'BENEVOLE'
  AND i.statut != 'ANNULE';

DELETE i
FROM inscription_service i
JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur
WHERE u.role = 'BENEVOLE';

-- Un SIRET identifie un commerçant : on évite les doublons quand il est renseigné.
ALTER TABLE utilisateur
    ADD UNIQUE INDEX uq_utilisateur_siret (siret);


-- Correction ciblée des anciennes données de démonstration :
-- les adhésions doivent appartenir au commerçant de démonstration,
-- pas aux bénévoles Paul/Aditya.
DELETE a
FROM adhesion a
JOIN utilisateur u ON u.id_utilisateur = a.id_utilisateur
WHERE u.email IN ('paul@nomorewaste.fr', 'aditya@nomorewaste.fr');

INSERT INTO adhesion (id_utilisateur, date_debut, date_fin, montant, statut)
SELECT u.id_utilisateur, '2026-01-01', '2026-12-31', 50.00, 'ACTIVE'
FROM utilisateur u
WHERE u.email = 'carrefour@nomorewaste.fr'
  AND NOT EXISTS (
      SELECT 1
      FROM adhesion a
      WHERE a.id_utilisateur = u.id_utilisateur
        AND a.date_debut = '2026-01-01'
        AND a.date_fin = '2026-12-31'
  );
