package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

// MissionCollecteBenevole représente une collecte à laquelle le bénévole
// connecté a été affecté.
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

// ServiceBenevole représente un service auquel le bénévole connecté est inscrit.
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

// GetPlanningBenevole renvoie uniquement les missions et inscriptions du
// bénévole connecté. Son identifiant provient du JWT et non d'un paramètre URL.
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
        WHERE i.id_utilisateur = ? AND i.statut != 'ANNULE'
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
