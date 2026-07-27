package models

type CollecteBenevole struct {
	IDCollecte    int    `json:"id_collecte"`
	IDUtilisateur int    `json:"id_utilisateur"`
	RoleCollecte  string `json:"role_collecte"`
	HeureArrivee  string `json:"heure_arrivee"`
	HeureDepart   string `json:"heure_depart"`
}
