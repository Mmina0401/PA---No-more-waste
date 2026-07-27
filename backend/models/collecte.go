package models

type Collecte struct {
	IDCollecte    int    `json:"id_collecte"`
	IDUtilisateur int    `json:"id_utilisateur"`
	IDVehicule    *int   `json:"id_vehicule"`
	DateCollecte  string `json:"date_collecte"`
	HeureDebut    string `json:"heure_debut"`
	HeureFin      string `json:"heure_fin"`
	Adresse       string `json:"adresse"`
	Ville         string `json:"ville"`
	CodePostal    string `json:"code_postal"`
	Commentaire   string `json:"commentaire"`
	Statut        string `json:"statut"`
}
