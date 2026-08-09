package handlers

import (
	"encoding/json"
	"net/http"

	"golang.org/x/crypto/bcrypt"

	"github.com/MMina040/PA-No-More-Waste/config"
)

// ServicePublic est une version simplifiée d'un service, pensée pour être
// montrée à des visiteurs non connectés.
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

// ObtenirServicesPublics renvoie uniquement les services ouverts aux
// inscriptions. Aucune connexion nécessaire.
// GET /api/public/services
func ObtenirServicesPublics(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	lignes, err := config.DB.Query(`
		SELECT
			s.id_service, s.nom, s.description, s.lieu, s.date_service,
			s.heure_debut, s.heure_fin, s.capacite_max,
			COUNT(i.id_utilisateur) AS nb_inscrits
		FROM service s
		LEFT JOIN inscription_service i
			ON i.id_service = s.id_service AND i.statut != 'ANNULE'
		WHERE s.statut = 'OUVERT'
		GROUP BY s.id_service
		ORDER BY s.date_service ASC
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer lignes.Close()

	var services []ServicePublic
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

// InscrirePublique permet à une personne sans compte de s'inscrire à un
// service. Un compte "VISITEUR" léger est créé automatiquement pour elle
// (ou réutilisé si son email existe déjà dans la base).
// POST /api/public/inscription
// corps attendu : {"nom":"...","prenom":"...","email":"...","id_service":3}
func InscrirePublique(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var demande struct {
		Nom       string `json:"nom"`
		Prenom    string `json:"prenom"`
		Email     string `json:"email"`
		IDService int    `json:"id_service"`
	}
	if err := json.NewDecoder(r.Body).Decode(&demande); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}
	if demande.Nom == "" || demande.Prenom == "" || demande.Email == "" || demande.IDService == 0 {
		http.Error(w, "nom, prenom, email et id_service sont obligatoires", http.StatusBadRequest)
		return
	}

	// On regarde si cette personne a déjà un compte (par email).
	var idUtilisateur int
	err := config.DB.QueryRow(`SELECT id_utilisateur FROM utilisateur WHERE email = ?`, demande.Email).Scan(&idUtilisateur)

	if err != nil {
		// Personne inconnue : on lui crée un compte visiteur léger.
		motDePasseAleatoire := "visiteur-" + demande.Email
		hash, errHash := bcrypt.GenerateFromPassword([]byte(motDePasseAleatoire), bcrypt.DefaultCost)
		if errHash != nil {
			http.Error(w, "erreur lors de la création du compte", http.StatusInternalServerError)
			return
		}

		resultat, errCreation := config.DB.Exec(`
			INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role)
			VALUES (?, ?, ?, ?, 'VISITEUR')
		`, demande.Nom, demande.Prenom, demande.Email, string(hash))

		if errCreation != nil {
			http.Error(w, "impossible de créer le compte visiteur", http.StatusInternalServerError)
			return
		}

		nouvelID, _ := resultat.LastInsertId()
		idUtilisateur = int(nouvelID)
	}

	// Vérifie la capacité restante avant d'inscrire (même règle que côté back-office).
	var capaciteMax *int
	var nbInscrits int

	err = config.DB.QueryRow(`SELECT capacite_max FROM service WHERE id_service = ? AND statut = 'OUVERT'`, demande.IDService).Scan(&capaciteMax)
	if err != nil {
		http.Error(w, "service introuvable ou fermé", http.StatusNotFound)
		return
	}

	config.DB.QueryRow(`
		SELECT COUNT(*) FROM inscription_service
		WHERE id_service = ? AND statut != 'ANNULE'
	`, demande.IDService).Scan(&nbInscrits)

	if capaciteMax != nil && nbInscrits >= *capaciteMax {
		http.Error(w, "ce service est complet", http.StatusConflict)
		return
	}

	_, err = config.DB.Exec(`
		INSERT INTO inscription_service (id_service, id_utilisateur, statut)
		VALUES (?, ?, 'INSCRIT')
	`, demande.IDService, idUtilisateur)

	if err != nil {
		http.Error(w, "vous êtes déjà inscrit à ce service", http.StatusConflict)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Inscription enregistrée avec succès"})
}