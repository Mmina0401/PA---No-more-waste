package models

type Stock struct {
	IDStock     int     `json:"id_stock"`
	IDProduit   int     `json:"id_produit"`
	NomProduit  string  `json:"nom_produit"`
	CodeBarre   string  `json:"code_barre"`
	Categorie   string  `json:"categorie"`
	Quantite    float64 `json:"quantite"`
	Emplacement string  `json:"emplacement"`
	DateEntree  string  `json:"date_entree"`
	DerniereMaj string  `json:"derniere_maj"`
}
