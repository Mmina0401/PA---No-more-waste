package handlers

import (
	"database/sql"
	"encoding/json"
	"net/http"
	"regexp"

	"golang.org/x/crypto/bcrypt"

	"github.com/MMina040/PA-No-More-Waste/config"
)

type ServicePublic struct {
	IDService   int     `json:"id_service"`
	Nom         string  `json:"nom"`
	Description *string `json:"description"`
	Lieu        *string `json:"lieu"`
	DateService *string `json:"date_service"`
	HeureDebut  *string `json:"heure_debut"`
	HeureFin    *string `json:"heure_fin"`
	CapaciteMax *int    `json:"capacite_max"`
	NbInscrits  int     `json:"nb_inscrits"`
}

func trouverOuCreerVisiteur(nom, prenom, email string, raisonSociale, siret, secteurActivite *string) (int, error) {
	var idUtilisateur int
	var role string

	err := config.DB.QueryRow(`SELECT id_utilisateur, role FROM utilisateur WHERE email = ?`, email).Scan(&idUtilisateur, &role)

	if err == nil {

		if (role == "COMMERCANT" || role == "VISITEUR") && (raisonSociale != nil || siret != nil) {
			_, _ = config.DB.Exec(`UPDATE utilisateur SET raison_sociale = COALESCE(?, raison_sociale), siret = COALESCE(?, siret), secteur_activite = COALESCE(?, secteur_activite) WHERE id_utilisateur = ?`, raisonSociale, siret, secteurActivite, idUtilisateur)
		}
		return idUtilisateur, nil
	}

	if err != sql.ErrNoRows {
		return 0, err
	}

	motDePasseTechnique := "visiteur-" + email
	hash, errHash := bcrypt.GenerateFromPassword([]byte(motDePasseTechnique), bcrypt.DefaultCost)
	if errHash != nil {
		return 0, errHash
	}

	resultat, errCreation := config.DB.Exec(`INSERT INTO utilisateur ( nom, prenom, email, mot_de_passe, role, raison_sociale, siret, secteur_activite ) VALUES (?, ?, ?, ?, 'VISITEUR', ?, ?, ?)`, nom, prenom, email, string(hash), raisonSociale, siret, secteurActivite)
	if errCreation != nil {
		return 0, errCreation
	}

	nouvelID, _ := resultat.LastInsertId()
	return int(nouvelID), nil
}

func ObtenirServicesPublics(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	lignes, err := config.DB.Query(`SELECT s.id_service, s.nom, s.description, s.lieu, s.date_service, s.heure_debut, s.heure_fin, s.capacite_max, COUNT(CASE WHEN u.role = 'COMMERCANT' THEN 1 END) AS nb_inscrits FROM service s LEFT JOIN inscription_service i ON i.id_service = s.id_service AND i.statut != 'ANNULE' LEFT JOIN utilisateur u ON u.id_utilisateur = i.id_utilisateur WHERE s.statut = 'OUVERT' GROUP BY s.id_service ORDER BY s.date_service ASC`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer lignes.Close()

	services := []ServicePublic{}
	for lignes.Next() {
		var s ServicePublic
		if err := lignes.Scan(
			&s.IDService, &s.Nom, &s.Description, &s.Lieu, &s.DateService,
			&s.HeureDebut, &s.HeureFin, &s.CapaciteMax, &s.NbInscrits,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		services = append(services, s)
	}

	json.NewEncoder(w).Encode(services)
}

func DemanderAdhesionCommercant(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var demande struct {
		Nom             string `json:"nom"`
		Prenom          string `json:"prenom"`
		Email           string `json:"email"`
		MotDePasse      string `json:"mot_de_passe"`
		Telephone       string `json:"telephone"`
		Adresse         string `json:"adresse"`
		Ville           string `json:"ville"`
		CodePostal      string `json:"code_postal"`
		RaisonSociale   string `json:"raison_sociale"`
		Siret           string `json:"siret"`
		SecteurActivite string `json:"secteur_activite"`
	}

	if err := json.NewDecoder(r.Body).Decode(&demande); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	if demande.Nom == "" || demande.Prenom == "" || demande.Email == "" ||
		demande.MotDePasse == "" || demande.RaisonSociale == "" || demande.Siret == "" {
		http.Error(w, "les informations du commerçant, l'email et le mot de passe sont obligatoires", http.StatusBadRequest)
		return
	}

	if !regexp.MustCompile(`^[0-9]{14}$`).MatchString(demande.Siret) {
		http.Error(w, "le SIRET doit contenir exactement 14 chiffres", http.StatusBadRequest)
		return
	}

	if len(demande.MotDePasse) < 8 {
		http.Error(w, "le mot de passe doit contenir au moins 8 caractères", http.StatusBadRequest)
		return
	}

	hash, err := bcrypt.GenerateFromPassword([]byte(demande.MotDePasse), bcrypt.DefaultCost)
	if err != nil {
		http.Error(w, "erreur lors du chiffrement du mot de passe", http.StatusInternalServerError)
		return
	}

	var idExistant int
	var roleExistant string
	err = config.DB.QueryRow(`SELECT id_utilisateur, role FROM utilisateur WHERE email = ?`, demande.Email).Scan(&idExistant, &roleExistant)

	if err == nil {
		if roleExistant != "VISITEUR" {
			http.Error(w, "un compte existe déjà avec cet email", http.StatusConflict)
			return
		}

		_, err = config.DB.Exec(`UPDATE utilisateur SET nom = ?, prenom = ?, mot_de_passe = ?, telephone = ?, adresse = ?, ville = ?, code_postal = ?, role = 'COMMERCANT', actif = FALSE, statut_compte = 'EN_ATTENTE', raison_sociale = ?, siret = ?, secteur_activite = ? WHERE id_utilisateur = ?`, demande.Nom, demande.Prenom, string(hash), demande.Telephone,
			demande.Adresse, demande.Ville, demande.CodePostal,
			demande.RaisonSociale, demande.Siret, demande.SecteurActivite,
			idExistant)
		if err != nil {
			http.Error(w, "impossible d'enregistrer la demande d'adhésion", http.StatusInternalServerError)
			return
		}
	} else if err == sql.ErrNoRows {
		_, err = config.DB.Exec(`INSERT INTO utilisateur ( nom, prenom, email, mot_de_passe, telephone, adresse, ville, code_postal, role, actif, statut_compte, raison_sociale, siret, secteur_activite ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'COMMERCANT', FALSE, 'EN_ATTENTE', ?, ?, ?)`, demande.Nom, demande.Prenom, demande.Email, string(hash), demande.Telephone,
			demande.Adresse, demande.Ville, demande.CodePostal,
			demande.RaisonSociale, demande.Siret, demande.SecteurActivite)
		if err != nil {
			http.Error(w, "impossible d'enregistrer la demande d'adhésion", http.StatusInternalServerError)
			return
		}
	} else {
		http.Error(w, "erreur lors de la vérification du compte", http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Demande d'adhésion enregistrée. Un administrateur doit maintenant valider votre compte et votre adhésion.",
	})
}

func DemanderCollectePublique(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var demande struct {
		Nom             string  `json:"nom"`
		Prenom          string  `json:"prenom"`
		Email           string  `json:"email"`
		Adresse         string  `json:"adresse"`
		Ville           string  `json:"ville"`
		CodePostal      string  `json:"code_postal"`
		DateCollecte    string  `json:"date_collecte"`
		HeureDebut      string  `json:"heure_debut"`
		HeureFin        string  `json:"heure_fin"`
		Commentaire     string  `json:"commentaire"`
		RaisonSociale   *string `json:"raison_sociale"`
		Siret           *string `json:"siret"`
		SecteurActivite *string `json:"secteur_activite"`
	}

	if err := json.NewDecoder(r.Body).Decode(&demande); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	if demande.Nom == "" || demande.Prenom == "" || demande.Email == "" ||
		demande.Adresse == "" || demande.Ville == "" || demande.CodePostal == "" ||
		demande.DateCollecte == "" || demande.HeureDebut == "" || demande.HeureFin == "" {
		http.Error(w, "tous les champs (sauf commentaire) sont obligatoires", http.StatusBadRequest)
		return
	}

	idUtilisateur, err := trouverOuCreerVisiteur(
		demande.Nom, demande.Prenom, demande.Email,
		demande.RaisonSociale, demande.Siret, demande.SecteurActivite,
	)
	if err != nil {
		http.Error(w, "impossible d'identifier le demandeur", http.StatusInternalServerError)
		return
	}

	_, err = config.DB.Exec(`INSERT INTO collecte ( id_utilisateur, date_collecte, heure_debut, heure_fin, adresse, ville, code_postal, commentaire, statut ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'EN_ATTENTE')`, idUtilisateur, demande.DateCollecte, demande.HeureDebut, demande.HeureFin,
		demande.Adresse, demande.Ville, demande.CodePostal, demande.Commentaire)
	if err != nil {
		http.Error(w, "impossible d'enregistrer la demande", http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Demande de collecte enregistrée avec succès",
	})
}

func GetCompetencesPubliques(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodGet {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	rows, err := config.DB.Query(`SELECT id_competence, nom FROM competence ORDER BY nom`)
	if err != nil {
		http.Error(w, "impossible de récupérer les compétences", http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	type CompetencePublique struct {
		ID  int    `json:"id_competence"`
		Nom string `json:"nom"`
	}

	competences := []CompetencePublique{}

	for rows.Next() {
		var competence CompetencePublique

		if err := rows.Scan(&competence.ID, &competence.Nom); err != nil {
			http.Error(w, "impossible de lire les compétences", http.StatusInternalServerError)
			return
		}

		competences = append(competences, competence)
	}

	if err := rows.Err(); err != nil {
		http.Error(w, "impossible de lire les compétences", http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(competences)
}

func CandidaterBenevole(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var candidature struct {
		Nom         string `json:"nom"`
		Prenom      string `json:"prenom"`
		Email       string `json:"email"`
		MotDePasse  string `json:"mot_de_passe"`
		Telephone   string `json:"telephone"`
		Ville       string `json:"ville"`
		Competences []int  `json:"competences"`
	}

	if err := json.NewDecoder(r.Body).Decode(&candidature); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	if candidature.Nom == "" || candidature.Prenom == "" ||
		candidature.Email == "" || candidature.MotDePasse == "" {
		http.Error(w, "nom, prenom, email et mot de passe sont obligatoires", http.StatusBadRequest)
		return
	}

	if len(candidature.Competences) == 0 {
		http.Error(w, "au moins une compétence doit être sélectionnée", http.StatusBadRequest)
		return
	}

	var idExistant int
	err := config.DB.QueryRow(`SELECT id_utilisateur FROM utilisateur WHERE email = ?`, candidature.Email).Scan(&idExistant)

	if err == nil {
		http.Error(w, "un compte existe déjà avec cet email", http.StatusConflict)
		return
	}

	if err != sql.ErrNoRows {
		http.Error(w, "erreur lors de la vérification de l'email", http.StatusInternalServerError)
		return
	}

	hash, err := bcrypt.GenerateFromPassword(
		[]byte(candidature.MotDePasse),
		bcrypt.DefaultCost,
	)
	if err != nil {
		http.Error(w, "erreur lors du chiffrement du mot de passe", http.StatusInternalServerError)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, "impossible de démarrer l'enregistrement", http.StatusInternalServerError)
		return
	}

	resultat, err := tx.Exec(`INSERT INTO utilisateur ( nom, prenom, email, mot_de_passe, telephone, ville, role, actif ) VALUES (?, ?, ?, ?, ?, ?, 'BENEVOLE', FALSE)`,
		candidature.Nom,
		candidature.Prenom,
		candidature.Email,
		string(hash),
		candidature.Telephone,
		candidature.Ville,
	)
	if err != nil {
		tx.Rollback()
		http.Error(w, "impossible d'enregistrer la candidature", http.StatusInternalServerError)
		return
	}

	idUtilisateur, err := resultat.LastInsertId()
	if err != nil {
		tx.Rollback()
		http.Error(w, "impossible de récupérer la candidature créée", http.StatusInternalServerError)
		return
	}

	competencesDejaTraitees := make(map[int]bool)

	for _, idCompetence := range candidature.Competences {

		if idCompetence <= 0 || competencesDejaTraitees[idCompetence] {
			continue
		}

		var existe int
		err := tx.QueryRow(`SELECT COUNT(*) FROM competence WHERE id_competence = ?`, idCompetence).Scan(&existe)

		if err != nil {
			tx.Rollback()
			http.Error(w, "impossible de vérifier les compétences", http.StatusInternalServerError)
			return
		}

		if existe == 0 {
			tx.Rollback()
			http.Error(w, "compétence invalide", http.StatusBadRequest)
			return
		}

		_, err = tx.Exec(`INSERT INTO benevole_competence ( id_utilisateur, id_competence ) VALUES (?, ?)`, idUtilisateur, idCompetence)

		if err != nil {
			tx.Rollback()
			http.Error(w, "impossible d'enregistrer les compétences", http.StatusInternalServerError)
			return
		}

		competencesDejaTraitees[idCompetence] = true
	}

	if len(competencesDejaTraitees) == 0 {
		tx.Rollback()
		http.Error(w, "au moins une compétence valide doit être sélectionnée", http.StatusBadRequest)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, "impossible de valider la candidature", http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Candidature enregistrée avec vos compétences. Un administrateur va maintenant l'examiner.",
	})
}
