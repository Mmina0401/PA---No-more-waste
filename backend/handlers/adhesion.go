package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

// GetAdhesions renvoie les adhésions. Filtre optionnel :
// /api/adhesions?id_utilisateur=3 -> historique d'un seul commerçant
func GetAdhesions(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	requete := `
		SELECT
			id_adhesion, id_utilisateur, date_debut, date_fin, montant,
			date_paiement, mode_paiement, statut, rappel_envoye
		FROM adhesion
	`
	arguments := []interface{}{}

	if idUtilisateur := r.URL.Query().Get("id_utilisateur"); idUtilisateur != "" {
		requete += " WHERE id_utilisateur = ?"
		arguments = append(arguments, idUtilisateur)
	}
	requete += " ORDER BY date_debut DESC"

	rows, err := config.DB.Query(requete, arguments...)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var adhesions []models.Adhesion

	for rows.Next() {

		var a models.Adhesion

		err := rows.Scan(
			&a.ID, &a.IDUtilisateur, &a.DateDebut, &a.DateFin, &a.Montant,
			&a.DatePaiement, &a.ModePaiement, &a.Statut, &a.RappelEnvoye,
		)

		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		adhesions = append(adhesions, a)
	}

	json.NewEncoder(w).Encode(adhesions)
}

func CreateAdhesion(w http.ResponseWriter, r *http.Request) {

	var a models.Adhesion

	err := json.NewDecoder(r.Body).Decode(&a)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	result, err := config.DB.Exec(`
		INSERT INTO adhesion(
			id_utilisateur, date_debut, date_fin, montant,
			date_paiement, mode_paiement, statut, rappel_envoye
		)
		VALUES(?,?,?,?,?,?,?,?)
	`,
		a.IDUtilisateur, a.DateDebut, a.DateFin, a.Montant,
		a.DatePaiement, a.ModePaiement, a.Statut, a.RappelEnvoye,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	a.ID = int(id)

	json.NewEncoder(w).Encode(a)
}

func UpdateAdhesion(w http.ResponseWriter, r *http.Request) {

	var a models.Adhesion

	err := json.NewDecoder(r.Body).Decode(&a)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE adhesion
		SET id_utilisateur=?, date_debut=?, date_fin=?, montant=?,
			date_paiement=?, mode_paiement=?, statut=?, rappel_envoye=?
		WHERE id_adhesion=?
	`,
		a.IDUtilisateur, a.DateDebut, a.DateFin, a.Montant,
		a.DatePaiement, a.ModePaiement, a.Statut, a.RappelEnvoye,
		a.ID,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Adhésion modifiée",
	})
}

func DeleteAdhesion(w http.ResponseWriter, r *http.Request) {

	var a models.Adhesion

	err := json.NewDecoder(r.Body).Decode(&a)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM adhesion
		WHERE id_adhesion=?
	`, a.ID)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Adhésion supprimée",
	})
}
