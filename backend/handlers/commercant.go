package handlers

import (
	"database/sql"
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

type CommercantEspace struct {
	IDUtilisateur   int                  `json:"id_utilisateur"`
	Nom             string               `json:"nom"`
	Prenom          string               `json:"prenom"`
	Email           string               `json:"email"`
	Telephone       *string              `json:"telephone"`
	Adresse         *string              `json:"adresse"`
	Ville           *string              `json:"ville"`
	CodePostal      *string              `json:"code_postal"`
	RaisonSociale   *string              `json:"raison_sociale"`
	Siret           *string              `json:"siret"`
	SecteurActivite *string              `json:"secteur_activite"`
	AdhesionActive  *AdhesionCommercant  `json:"adhesion_active"`
	Collectes       []CollecteCommercant `json:"collectes"`
	Services        []ServiceCommercant  `json:"services"`
}

type AdhesionCommercant struct {
	IDAdhesion int     `json:"id_adhesion"`
	DateDebut  string  `json:"date_debut"`
	DateFin    string  `json:"date_fin"`
	Montant    float64 `json:"montant"`
	Statut     string  `json:"statut"`
}

type CollecteCommercant struct {
	IDCollecte   int    `json:"id_collecte"`
	DateCollecte string `json:"date_collecte"`
	HeureDebut   string `json:"heure_debut"`
	HeureFin     string `json:"heure_fin"`
	Adresse      string `json:"adresse"`
	Ville        string `json:"ville"`
	Statut       string `json:"statut"`
}

type ServiceCommercant struct {
	IDService         int    `json:"id_service"`
	Nom               string `json:"nom"`
	DateService       string `json:"date_service"`
	HeureDebut        string `json:"heure_debut"`
	HeureFin          string `json:"heure_fin"`
	Lieu              string `json:"lieu"`
	StatutInscription string `json:"statut_inscription"`
}

type AdherentActif struct {
	IDUtilisateur int     `json:"id_utilisateur"`
	Nom           string  `json:"nom"`
	Prenom        string  `json:"prenom"`
	Email         string  `json:"email"`
	RaisonSociale *string `json:"raison_sociale"`
	DateFin       string  `json:"date_fin"`
}

func commercantAdherentActif(idUtilisateur int) (bool, error) {
	var existe int
	err := config.DB.QueryRow(`
		SELECT COUNT(*)
		FROM utilisateur u
		JOIN adhesion a ON a.id_utilisateur = u.id_utilisateur
		WHERE u.id_utilisateur = ?
		  AND u.role = 'COMMERCANT'
		  AND u.actif = TRUE
		  AND a.statut = 'ACTIVE'
		  AND CURDATE() BETWEEN a.date_debut AND a.date_fin
	`, idUtilisateur).Scan(&existe)
	return existe > 0, err
}

// GetEspaceCommercant renvoie uniquement les données du commerçant connecté.
func GetEspaceCommercant(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil {
		http.Error(w, "connexion requise", http.StatusUnauthorized)
		return
	}

	var espace CommercantEspace
	err := config.DB.QueryRow(`
		SELECT
			id_utilisateur, nom, prenom, email, telephone, adresse, ville,
			code_postal, raison_sociale, siret, secteur_activite
		FROM utilisateur
		WHERE id_utilisateur = ? AND role = 'COMMERCANT'
	`, informations.IDUtilisateur).Scan(
		&espace.IDUtilisateur, &espace.Nom, &espace.Prenom, &espace.Email,
		&espace.Telephone, &espace.Adresse, &espace.Ville, &espace.CodePostal,
		&espace.RaisonSociale, &espace.Siret, &espace.SecteurActivite,
	)
	if err != nil {
		http.Error(w, "commerçant introuvable", http.StatusNotFound)
		return
	}

	var adhesion AdhesionCommercant
	err = config.DB.QueryRow(`
		SELECT
			id_adhesion,
			DATE_FORMAT(date_debut, '%Y-%m-%d'),
			DATE_FORMAT(date_fin, '%Y-%m-%d'),
			montant,
			statut
		FROM adhesion
		WHERE id_utilisateur = ?
		  AND statut = 'ACTIVE'
		  AND CURDATE() BETWEEN date_debut AND date_fin
		ORDER BY date_fin DESC
		LIMIT 1
	`, informations.IDUtilisateur).Scan(
		&adhesion.IDAdhesion, &adhesion.DateDebut, &adhesion.DateFin,
		&adhesion.Montant, &adhesion.Statut,
	)
	if err == nil {
		espace.AdhesionActive = &adhesion
	} else if err != sql.ErrNoRows {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	espace.Collectes = []CollecteCommercant{}
	rowsCollectes, err := config.DB.Query(`
		SELECT
			id_collecte,
			DATE_FORMAT(date_collecte, '%Y-%m-%d'),
			TIME_FORMAT(heure_debut, '%H:%i'),
			TIME_FORMAT(heure_fin, '%H:%i'),
			adresse,
			ville,
			statut
		FROM collecte
		WHERE id_utilisateur = ?
		ORDER BY date_collecte DESC, heure_debut DESC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	for rowsCollectes.Next() {
		var c CollecteCommercant
		if err := rowsCollectes.Scan(
			&c.IDCollecte, &c.DateCollecte, &c.HeureDebut, &c.HeureFin,
			&c.Adresse, &c.Ville, &c.Statut,
		); err != nil {
			rowsCollectes.Close()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		espace.Collectes = append(espace.Collectes, c)
	}
	rowsCollectes.Close()

	espace.Services = []ServiceCommercant{}
	rowsServices, err := config.DB.Query(`
		SELECT
			s.id_service,
			s.nom,
			COALESCE(DATE_FORMAT(s.date_service, '%Y-%m-%d'), ''),
			COALESCE(TIME_FORMAT(s.heure_debut, '%H:%i'), ''),
			COALESCE(TIME_FORMAT(s.heure_fin, '%H:%i'), ''),
			COALESCE(s.lieu, ''),
			i.statut
		FROM inscription_service i
		JOIN service s ON s.id_service = i.id_service
		WHERE i.id_utilisateur = ?
		  AND i.statut != 'ANNULE'
		ORDER BY s.date_service DESC, s.heure_debut DESC
	`, informations.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	for rowsServices.Next() {
		var s ServiceCommercant
		if err := rowsServices.Scan(
			&s.IDService, &s.Nom, &s.DateService, &s.HeureDebut,
			&s.HeureFin, &s.Lieu, &s.StatutInscription,
		); err != nil {
			rowsServices.Close()
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		espace.Services = append(espace.Services, s)
	}
	rowsServices.Close()

	json.NewEncoder(w).Encode(espace)
}

// GetAdherentsActifs permet à l'admin de sélectionner uniquement les commerçants
// qui ont actuellement une adhésion annuelle active.
func GetAdherentsActifs(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT
			u.id_utilisateur,
			u.nom,
			u.prenom,
			u.email,
			u.raison_sociale,
			DATE_FORMAT(MAX(a.date_fin), '%Y-%m-%d')
		FROM utilisateur u
		JOIN adhesion a ON a.id_utilisateur = u.id_utilisateur
		WHERE u.role = 'COMMERCANT'
		  AND u.actif = TRUE
		  AND a.statut = 'ACTIVE'
		  AND CURDATE() BETWEEN a.date_debut AND a.date_fin
		GROUP BY u.id_utilisateur
		ORDER BY COALESCE(u.raison_sociale, u.nom), u.prenom
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	adherents := []AdherentActif{}
	for rows.Next() {
		var a AdherentActif
		if err := rows.Scan(
			&a.IDUtilisateur, &a.Nom, &a.Prenom, &a.Email,
			&a.RaisonSociale, &a.DateFin,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		adherents = append(adherents, a)
	}

	json.NewEncoder(w).Encode(adherents)
}

// ChangerStatutCommercant valide, désactive ou réactive un compte commerçant
// sans supprimer son historique ni ses adhésions.
func ChangerStatutCommercant(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var demande struct {
		IDUtilisateur int  `json:"id_utilisateur"`
		Actif         bool `json:"actif"`
	}
	if err := json.NewDecoder(r.Body).Decode(&demande); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}
	if demande.IDUtilisateur <= 0 {
		http.Error(w, "commerçant invalide", http.StatusBadRequest)
		return
	}

	resultat, err := config.DB.Exec(`
		UPDATE utilisateur
		SET actif = ?
		WHERE id_utilisateur = ? AND role = 'COMMERCANT'
	`, demande.Actif, demande.IDUtilisateur)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	nb, _ := resultat.RowsAffected()
	if nb == 0 {
		http.Error(w, "commerçant introuvable", http.StatusNotFound)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Statut du commerçant mis à jour",
	})
}
