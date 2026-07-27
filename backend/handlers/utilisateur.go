package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetUtilisateurs(w http.ResponseWriter, r *http.Request) {

	var utilisateurs []models.Utilisateur

	rows, err := config.DB.Query(`
		SELECT
			id_utilisateur,
			nom,
			prenom,
			email,
			mot_de_passe,
			telephone,
			adresse,
			ville,
			code_postal,
			role,
			actif,
			date_creation,
			dernier_acces
		FROM utilisateur
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var u models.Utilisateur

		err := rows.Scan(
			&u.ID,
			&u.Nom,
			&u.Prenom,
			&u.Email,
			&u.MotDePasse,
			&u.Telephone,
			&u.Adresse,
			&u.Ville,
			&u.CodePostal,
			&u.Role,
			&u.Actif,
			&u.DateCreation,
		)

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		utilisateurs = append(utilisateurs, u)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(utilisateurs)
}
