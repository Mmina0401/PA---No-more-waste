package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func GetServices(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	rows, err := config.DB.Query(`
		SELECT
			s.id_service, s.nom, s.description, s.lieu, s.date_service,
			s.heure_debut, s.heure_fin, s.capacite_max, s.statut,
			COUNT(i.id_utilisateur) AS nb_inscrits
		FROM service s
		LEFT JOIN inscription_service i
			ON i.id_service = s.id_service AND i.statut != 'ANNULE'
		GROUP BY s.id_service
		ORDER BY s.date_service, s.heure_debut
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()
	var services []models.Service
	for rows.Next() {
		var s models.Service
		if err := rows.Scan(
			&s.IDService, &s.Nom, &s.Description, &s.Lieu, &s.DateService,
			&s.HeureDebut, &s.HeureFin, &s.CapaciteMax, &s.Statut, &s.NbInscrits,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		services = append(services, s)
	}
	json.NewEncoder(w).Encode(services)
}

// GetService renvoie un seul service : /api/services/get?id=3
func GetService(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	id := r.URL.Query().Get("id")
	if id == "" {
		http.Error(w, "paramètre id manquant", http.StatusBadRequest)
		return
	}

	var s models.Service
	err := config.DB.QueryRow(`
		SELECT id_service, nom, description, lieu, date_service,
			   heure_debut, heure_fin, capacite_max, statut
		FROM service WHERE id_service = ?
	`, id).Scan(
		&s.IDService, &s.Nom, &s.Description, &s.Lieu, &s.DateService,
		&s.HeureDebut, &s.HeureFin, &s.CapaciteMax, &s.Statut,
	)
	if err != nil {
		http.Error(w, "service introuvable", http.StatusNotFound)
		return
	}

	json.NewEncoder(w).Encode(s)
}

func CreateService(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var s models.Service
	if err := json.NewDecoder(r.Body).Decode(&s); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if s.Nom == "" {
		http.Error(w, "le nom du service est obligatoire", http.StatusBadRequest)
		return
	}
	if s.Statut == "" {
		s.Statut = "OUVERT"
	}

	result, err := config.DB.Exec(`
		INSERT INTO service (nom, description, lieu, date_service, heure_debut, heure_fin, capacite_max, statut)
		VALUES (?,?,?,?,?,?,?,?)
	`, s.Nom, s.Description, s.Lieu, s.DateService, s.HeureDebut, s.HeureFin, s.CapaciteMax, s.Statut)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	s.IDService = int(id)
	json.NewEncoder(w).Encode(s)
}

func UpdateService(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var s models.Service
	if err := json.NewDecoder(r.Body).Decode(&s); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if s.IDService == 0 {
		http.Error(w, "id_service manquant", http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
		UPDATE service
		SET nom=?, description=?, lieu=?, date_service=?, heure_debut=?, heure_fin=?, capacite_max=?, statut=?
		WHERE id_service=?
	`, s.Nom, s.Description, s.Lieu, s.DateService, s.HeureDebut, s.HeureFin, s.CapaciteMax, s.Statut, s.IDService)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Service modifié"})
}

func DeleteService(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var body struct {
		IDService int `json:"id_service"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	// ON DELETE CASCADE supprime aussi les inscriptions liées à ce service
	_, err := config.DB.Exec(`DELETE FROM service WHERE id_service = ?`, body.IDService)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Service supprimé"})
}

// GetInscriptions renvoie les inscriptions d'un service, avec le nom du
// bénévole/adhérent inscrit (jointure sur utilisateur).
// /api/inscriptions?id_service=3
func GetInscriptions(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	idService := r.URL.Query().Get("id_service")
	if idService == "" {
		http.Error(w, "paramètre id_service manquant", http.StatusBadRequest)
		return
	}

	rows, err := config.DB.Query(`
		SELECT i.id_service, i.id_utilisateur, i.date_inscription, i.statut,
			   u.nom, u.prenom
		FROM inscription_service i
		JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur
		WHERE i.id_service = ?
		ORDER BY i.date_inscription
	`, idService)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var inscriptions []models.InscriptionService
	for rows.Next() {
		var i models.InscriptionService
		if err := rows.Scan(
			&i.IDService, &i.IDUtilisateur, &i.DateInscription, &i.Statut,
			&i.NomUtilisateur, &i.PrenomUtilisateur,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		inscriptions = append(inscriptions, i)
	}

	json.NewEncoder(w).Encode(inscriptions)
}

// CreateInscription inscrit un utilisateur à un service.
// Refuse si le service est déjà complet ou si la personne est déjà inscrite.
func CreateInscription(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var i models.InscriptionService
	if err := json.NewDecoder(r.Body).Decode(&i); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if i.IDService == 0 || i.IDUtilisateur == 0 {
		http.Error(w, "id_service et id_utilisateur sont obligatoires", http.StatusBadRequest)
		return
	}
	// Vérifie la capacité restante avant d'inscrire
	var capaciteMax *int
	var nbInscrits int
	err := config.DB.QueryRow(`SELECT capacite_max FROM service WHERE id_service = ?`, i.IDService).Scan(&capaciteMax)
	if err != nil {
		http.Error(w, "service introuvable", http.StatusNotFound)
		return
	}
	config.DB.QueryRow(`
		SELECT COUNT(*) FROM inscription_service
		WHERE id_service = ? AND statut != 'ANNULE'
	`, i.IDService).Scan(&nbInscrits)
	if capaciteMax != nil && nbInscrits >= *capaciteMax {
		http.Error(w, "ce service est complet", http.StatusConflict)
		return
	}
	if i.Statut == "" {
		i.Statut = "INSCRIT"
	}

	_, err = config.DB.Exec(`
		INSERT INTO inscription_service (id_service, id_utilisateur, statut)
		VALUES (?,?,?)
	`, i.IDService, i.IDUtilisateur, i.Statut)

	if err != nil {
		http.Error(w, "cette personne est déjà inscrite à ce service", http.StatusConflict)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Inscription enregistrée"})
}

// UpdateInscription change le statut d'une inscription (ex: CONFIRME, ANNULE)
func UpdateInscription(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var i models.InscriptionService
	if err := json.NewDecoder(r.Body).Decode(&i); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
		UPDATE inscription_service SET statut = ?
		WHERE id_service = ? AND id_utilisateur = ?
	`, i.Statut, i.IDService, i.IDUtilisateur)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Inscription modifiée"})
}

// DeleteInscription désinscrit complètement une personne d'un service.
func DeleteInscription(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var i models.InscriptionService
	if err := json.NewDecoder(r.Body).Decode(&i); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
		DELETE FROM inscription_service
		WHERE id_service = ? AND id_utilisateur = ?
	`, i.IDService, i.IDUtilisateur)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Inscription supprimée"})
}
