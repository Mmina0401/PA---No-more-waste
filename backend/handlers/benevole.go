package handlers

import (
	"encoding/json"
	"net/http"
	"time"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

// ========== Espace personnel du bénévole connecté ==========

// MissionCollecteBenevole représente une collecte à laquelle le bénévole
// connecté a été affecté.
type MissionCollecteBenevole struct {
	IDCollecte          int    `json:"id_collecte"`
	DateCollecte        string `json:"date_collecte"`
	HeureDebut          string `json:"heure_debut"`
	HeureFin            string `json:"heure_fin"`
	Adresse             string `json:"adresse"`
	Ville               string `json:"ville"`
	CodePostal          string `json:"code_postal"`
	Statut              string `json:"statut"`
	RoleCollecte        string `json:"role_collecte"`
	HeureArrivee        string `json:"heure_arrivee"`
	HeureDepart         string `json:"heure_depart"`
	StatutParticipation string `json:"statut_participation"`
}

type ServiceBenevole struct {
	IDService           int    `json:"id_service"`
	Nom                 string `json:"nom"`
	Lieu                string `json:"lieu"`
	DateService         string `json:"date_service"`
	HeureDebut          string `json:"heure_debut"`
	HeureFin            string `json:"heure_fin"`
	StatutService       string `json:"statut_service"`
	RoleService         string `json:"role_service"`
	StatutParticipation string `json:"statut_participation"`
}

// GetPlanningBenevole renvoie les demandes en attente ET les affectations validées
// du bénévole connecté. Son identifiant provient du JWT.
func GetPlanningBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "connexion requise", http.StatusUnauthorized)
		return
	}

	collectes := []MissionCollecteBenevole{}

	// 1) Collectes déjà affectées par un admin
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
		JOIN collecte c
			ON c.id_collecte = cb.id_collecte
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

		mission.StatutParticipation = "AFFECTE"
		collectes = append(collectes, mission)
	}
	rows.Close()

	// 2) Collectes demandées mais pas encore affectées
	rowsAttenteCollectes, err := config.DB.Query(`
		SELECT DISTINCT
			c.id_collecte,
			DATE_FORMAT(c.date_collecte, '%Y-%m-%d'),
			TIME_FORMAT(c.heure_debut, '%H:%i'),
			TIME_FORMAT(c.heure_fin, '%H:%i'),
			c.adresse,
			c.ville,
			c.code_postal,
			c.statut
		FROM offre_benevole_reponse obr
		JOIN offre_benevole ob
			ON ob.id_offre = obr.id_offre
		JOIN collecte c
			ON c.id_collecte = ob.id_evenement
		WHERE obr.id_utilisateur = ?
		AND ob.type_evenement = 'COLLECTE'
		AND NOT EXISTS (
			SELECT 1
			FROM collecte_benevole cb
			WHERE cb.id_collecte = c.id_collecte
			AND cb.id_utilisateur = obr.id_utilisateur
		)
		ORDER BY c.date_collecte ASC, c.heure_debut ASC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	for rowsAttenteCollectes.Next() {
		var mission MissionCollecteBenevole

		if err := rowsAttenteCollectes.Scan(
			&mission.IDCollecte,
			&mission.DateCollecte,
			&mission.HeureDebut,
			&mission.HeureFin,
			&mission.Adresse,
			&mission.Ville,
			&mission.CodePostal,
			&mission.Statut,
		); err != nil {
			rowsAttenteCollectes.Close()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		mission.StatutParticipation = "EN_ATTENTE"
		collectes = append(collectes, mission)
	}
	rowsAttenteCollectes.Close()

	services := []ServiceBenevole{}

	// 3) Services déjà affectés par un admin
	rowsServices, err := config.DB.Query(`
		SELECT
			s.id_service,
			s.nom,
			COALESCE(s.lieu, ''),
			COALESCE(DATE_FORMAT(s.date_service, '%Y-%m-%d'), ''),
			COALESCE(TIME_FORMAT(s.heure_debut, '%H:%i'), ''),
			COALESCE(TIME_FORMAT(s.heure_fin, '%H:%i'), ''),
			s.statut,
			sb.role_service
		FROM service_benevole sb
		JOIN service s
			ON s.id_service = sb.id_service
		WHERE sb.id_utilisateur = ?
		ORDER BY s.date_service ASC, s.heure_debut ASC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

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
			&service.RoleService,
		); err != nil {
			rowsServices.Close()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		service.StatutParticipation = "AFFECTE"
		services = append(services, service)
	}
	rowsServices.Close()

	// 4) Services demandés mais pas encore affectés
	rowsAttenteServices, err := config.DB.Query(`
		SELECT DISTINCT
			s.id_service,
			s.nom,
			COALESCE(s.lieu, ''),
			COALESCE(DATE_FORMAT(s.date_service, '%Y-%m-%d'), ''),
			COALESCE(TIME_FORMAT(s.heure_debut, '%H:%i'), ''),
			COALESCE(TIME_FORMAT(s.heure_fin, '%H:%i'), ''),
			s.statut
		FROM offre_benevole_reponse obr
		JOIN offre_benevole ob
			ON ob.id_offre = obr.id_offre
		JOIN service s
			ON s.id_service = ob.id_evenement
		WHERE obr.id_utilisateur = ?
		AND ob.type_evenement = 'SERVICE'
		AND NOT EXISTS (
			SELECT 1
			FROM service_benevole sb
			WHERE sb.id_service = s.id_service
			AND sb.id_utilisateur = obr.id_utilisateur
		)
		ORDER BY s.date_service ASC, s.heure_debut ASC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	for rowsAttenteServices.Next() {
		var service ServiceBenevole

		if err := rowsAttenteServices.Scan(
			&service.IDService,
			&service.Nom,
			&service.Lieu,
			&service.DateService,
			&service.HeureDebut,
			&service.HeureFin,
			&service.StatutService,
		); err != nil {
			rowsAttenteServices.Close()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		service.StatutParticipation = "EN_ATTENTE"
		services = append(services, service)
	}
	rowsAttenteServices.Close()

	json.NewEncoder(w).Encode(map[string]interface{}{
		"collectes": collectes,
		"services":  services,
	})
}

func AnnulerParticipationCollecte(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "Connexion requise", http.StatusUnauthorized)
		return
	}

	var data struct {
		IDCollecte int `json:"id_collecte"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.IDCollecte <= 0 {
		http.Error(w, "Collecte invalide", http.StatusBadRequest)
		return
	}

	var dateCollecte string
	err := config.DB.QueryRow(`
		SELECT DATE_FORMAT(date_collecte, '%Y-%m-%d')
		FROM collecte
		WHERE id_collecte = ?
	`, data.IDCollecte).Scan(&dateCollecte)
	if err != nil {
		http.Error(w, "Collecte introuvable", http.StatusNotFound)
		return
	}

	if dateCollecte < time.Now().Format("2006-01-02") {
		http.Error(w, "Impossible d'annuler une participation à une collecte passée", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	resultAffectation, err := tx.Exec(`
		DELETE FROM collecte_benevole
		WHERE id_collecte = ?
		AND id_utilisateur = ?
	`, data.IDCollecte, informations.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	resultDemande, err := tx.Exec(`
		DELETE obr
		FROM offre_benevole_reponse obr
		INNER JOIN offre_benevole ob
			ON ob.id_offre = obr.id_offre
		WHERE ob.type_evenement = 'COLLECTE'
		AND ob.id_evenement = ?
		AND obr.id_utilisateur = ?
	`, data.IDCollecte, informations.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	nbAffectation, _ := resultAffectation.RowsAffected()
	nbDemande, _ := resultDemande.RowsAffected()

	if nbAffectation == 0 && nbDemande == 0 {
		tx.Rollback()
		http.Error(w, "Aucune participation ou demande trouvée pour cette collecte", http.StatusNotFound)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Participation à la collecte annulée",
	})
}

func AnnulerParticipationService(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "Connexion requise", http.StatusUnauthorized)
		return
	}

	var data struct {
		IDService int `json:"id_service"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.IDService <= 0 {
		http.Error(w, "Service invalide", http.StatusBadRequest)
		return
	}

	var dateService string
	err := config.DB.QueryRow(`
		SELECT DATE_FORMAT(date_service, '%Y-%m-%d')
		FROM service
		WHERE id_service = ?
	`, data.IDService).Scan(&dateService)
	if err != nil {
		http.Error(w, "Service introuvable", http.StatusNotFound)
		return
	}

	if dateService < time.Now().Format("2006-01-02") {
		http.Error(w, "Impossible d'annuler une participation à un service passé", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	resultAffectation, err := tx.Exec(`
		DELETE FROM service_benevole
		WHERE id_service = ?
		AND id_utilisateur = ?
	`, data.IDService, informations.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	resultDemande, err := tx.Exec(`
		DELETE obr
		FROM offre_benevole_reponse obr
		INNER JOIN offre_benevole ob
			ON ob.id_offre = obr.id_offre
		WHERE ob.type_evenement = 'SERVICE'
		AND ob.id_evenement = ?
		AND obr.id_utilisateur = ?
	`, data.IDService, informations.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	nbAffectation, _ := resultAffectation.RowsAffected()
	nbDemande, _ := resultDemande.RowsAffected()

	if nbAffectation == 0 && nbDemande == 0 {
		tx.Rollback()
		http.Error(w, "Aucune participation ou demande trouvée pour ce service", http.StatusNotFound)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Participation au service annulée",
	})
}

// ========== Gestion des bénévoles par un admin/responsable ==========

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

func GetBenevoles(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT
			id_utilisateur,
			nom,
			prenom,
			email,
			telephone,
			ville,
			actif
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
		http.Error(
			w,
			"Nom, prénom et email obligatoires",
			http.StatusBadRequest,
		)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		UPDATE utilisateur
		SET
			nom = ?,
			prenom = ?,
			email = ?,
			telephone = ?,
			ville = ?,
			actif = ?
		WHERE id_utilisateur = ?
		AND role = 'BENEVOLE'
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
		`,
			data.IDUtilisateur,
			idCompetence,
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
		"message": "Bénévole mis à jour avec succès",
	})
}

func DeleteBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var data struct {
		IDUtilisateur int `json:"id_utilisateur"`
	}

	if err := json.NewDecoder(r.Body).Decode(&data); err != nil {
		http.Error(w, "Données invalides", http.StatusBadRequest)
		return
	}

	if data.IDUtilisateur <= 0 {
		http.Error(w, "Identifiant invalide", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
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

	_, err = tx.Exec(`
		DELETE FROM disponibilite_benevole
		WHERE id_utilisateur = ?
	`, data.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		DELETE FROM collecte_benevole
		WHERE id_utilisateur = ?
	`, data.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	_, err = tx.Exec(`
		DELETE FROM service_benevole
		WHERE id_utilisateur = ?
	`, data.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	resultat, err := tx.Exec(`
		DELETE FROM utilisateur
		WHERE id_utilisateur = ?
		AND role = 'BENEVOLE'
	`, data.IDUtilisateur)
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	nbLignes, err := resultat.RowsAffected()
	if err != nil {
		tx.Rollback()
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if nbLignes == 0 {
		tx.Rollback()
		http.Error(w, "Bénévole introuvable", http.StatusNotFound)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Bénévole supprimé avec succès",
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
		(
			id_utilisateur,
			date_disponibilite,
			heure_debut,
			heure_fin
		)
		VALUES (?, ?, ?, ?)
	`,
		data.IDUtilisateur,
		data.DateDisponibilite,
		data.HeureDebut,
		data.HeureFin,
	)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Disponibilité ajoutée avec succès",
	})
}
