SET NAMES utf8mb4;
USE no_more_waste;
ALTER TABLE utilisateur
    ADD COLUMN raison_sociale VARCHAR(150) NULL,
    ADD COLUMN siret VARCHAR(14) NULL,
    ADD COLUMN secteur_activite VARCHAR(100) NULL;

UPDATE utilisateur
SET raison_sociale = 'Carrefour Nantes',
    siret = '12345678901234',
    secteur_activite = 'Grande distribution'
WHERE email = 'carrefour@nomorewaste.fr';
