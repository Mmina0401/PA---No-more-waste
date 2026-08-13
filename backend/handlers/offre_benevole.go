package handlers

import (
	"encoding/json"
	"net/http"
	"time"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

type OffreBenevole struct {
	IDOffre       int         `json:"id_offre"`
	TypeEvenement string      `json:"type_evenement"`
	IDEvenement   int         `json:"id_evenement"`
	Titre         string      `json:"titre"`
	Description   string      `json:"description"`
	HeureDebut    string      `json:"heure_debut"`
	HeureFin      string      `json:"heure_fin"`
	Statut        string      `json:"statut"`
	Jours         []JourOffre `json:"jours"`
}

type JourOffre struct {
	IDJour   int    `json:"id_jour"`
	DateJour string `json:"date_jour"`
}

func CreateOffreBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var data struct {
		TypeEvenement         string `json:"type_evenement"`
		IDEvenement           int    `json:"id_evenement"`
		Titre                 string `json:"titre"`
		Description           string `json:"description"`
		NombreBenevolesRequis int    `json:"nombre_benevoles_requis"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.TypeEvenement != "COLLECTE" && data.TypeEvenement != "SERVICE" {
		http.Error(
			w,
			"Le type d'événement doit être COLLECTE ou SERVICE",
			http.StatusBadRequest,
		)
		return
	}

	if data.IDEvenement <= 0 {
		http.Error(w, "Événement invalide", http.StatusBadRequest)
		return
	}

	if data.Titre == "" {
		http.Error(w, "Le titre est obligatoire", http.StatusBadRequest)
		return
	}

	if data.NombreBenevolesRequis <= 0 {
		http.Error(
			w,
			"Le nombre de bénévoles requis doit être supérieur à 0",
			http.StatusBadRequest,
		)
		return
	}

	var dateEvenement string
	var heureDebut string
	var heureFin string
	var statutEvenement string

	if data.TypeEvenement == "COLLECTE" {
		err := config.DB.QueryRow(`
			SELECT
				DATE_FORMAT(date_collecte, '%Y-%m-%d'),
				TIME_FORMAT(heure_debut, '%H:%i'),
				TIME_FORMAT(heure_fin, '%H:%i'),
				statut
			FROM collecte
			WHERE id_collecte = ?
		`, data.IDEvenement).Scan(
			&dateEvenement,
			&heureDebut,
			&heureFin,
			&statutEvenement,
		)

		if err != nil {
			http.Error(w, "Collecte introuvable", http.StatusNotFound)
			return
		}

		if statutEvenement == "ANNULEE" ||
			statutEvenement == "TERMINEE" {
			http.Error(
				w,
				"Cette collecte ne peut plus recevoir d'offre bénévole",
				http.StatusBadRequest,
			)
			return
		}
	}

	if data.TypeEvenement == "SERVICE" {
		err := config.DB.QueryRow(`
			SELECT
				DATE_FORMAT(date_service, '%Y-%m-%d'),
				TIME_FORMAT(heure_debut, '%H:%i'),
				TIME_FORMAT(heure_fin, '%H:%i'),
				statut
			FROM service
			WHERE id_service = ?
		`, data.IDEvenement).Scan(
			&dateEvenement,
			&heureDebut,
			&heureFin,
			&statutEvenement,
		)

		if err != nil {
			http.Error(w, "Service introuvable", http.StatusNotFound)
			return
		}

		if statutEvenement == "ANNULE" {
			http.Error(
				w,
				"Ce service ne peut plus recevoir d'offre bénévole",
				http.StatusBadRequest,
			)
			return
		}
	}

	if dateEvenement == "" {
		http.Error(
			w,
			"La date de l'événement est obligatoire",
			http.StatusBadRequest,
		)
		return
	}

	if heureDebut == "" || heureFin == "" {
		http.Error(
			w,
			"Les horaires de l'événement sont obligatoires",
			http.StatusBadRequest,
		)
		return
	}

	if dateEvenement < time.Now().Format("2006-01-02") {
		http.Error(
			w,
			"Impossible de créer une offre pour un événement passé",
			http.StatusBadRequest,
		)
		return
	}

	var existe int

	err := config.DB.QueryRow(`
		SELECT COUNT(*)
		FROM offre_benevole
		WHERE type_evenement = ?
		AND id_evenement = ?
		AND statut = 'OUVERTE'
	`,
		data.TypeEvenement,
		data.IDEvenement,
	).Scan(&existe)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if existe > 0 {
		http.Error(
			w,
			"Une offre ouverte existe déjà pour cet événement",
			http.StatusConflict,
		)
		return
	}

	tx, err := config.DB.Begin()

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	resultat, err := tx.Exec(`
		INSERT INTO offre_benevole
		(
			type_evenement,
			id_evenement,
			titre,
			description,
			nombre_benevoles_requis,
			heure_debut,
			heure_fin,
			statut
		)
		VALUES (?, ?, ?, ?, ?, ?, ?, 'OUVERTE')
	`,
		data.TypeEvenement,
		data.IDEvenement,
		data.Titre,
		data.Description,
		data.NombreBenevolesRequis,
		heureDebut,
		heureFin,
	)

	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	idOffre, err := resultat.LastInsertId()

	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		INSERT INTO offre_benevole_jour
		(
			id_offre,
			date_jour
		)
		VALUES (?, ?)
	`,
		idOffre,
		dateEvenement,
	)

	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"message":        "Offre bénévole créée avec succès",
		"id_offre":       idOffre,
		"type_evenement": data.TypeEvenement,
		"id_evenement":   data.IDEvenement,
	})
}

func GetOffresBenevoles(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT
			id_offre,
			type_evenement,
			id_evenement,
			titre,
			COALESCE(description, ''),
			TIME_FORMAT(heure_debut, '%H:%i'),
			TIME_FORMAT(heure_fin, '%H:%i'),
			statut
		FROM offre_benevole
		ORDER BY date_creation DESC
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	offres := []OffreBenevole{}

	for rows.Next() {
		var offre OffreBenevole

		if err := rows.Scan(
			&offre.IDOffre,
			&offre.TypeEvenement,
			&offre.IDEvenement,
			&offre.Titre,
			&offre.Description,
			&offre.HeureDebut,
			&offre.HeureFin,
			&offre.Statut,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		rowsJours, err := config.DB.Query(`
			SELECT id_jour, DATE_FORMAT(date_jour, '%Y-%m-%d')
			FROM offre_benevole_jour
			WHERE id_offre = ?
			ORDER BY date_jour
		`, offre.IDOffre)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		for rowsJours.Next() {
			var jour JourOffre

			if err := rowsJours.Scan(
				&jour.IDJour,
				&jour.DateJour,
			); err != nil {
				rowsJours.Close()
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}

			offre.Jours = append(offre.Jours, jour)
		}

		rowsJours.Close()
		offres = append(offres, offre)
	}

	json.NewEncoder(w).Encode(offres)
}

func RepondreOffreBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "Connexion requise", http.StatusUnauthorized)
		return
	}

	var data struct {
		IDOffre int   `json:"id_offre"`
		IDJours []int `json:"id_jours"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.IDOffre <= 0 {
		http.Error(w, "Offre invalide", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		DELETE FROM offre_benevole_reponse
		WHERE id_offre = ?
		AND id_utilisateur = ?
	`,
		data.IDOffre,
		informations.IDUtilisateur,
	)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	for _, idJour := range data.IDJours {
		_, err = tx.Exec(`
			INSERT INTO offre_benevole_reponse
			(id_offre, id_jour, id_utilisateur)
			VALUES (?, ?, ?)
		`,
			data.IDOffre,
			idJour,
			informations.IDUtilisateur,
		)

		if err != nil {
			tx.Rollback()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Disponibilités enregistrées avec succès",
	})
}

func GetReponsesOffreBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	idOffre := r.URL.Query().Get("id_offre")

	if idOffre == "" {
		http.Error(w, "id_offre manquant", http.StatusBadRequest)
		return
	}

	rows, err := config.DB.Query(`
		SELECT
			obr.id_jour,
			DATE_FORMAT(obj.date_jour, '%Y-%m-%d'),
			u.id_utilisateur,
			u.nom,
			u.prenom,
			u.email
		FROM offre_benevole_reponse obr
		INNER JOIN offre_benevole_jour obj
			ON obj.id_jour = obr.id_jour
		INNER JOIN utilisateur u
			ON u.id_utilisateur = obr.id_utilisateur
		WHERE obr.id_offre = ?
		ORDER BY obj.date_jour, u.nom, u.prenom
	`, idOffre)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	type Reponse struct {
		IDJour        int    `json:"id_jour"`
		DateJour      string `json:"date_jour"`
		IDUtilisateur int    `json:"id_utilisateur"`
		Nom           string `json:"nom"`
		Prenom        string `json:"prenom"`
		Email         string `json:"email"`
	}

	reponses := []Reponse{}

	for rows.Next() {
		var reponse Reponse

		if err := rows.Scan(
			&reponse.IDJour,
			&reponse.DateJour,
			&reponse.IDUtilisateur,
			&reponse.Nom,
			&reponse.Prenom,
			&reponse.Email,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		reponses = append(reponses, reponse)
	}

	json.NewEncoder(w).Encode(reponses)
}

func GetMesReponsesOffresBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "Connexion requise", http.StatusUnauthorized)
		return
	}

	rows, err := config.DB.Query(`
		SELECT
			id_offre,
			id_jour
		FROM offre_benevole_reponse
		WHERE id_utilisateur = ?
		ORDER BY id_offre, id_jour
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	reponses := map[int][]int{}

	for rows.Next() {
		var idOffre int
		var idJour int

		if err := rows.Scan(
			&idOffre,
			&idJour,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		reponses[idOffre] = append(
			reponses[idOffre],
			idJour,
		)
	}

	resultat := []map[string]interface{}{}

	for idOffre, jours := range reponses {
		resultat = append(
			resultat,
			map[string]interface{}{
				"id_offre": idOffre,
				"id_jours": jours,
			},
		)
	}

	json.NewEncoder(w).Encode(resultat)
}
