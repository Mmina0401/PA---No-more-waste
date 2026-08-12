package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

type Benevole struct {
	ID          int      `json:"id_utilisateur"`
	Nom         string   `json:"nom"`
	Prenom      string   `json:"prenom"`
	Email       string   `json:"email"`
	Telephone   *string  `json:"telephone"`
	Ville       *string  `json:"ville"`
	Actif       bool     `json:"actif"`
	Competences []string `json:"competences"`
}

type MissionCollecteBenevole struct {
	IDCollecte   int    `json:"id_collecte"`
	DateCollecte string `json:"date_collecte"`
	HeureDebut   string `json:"heure_debut"`
	HeureFin     string `json:"heure_fin"`
	Adresse      string `json:"adresse"`
	Ville        string `json:"ville"`
	CodePostal   string `json:"code_postal"`
	Statut       string `json:"statut"`
	RoleCollecte string `json:"role_collecte"`
	HeureArrivee string `json:"heure_arrivee"`
	HeureDepart  string `json:"heure_depart"`
}

type ServiceBenevole struct {
	IDService         int    `json:"id_service"`
	Nom               string `json:"nom"`
	Lieu              string `json:"lieu"`
	DateService       string `json:"date_service"`
	HeureDebut        string `json:"heure_debut"`
	HeureFin          string `json:"heure_fin"`
	StatutService     string `json:"statut_service"`
	StatutInscription string `json:"statut_inscription"`
}

func GetBenevoles(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT id_utilisateur, nom, prenom, email, telephone, ville, actif
		FROM utilisateur
		WHERE role = 'BENEVOLE'
		ORDER BY nom, prenom
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var benevoles []Benevole

	for rows.Next() {
		var b Benevole

		err := rows.Scan(
			&b.ID,
			&b.Nom,
			&b.Prenom,
			&b.Email,
			&b.Telephone,
			&b.Ville,
			&b.Actif,
		)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		competenceRows, err := config.DB.Query(`
			SELECT c.nom
			FROM competence c
			INNER JOIN benevole_competence bc
				ON bc.id_competence = c.id_competence
			WHERE bc.id_utilisateur = ?
			ORDER BY c.nom
		`, b.ID)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		for competenceRows.Next() {
			var competence string

			if err := competenceRows.Scan(&competence); err != nil {
				competenceRows.Close()
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}

			b.Competences = append(b.Competences, competence)
		}

		competenceRows.Close()
		benevoles = append(benevoles, b)
	}

	if err = rows.Err(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(benevoles)
}

func UpdateBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var data struct {
		IDUtilisateur int    `json:"id_utilisateur"`
		Nom           string `json:"nom"`
		Prenom        string `json:"prenom"`
		Email         string `json:"email"`
		Telephone     string `json:"telephone"`
		Ville         string `json:"ville"`
		Actif         bool   `json:"actif"`
		Competences   []int  `json:"competences"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.IDUtilisateur <= 0 {
		http.Error(w, "Identifiant invalide", http.StatusBadRequest)
		return
	}

	if data.Nom == "" || data.Prenom == "" || data.Email == "" {
		http.Error(w, "Nom, prénom et email obligatoires", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		UPDATE utilisateur
		SET nom = ?, prenom = ?, email = ?, telephone = ?, ville = ?, actif = ?
		WHERE id_utilisateur = ? AND role = 'BENEVOLE'
	`,
		data.Nom,
		data.Prenom,
		data.Email,
		data.Telephone,
		data.Ville,
		data.Actif,
		data.IDUtilisateur,
	)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		DELETE FROM benevole_competence
		WHERE id_utilisateur = ?
	`, data.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	for _, idCompetence := range data.Competences {
		_, err = tx.Exec(`
			INSERT INTO benevole_competence
			(id_utilisateur, id_competence)
			VALUES (?, ?)
		`, data.IDUtilisateur, idCompetence)
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
		"message": "Bénévole mis à jour avec succès",
	})
}

func AddDisponibiliteBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var data struct {
		IDUtilisateur     int    `json:"id_utilisateur"`
		DateDisponibilite string `json:"date_disponibilite"`
		HeureDebut        string `json:"heure_debut"`
		HeureFin          string `json:"heure_fin"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.IDUtilisateur <= 0 || data.DateDisponibilite == "" {
		http.Error(w, "Informations manquantes", http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
		INSERT INTO disponibilite_benevole
		(id_utilisateur, date_disponibilite, heure_debut, heure_fin)
		VALUES (?, ?, ?, ?)
	`, data.IDUtilisateur, data.DateDisponibilite, data.HeureDebut, data.HeureFin)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Disponibilité ajoutée avec succès",
	})
}

func GetPlanningBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "connexion requise", http.StatusUnauthorized)
		return
	}

	collectes := []MissionCollecteBenevole{}
	rows, err := config.DB.Query(`
		SELECT
			c.id_collecte,
			DATE_FORMAT(c.date_collecte, '%Y-%m-%d'),
			TIME_FORMAT(c.heure_debut, '%H:%i'),
			TIME_FORMAT(c.heure_fin, '%H:%i'),
			c.adresse,
			c.ville,
			c.code_postal,
			c.statut,
			cb.role_collecte,
			COALESCE(TIME_FORMAT(cb.heure_arrivee, '%H:%i'), ''),
			COALESCE(TIME_FORMAT(cb.heure_depart, '%H:%i'), '')
		FROM collecte_benevole cb
		JOIN collecte c ON c.id_collecte = cb.id_collecte
		WHERE cb.id_utilisateur = ?
		ORDER BY c.date_collecte ASC, c.heure_debut ASC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	for rows.Next() {
		var mission MissionCollecteBenevole
		if err := rows.Scan(
			&mission.IDCollecte,
			&mission.DateCollecte,
			&mission.HeureDebut,
			&mission.HeureFin,
			&mission.Adresse,
			&mission.Ville,
			&mission.CodePostal,
			&mission.Statut,
			&mission.RoleCollecte,
			&mission.HeureArrivee,
			&mission.HeureDepart,
		); err != nil {
			rows.Close()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		collectes = append(collectes, mission)
	}
	rows.Close()

	services := []ServiceBenevole{}
	rowsServices, err := config.DB.Query(`
		SELECT
			s.id_service,
			s.nom,
			COALESCE(s.lieu, ''),
			COALESCE(DATE_FORMAT(s.date_service, '%Y-%m-%d'), ''),
			COALESCE(TIME_FORMAT(s.heure_debut, '%H:%i'), ''),
			COALESCE(TIME_FORMAT(s.heure_fin, '%H:%i'), ''),
			s.statut,
			i.statut
		FROM inscription_service i
		JOIN service s ON s.id_service = i.id_service
		WHERE i.id_utilisateur = ?
		AND i.statut != 'ANNULE'
		ORDER BY s.date_service ASC, s.heure_debut ASC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rowsServices.Close()

	for rowsServices.Next() {
		var service ServiceBenevole
		if err := rowsServices.Scan(
			&service.IDService,
			&service.Nom,
			&service.Lieu,
			&service.DateService,
			&service.HeureDebut,
			&service.HeureFin,
			&service.StatutService,
			&service.StatutInscription,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		services = append(services, service)
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"collectes": collectes,
		"services":  services,
	})
}
