package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetProduits(w http.ResponseWriter, r *http.Request) {

	var produits []models.Produit

	rows, err := config.DB.Query(`
		SELECT
			id_produit,
			nom,
			description,
			code_barre,
			id_categorie,
			unite,
			poids,
			actif
		FROM produit
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var p models.Produit

		err := rows.Scan(
			&p.ID,
			&p.Nom,
			&p.Description,
			&p.CodeBarre,
			&p.IDCategorie,
			&p.Unite,
			&p.Poids,
			&p.Actif,
		)

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		produits = append(produits, p)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(produits)
}

func CreateProduit(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var p models.Produit

	err := json.NewDecoder(r.Body).Decode(&p)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	result, err := config.DB.Exec(`
		INSERT INTO produit
		(
			nom,
			description,
			code_barre,
			id_categorie,
			unite,
			poids
		)
		VALUES (?, ?, ?, ?, ?, ?)
	`,
		p.Nom,
		p.Description,
		p.CodeBarre,
		p.IDCategorie,
		p.Unite,
		p.Poids,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	p.ID = int(id)

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	json.NewEncoder(w).Encode(p)
}

func UpdateProduit(w http.ResponseWriter, r *http.Request) {

	var p models.Produit

	err := json.NewDecoder(r.Body).Decode(&p)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE produit
		SET
			nom = ?,
			description = ?,
			code_barre = ?,
			id_categorie = ?,
			unite = ?,
			poids = ?,
			actif = ?
		WHERE id_produit = ?
	`,
		p.Nom,
		p.Description,
		p.CodeBarre,
		p.IDCategorie,
		p.Unite,
		p.Poids,
		p.Actif,
		p.ID,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Produit modifié",
	})
}

func DeleteProduit(w http.ResponseWriter, r *http.Request) {

	var produit models.Produit

	err := json.NewDecoder(r.Body).Decode(&produit)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM produit
		WHERE id_produit = ?
	`, produit.ID)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Produit supprimé",
	})
}
