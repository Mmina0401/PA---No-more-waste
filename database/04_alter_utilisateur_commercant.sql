ALTER TABLE utilisateur
    ADD COLUMN raison_sociale VARCHAR(150) NULL,
    ADD COLUMN siret VARCHAR(14) NULL,
    ADD COLUMN secteur_activite VARCHAR(100) NULL;