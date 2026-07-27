package models

import "time"

type Utilisateur struct {
	ID           int       `json:"id_utilisateur"`
	Nom          string    `json:"nom"`
	Prenom       string    `json:"prenom"`
	Email        string    `json:"email"`
	MotDePasse   string    `json:"mot_de_passe"`
	Telephone    string    `json:"telephone"`
	Adresse      string    `json:"adresse"`
	Ville        string    `json:"ville"`
	CodePostal   string    `json:"code_postal"`
	Role         string    `json:"role"`
	Actif        bool      `json:"actif"`
	DateCreation time.Time `json:"date_creation"`
}
