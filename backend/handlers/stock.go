package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetStocks(w http.ResponseWriter, r *http.Request) {

	var stocks []models.Stock

	rows, err := config.DB.Query(`
		SELECT
			id_stock,
			id_produit,
			quantite,
			emplacement,
			date_entree,
			derniere_maj
		FROM stock
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var s models.Stock

		rows.Scan(
			&s.IDStock,
			&s.IDProduit,
			&s.Quantite,
			&s.Emplacement,
			&s.DateEntree,
			&s.DerniereMaj,
		)

		stocks = append(stocks, s)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(stocks)
}

func CreateStock(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var s models.Stock

	err := json.NewDecoder(r.Body).Decode(&s)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		INSERT INTO stock
		(
			id_produit,
			quantite,
			emplacement
		)
		VALUES (?, ?, ?)
	`,
		s.IDProduit,
		s.Quantite,
		s.Emplacement,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Stock ajouté",
	})
}

func UpdateStock(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPut {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var s models.Stock

	err := json.NewDecoder(r.Body).Decode(&s)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE stock
		SET
			id_produit = ?,
			quantite = ?,
			emplacement = ?
		WHERE
			id_stock = ?
	`,
		s.IDProduit,
		s.Quantite,
		s.Emplacement,
		s.IDStock,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Stock modifié",
	})
}

func DeleteStock(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodDelete {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var s models.Stock

	err := json.NewDecoder(r.Body).Decode(&s)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM stock
		WHERE id_stock = ?
	`,
		s.IDStock,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Stock supprimé",
	})
}
