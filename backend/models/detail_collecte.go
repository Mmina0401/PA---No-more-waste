package models

type DetailCollecte struct {
	IDCollecte  int     `json:"id_collecte"`
	IDProduit   int     `json:"id_produit"`
	NomProduit  string  `json:"nom_produit,omitempty"`
	Quantite    float64 `json:"quantite"`
	DateDLC     string  `json:"date_dlc"`
	Etat        string  `json:"etat"`
	Observation string  `json:"observation"`
	Emplacement string  `json:"emplacement,omitempty"`
}
