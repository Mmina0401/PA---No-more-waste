package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetCollecte(w http.ResponseWriter, r *http.Request) {

	id := r.URL.Query().Get("id")

	var c models.Collecte

	err := config.DB.QueryRow(`
        SELECT
            id_collecte,
            id_utilisateur,
            id_vehicule,
            date_collecte,
            heure_debut,
            heure_fin,
            adresse,
            ville,
            code_postal,
            commentaire,
            statut
        FROM collecte
        WHERE id_collecte=?
    `, id).Scan(
		&c.IDCollecte,
		&c.IDUtilisateur,
		&c.IDVehicule,
		&c.DateCollecte,
		&c.HeureDebut,
		&c.HeureFin,
		&c.Adresse,
		&c.Ville,
		&c.CodePostal,
		&c.Commentaire,
		&c.Statut,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(c)
}

func CreateCollecte(w http.ResponseWriter, r *http.Request) {

	var c models.Collecte

	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	result, err := config.DB.Exec(`
        INSERT INTO collecte (
            id_utilisateur,
            id_vehicule,
            date_collecte,
            heure_debut,
            heure_fin,
            adresse,
            ville,
            code_postal,
            commentaire,
            statut
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `,
		c.IDUtilisateur,
		c.IDVehicule,
		c.DateCollecte,
		c.HeureDebut,
		c.HeureFin,
		c.Adresse,
		c.Ville,
		c.CodePostal,
		c.Commentaire,
		c.Statut,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	c.IDCollecte = int(id)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(c)
}

func UpdateCollecte(w http.ResponseWriter, r *http.Request) {

	var c models.Collecte

	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
        UPDATE collecte
        SET
            id_utilisateur=?,
            id_vehicule=?,
            date_collecte=?,
            heure_debut=?,
            heure_fin=?,
            adresse=?,
            ville=?,
            code_postal=?,
            commentaire=?,
            statut=?
        WHERE id_collecte=?
    `,
		c.IDUtilisateur,
		c.IDVehicule,
		c.DateCollecte,
		c.HeureDebut,
		c.HeureFin,
		c.Adresse,
		c.Ville,
		c.CodePostal,
		c.Commentaire,
		c.Statut,
		c.IDCollecte,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Collecte modifiée",
	})
}

func DeleteCollecte(w http.ResponseWriter, r *http.Request) {

	var body struct {
		IDCollecte int `json:"id_collecte"`
	}

	err := json.NewDecoder(r.Body).Decode(&body)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(
		"DELETE FROM collecte WHERE id_collecte=?",
		body.IDCollecte,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Collecte supprimée",
	})
}
func GetCollectes(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT
			id_collecte,
			id_utilisateur,
			id_vehicule,
			date_collecte,
			heure_debut,
			heure_fin,
			adresse,
			ville,
			code_postal,
			commentaire,
			statut
		FROM collecte
		ORDER BY date_collecte DESC, id_collecte DESC
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	collectes := []models.Collecte{}

	for rows.Next() {
		var c models.Collecte

		if err := rows.Scan(
			&c.IDCollecte,
			&c.IDUtilisateur,
			&c.IDVehicule,
			&c.DateCollecte,
			&c.HeureDebut,
			&c.HeureFin,
			&c.Adresse,
			&c.Ville,
			&c.CodePostal,
			&c.Commentaire,
			&c.Statut,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		collectes = append(collectes, c)
	}

	if err := rows.Err(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(collectes)
}
