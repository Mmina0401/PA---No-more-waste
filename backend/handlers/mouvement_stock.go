package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetMouvementsStock(w http.ResponseWriter, r *http.Request) {

	var mouvements []models.MouvementStock

	rows, err := config.DB.Query(`
		SELECT
			id_mouvement,
			id_stock,
			id_utilisateur,
			type,
			quantite,
			motif,
			date_mouvement
		FROM mouvement_stock
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var m models.MouvementStock

		rows.Scan(
			&m.IDMouvement,
			&m.IDStock,
			&m.IDUtilisateur,
			&m.Type,
			&m.Quantite,
			&m.Motif,
			&m.DateMouvement,
		)

		mouvements = append(mouvements, m)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(mouvements)
}

func CreateMouvementStock(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var m models.MouvementStock

	err := json.NewDecoder(r.Body).Decode(&m)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		INSERT INTO mouvement_stock
		(
			id_stock,
			id_utilisateur,
			type,
			quantite,
			motif
		)
		VALUES (?, ?, ?, ?, ?)
	`,
		m.IDStock,
		m.IDUtilisateur,
		m.Type,
		m.Quantite,
		m.Motif,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Mouvement ajouté",
	})
}

func UpdateMouvementStock(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPut {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var m models.MouvementStock

	err := json.NewDecoder(r.Body).Decode(&m)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE mouvement_stock
		SET
			id_stock = ?,
			id_utilisateur = ?,
			type = ?,
			quantite = ?,
			motif = ?
		WHERE
			id_mouvement = ?
	`,
		m.IDStock,
		m.IDUtilisateur,
		m.Type,
		m.Quantite,
		m.Motif,
		m.IDMouvement,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Mouvement modifié",
	})
}

func DeleteMouvementStock(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodDelete {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var m models.MouvementStock

	err := json.NewDecoder(r.Body).Decode(&m)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM mouvement_stock
		WHERE id_mouvement = ?
	`,
		m.IDMouvement,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Mouvement supprimé",
	})
}
