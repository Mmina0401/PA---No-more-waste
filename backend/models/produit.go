package models

type Produit struct {
	ID          int     `json:"id_produit"`
	Nom         string  `json:"nom"`
	Description string  `json:"description"`
	CodeBarre   string  `json:"code_barre"`
	IDCategorie int     `json:"id_categorie"`
	Unite       string  `json:"unite"`
	Poids       float64 `json:"poids"`
	Actif       bool    `json:"actif"`
}
