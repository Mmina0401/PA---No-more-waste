package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetStocks(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var stocks []models.Stock

	rows, err := config.DB.Query(`
		SELECT
			s.id_stock,
			s.id_produit,
			p.nom,
			p.code_barre,
			c.nom,
			s.quantite,
			s.emplacement,
			s.date_entree,
			s.derniere_maj
		FROM stock s
		INNER JOIN produit p
			ON p.id_produit = s.id_produit
		INNER JOIN categorie_produit c
			ON c.id_categorie = p.id_categorie
		ORDER BY p.nom ASC
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	for rows.Next() {
		var s models.Stock

		err := rows.Scan(
			&s.IDStock,
			&s.IDProduit,
			&s.NomProduit,
			&s.CodeBarre,
			&s.Categorie,
			&s.Quantite,
			&s.Emplacement,
			&s.DateEntree,
			&s.DerniereMaj,
		)

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		stocks = append(stocks, s)
	}

	if err := rows.Err(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

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
