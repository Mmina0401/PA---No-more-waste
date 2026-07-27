package models

type Stock struct {
	IDStock     int     `json:"id_stock"`
	IDProduit   int     `json:"id_produit"`
	Quantite    float64 `json:"quantite"`
	Emplacement string  `json:"emplacement"`
	DateEntree  string  `json:"date_entree"`
	DerniereMaj string  `json:"derniere_maj"`
}
