package models

// Service correspond à une session concrète (ex: "Cuisine solidaire" le
// 2026-08-01 de 9h à 12h). Il n'y a pas de table "créneau" séparée : la
// date et les horaires sont directement sur la ligne de service.
type Service struct {
	IDService   int     `json:"id_service"`
	Nom         string  `json:"nom"`
	Description *string `json:"description"`
	Lieu        *string `json:"lieu"`
	DateService *string `json:"date_service"`
	HeureDebut  *string `json:"heure_debut"`
	HeureFin    *string `json:"heure_fin"`
	CapaciteMax *int    `json:"capacite_max"`
	Statut      string  `json:"statut"` // OUVERT | COMPLET | ANNULE

	// Rempli uniquement par GetServices : nombre de personnes déjà inscrites
	// (calculé, pas stocké en base) pour savoir combien de places restent.
	NbInscrits int `json:"nb_inscrits,omitempty"`
}

// InscriptionService relie un utilisateur (bénévole, adhérent...) à un service.
type InscriptionService struct {
	IDService       int    `json:"id_service"`
	IDUtilisateur   int    `json:"id_utilisateur"`
	DateInscription string `json:"date_inscription,omitempty"`
	Statut          string `json:"statut"` // INSCRIT | CONFIRME | ANNULE

	// Renseignés uniquement par GetInscriptions (jointure), pour affichage.
	NomUtilisateur    string `json:"nom,omitempty"`
	PrenomUtilisateur string `json:"prenom,omitempty"`
}
