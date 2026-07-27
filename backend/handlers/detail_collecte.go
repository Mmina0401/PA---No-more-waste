package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetDetailCollectes(w http.ResponseWriter, r *http.Request) {

	details := []models.DetailCollecte{}

	rows, err := config.DB.Query(`
		SELECT
			id_collecte,
			id_produit,
			quantite,
			date_dlc,
			etat,
			observation
		FROM detail_collecte
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var d models.DetailCollecte

		err := rows.Scan(
			&d.IDCollecte,
			&d.IDProduit,
			&d.Quantite,
			&d.DateDLC,
			&d.Etat,
			&d.Observation,
		)

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		details = append(details, d)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(details)
}

func CreateDetailCollecte(w http.ResponseWriter, r *http.Request) {

	var d models.DetailCollecte

	err := json.NewDecoder(r.Body).Decode(&d)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		INSERT INTO detail_collecte (
			id_collecte,
			id_produit,
			quantite,
			date_dlc,
			etat,
			observation
		)
		VALUES (?, ?, ?, ?, ?, ?)
	`,
		d.IDCollecte,
		d.IDProduit,
		d.Quantite,
		d.DateDLC,
		d.Etat,
		d.Observation,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(map[string]string{
		"message": "Détail de collecte ajouté",
	})
}

func UpdateDetailCollecte(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPut {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var d models.DetailCollecte

	err := json.NewDecoder(r.Body).Decode(&d)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE detail_collecte
		SET
			quantite = ?,
			date_dlc = ?,
			etat = ?,
			observation = ?
		WHERE
			id_collecte = ?
			AND id_produit = ?
	`,
		d.Quantite,
		d.DateDLC,
		d.Etat,
		d.Observation,
		d.IDCollecte,
		d.IDProduit,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Détail de collecte modifié",
	})
}

func DeleteDetailCollecte(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodDelete {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var d models.DetailCollecte

	err := json.NewDecoder(r.Body).Decode(&d)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM detail_collecte
		WHERE
			id_collecte = ?
			AND id_produit = ?
	`,
		d.IDCollecte,
		d.IDProduit,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Détail de collecte supprimé",
	})
}
