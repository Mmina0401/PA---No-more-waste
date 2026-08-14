package handlers

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
)

type AffectationServiceBenevole struct {
	IDService     int    `json:"id_service"`
	IDUtilisateur int    `json:"id_utilisateur"`
	RoleService   string `json:"role_service"`
	Nom           string `json:"nom,omitempty"`
	Prenom        string `json:"prenom,omitempty"`
	Email         string `json:"email,omitempty"`
}

func GetServiceBenevoles(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	idService := r.URL.Query().Get("id_service")
	requete := `
		SELECT
			sb.id_service,
			sb.id_utilisateur,
			sb.role_service,
			u.nom,
			u.prenom,
			u.email
		FROM service_benevole sb
		JOIN utilisateur u ON u.id_utilisateur = sb.id_utilisateur
	`
	args := []interface{}{}

	if idService != "" {
		requete += " WHERE sb.id_service = ?"
		args = append(args, idService)
	}
	requete += " ORDER BY u.nom, u.prenom"

	rows, err := config.DB.Query(requete, args...)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	affectations := []AffectationServiceBenevole{}
	for rows.Next() {
		var a AffectationServiceBenevole
		if err := rows.Scan(
			&a.IDService,
			&a.IDUtilisateur,
			&a.RoleService,
			&a.Nom,
			&a.Prenom,
			&a.Email,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		affectations = append(affectations, a)
	}

	json.NewEncoder(w).Encode(affectations)
}

func CreateServiceBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var a AffectationServiceBenevole
	if err := json.NewDecoder(r.Body).Decode(&a); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	if a.IDService <= 0 || a.IDUtilisateur <= 0 {
		http.Error(w, "service et bénévole obligatoires", http.StatusBadRequest)
		return
	}

	if a.RoleService == "" {
		a.RoleService = "BENEVOLE"
	}

	var role string
	var actif bool

	err := config.DB.QueryRow(`
		SELECT role, actif
		FROM utilisateur
		WHERE id_utilisateur = ?
	`, a.IDUtilisateur).Scan(&role, &actif)

	if err != nil || role != "BENEVOLE" || !actif {
		http.Error(w, "bénévole invalide ou non validé", http.StatusBadRequest)
		return
	}

	var serviceExiste int
	err = config.DB.QueryRow(`
		SELECT COUNT(*)
		FROM service
		WHERE id_service = ?
		AND statut != 'ANNULE'
	`, a.IDService).Scan(&serviceExiste)

	if err != nil || serviceExiste == 0 {
		http.Error(w, "service introuvable ou annulé", http.StatusNotFound)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer tx.Rollback()

	// On utilise le nombre de bénévoles demandé dans l'offre,
	// pas la capacité des participants/adhérents du service.
	var nombreBenevolesRequis int

	err = tx.QueryRow(`
		SELECT nombre_benevoles_requis
		FROM offre_benevole
		WHERE type_evenement = 'SERVICE'
		AND id_evenement = ?
		AND statut = 'OUVERTE'
		ORDER BY id_offre DESC
		LIMIT 1
		FOR UPDATE
	`, a.IDService).Scan(&nombreBenevolesRequis)

	if err != nil && err != sql.ErrNoRows {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if err == nil {
		var nombreAffectes int

		if err := tx.QueryRow(`
			SELECT COUNT(*)
			FROM service_benevole
			WHERE id_service = ?
		`, a.IDService).Scan(&nombreAffectes); err != nil {
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
		INSERT INTO service_benevole
			(id_service, id_utilisateur, role_service)
		VALUES (?, ?, ?)
	`, a.IDService, a.IDUtilisateur, a.RoleService)

	if err != nil {
		http.Error(
			w,
			"Ce bénévole est déjà affecté à ce service ou les données sont invalides",
			http.StatusConflict,
		)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Bénévole affecté au service",
	})
}

func DeleteServiceBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var a AffectationServiceBenevole
	if err := json.NewDecoder(r.Body).Decode(&a); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
		DELETE FROM service_benevole
		WHERE id_service = ?
		AND id_utilisateur = ?
	`, a.IDService, a.IDUtilisateur)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Affectation supprimée",
	})
}
