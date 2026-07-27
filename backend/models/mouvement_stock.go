package models

type MouvementStock struct {
	IDMouvement   int     `json:"id_mouvement"`
	IDStock       int     `json:"id_stock"`
	IDUtilisateur int     `json:"id_utilisateur"`
	Type          string  `json:"type"`
	Quantite      float64 `json:"quantite"`
	Motif         string  `json:"motif"`
	DateMouvement string  `json:"date_mouvement"`
}
