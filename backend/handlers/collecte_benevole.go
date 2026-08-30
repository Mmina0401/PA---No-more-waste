// API AFFECTATION BENEVOLE/COLLECTE — Affectation réelle d'un bénévole à une collecte
package handlers

import (
	"database/sql"
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

		if err := rows.Scan(
			&b.IDCollecte,
			&b.IDUtilisateur,
			&b.RoleCollecte,
			&b.HeureArrivee,
			&b.HeureDepart,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		benevoles = append(benevoles, b)
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(benevoles)
}

func CreateCollecteBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var b models.CollecteBenevole

	if err := json.NewDecoder(r.Body).Decode(&b); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	if b.IDCollecte <= 0 || b.IDUtilisateur <= 0 {
		http.Error(w, "Collecte et bénévole obligatoires", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer tx.Rollback()

	// Vérifie le nombre de places disponibles
	var nombreBenevolesRequis int

	err = tx.QueryRow(`
		SELECT nombre_benevoles_requis
		FROM offre_benevole
		WHERE type_evenement = 'COLLECTE'
		AND id_evenement = ?
		AND statut = 'OUVERTE'
		ORDER BY id_offre DESC
		LIMIT 1
		FOR UPDATE
	`, b.IDCollecte).Scan(&nombreBenevolesRequis)

	if err != nil && err != sql.ErrNoRows {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if err == nil {
		var nombreAffectes int

		if err := tx.QueryRow(`
			SELECT COUNT(*)
			FROM collecte_benevole
			WHERE id_collecte = ?
		`, b.IDCollecte).Scan(&nombreAffectes); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		if nombreAffectes >= nombreBenevolesRequis {
			http.Error(
				w,
				"Cette offre est complète : toutes les places bénévoles sont déjà attribuées",
				http.StatusConflict,
			)
			return
		}
	}

	_, err = tx.Exec(`
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
		http.Error(
			w,
			"Ce bénévole est déjà affecté à cette collecte ou les données sont invalides",
			http.StatusConflict,
		)
		return
	}

	// La réponse du bénévole à l'offre devient acceptée dès son affectation.
	_, err = tx.Exec(`
		UPDATE offre_benevole_reponse obr
		INNER JOIN offre_benevole ob
			ON ob.id_offre = obr.id_offre
		SET
			obr.statut = 'ACCEPTEE',
			obr.date_decision = NOW()
		WHERE ob.type_evenement = 'COLLECTE'
		AND ob.id_evenement = ?
		AND obr.id_utilisateur = ?
		AND obr.statut = 'EN_ATTENTE'
	`, b.IDCollecte, b.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	// Si toutes les places sont attribuées, l'offre est fermée.
	if err == nil {
		var idOffre int
		var requis int

		errOffre := tx.QueryRow(`
			SELECT id_offre, nombre_benevoles_requis
			FROM offre_benevole
			WHERE type_evenement = 'COLLECTE'
			AND id_evenement = ?
			AND statut = 'OUVERTE'
			ORDER BY id_offre DESC
			LIMIT 1
			FOR UPDATE
		`, b.IDCollecte).Scan(&idOffre, &requis)

		if errOffre == nil {
			var affectes int
			if err := tx.QueryRow(`
				SELECT COUNT(*)
				FROM collecte_benevole
				WHERE id_collecte = ?
			`, b.IDCollecte).Scan(&affectes); err != nil {
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}

			if requis > 0 && affectes >= requis {
				if _, err := tx.Exec(`
					UPDATE offre_benevole
					SET statut = 'FERMEE'
					WHERE id_offre = ?
				`, idOffre); err != nil {
					http.Error(w, err.Error(), http.StatusInternalServerError)
					return
				}
			}
		} else if errOffre != sql.ErrNoRows {
			http.Error(w, errOffre.Error(), http.StatusInternalServerError)
			return
		}
	}

	if err := tx.Commit(); err != nil {
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

	if err := json.NewDecoder(r.Body).Decode(&b); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
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

	if err := json.NewDecoder(r.Body).Decode(&b); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
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
