package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetCollectesBenevoles(w http.ResponseWriter, r *http.Request) {

	var benevoles []models.CollecteBenevole

	rows, err := config.DB.Query(`
		SELECT
			id_collecte,
			id_utilisateur,
			role_collecte,
			heure_arrivee,
			heure_depart
		FROM collecte_benevole
	`)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	defer rows.Close()

	for rows.Next() {

		var b models.CollecteBenevole

		rows.Scan(
			&b.IDCollecte,
			&b.IDUtilisateur,
			&b.RoleCollecte,
			&b.HeureArrivee,
			&b.HeureDepart,
		)

		benevoles = append(benevoles, b)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(benevoles)
}

func CreateCollecteBenevole(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var b models.CollecteBenevole

	err := json.NewDecoder(r.Body).Decode(&b)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		INSERT INTO collecte_benevole
		(
			id_collecte,
			id_utilisateur,
			role_collecte,
			heure_arrivee,
			heure_depart
		)
		VALUES (?, ?, ?, ?, ?)
	`,
		b.IDCollecte,
		b.IDUtilisateur,
		b.RoleCollecte,
		b.HeureArrivee,
		b.HeureDepart,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Bénévole ajouté à la collecte",
	})
}

func UpdateCollecteBenevole(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodPut {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var b models.CollecteBenevole

	err := json.NewDecoder(r.Body).Decode(&b)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE collecte_benevole
		SET
			role_collecte = ?,
			heure_arrivee = ?,
			heure_depart = ?
		WHERE
			id_collecte = ?
			AND id_utilisateur = ?
	`,
		b.RoleCollecte,
		b.HeureArrivee,
		b.HeureDepart,
		b.IDCollecte,
		b.IDUtilisateur,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Bénévole modifié",
	})
}

func DeleteCollecteBenevole(w http.ResponseWriter, r *http.Request) {

	if r.Method != http.MethodDelete {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var b models.CollecteBenevole

	err := json.NewDecoder(r.Body).Decode(&b)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM collecte_benevole
		WHERE
			id_collecte = ?
			AND id_utilisateur = ?
	`,
		b.IDCollecte,
		b.IDUtilisateur,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Bénévole supprimé",
	})
}
