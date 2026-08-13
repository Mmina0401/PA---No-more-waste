USE no_more_waste;

ALTER TABLE offre_benevole
ADD COLUMN nombre_benevoles_requis INT NOT NULL DEFAULT 1 AFTER description;

ALTER TABLE offre_benevole_reponse
ADD COLUMN statut ENUM('EN_ATTENTE', 'ACCEPTEE', 'REFUSEE')
NOT NULL DEFAULT 'EN_ATTENTE'
AFTER id_utilisateur;

ALTER TABLE offre_benevole_reponse
ADD COLUMN date_decision TIMESTAMP NULL
AFTER statut;

UPDATE offre_benevole_reponse
SET statut = 'EN_ATTENTE'
WHERE statut IS NULL;