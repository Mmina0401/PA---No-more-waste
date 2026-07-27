package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetCategories(w http.ResponseWriter, r *http.Request) {

	var categories []models.Categorie

	rows, err := config.DB.Query(`
		SELECT
			id_categorie,
			nom,
			description
		FROM categorie_produit
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var c models.Categorie

		err := rows.Scan(
			&c.ID,
			&c.Nom,
			&c.Description,
		)

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		categories = append(categories, c)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(categories)
}

func CreateCategorie(w http.ResponseWriter, r *http.Request) {

	var c models.Categorie

	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	result, err := config.DB.Exec(`
		INSERT INTO categorie_produit
		(nom, description)
		VALUES (?, ?)
	`,
		c.Nom,
		c.Description,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	c.ID = int(id)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(c)
}

func UpdateCategorie(w http.ResponseWriter, r *http.Request) {

	var c models.Categorie

	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE categorie_produit
		SET
			nom=?,
			description=?
		WHERE id_categorie=?
	`,
		c.Nom,
		c.Description,
		c.ID,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Catégorie modifiée",
	})
}

func DeleteCategorie(w http.ResponseWriter, r *http.Request) {

	var data struct {
		ID int `json:"id_categorie"`
	}

	err := json.NewDecoder(r.Body).Decode(&data)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(
		"DELETE FROM categorie_produit WHERE id_categorie=?",
		data.ID,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Catégorie supprimée",
	})
}
