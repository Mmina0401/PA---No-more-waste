package handlers

import (
	"encoding/json"
	"net/http"
	"time"

	"github.com/MMina040/PA-No-More-Waste/config"
	"golang.org/x/crypto/bcrypt"
)

// Utilisateur représente n'importe quel compte (admin, responsable,
// bénévole, commerçant, visiteur).
type Utilisateur struct {
	ID           int       `json:"id_utilisateur"`
	Nom          string    `json:"nom"`
	Prenom       string    `json:"prenom"`
	Email        string    `json:"email"`
	MotDePasse   string    `json:"mot_de_passe,omitempty"`
	Telephone    *string   `json:"telephone"`
	Adresse      *string   `json:"adresse"`
	Ville        *string   `json:"ville"`
	CodePostal   *string   `json:"code_postal"`
	Role         string    `json:"role"`
	Actif        bool      `json:"actif"`
	DateCreation time.Time `json:"date_creation"`

	// Champs spécifiques aux commerçants (NULL pour les autres rôles)
	RaisonSociale   *string `json:"raison_sociale"`
	Siret           *string `json:"siret"`
	SecteurActivite *string `json:"secteur_activite"`
}

// GetUtilisateurs renvoie la liste des utilisateurs.
// Filtre optionnel : /api/utilisateurs?role=COMMERCANT
func GetUtilisateurs(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	query := `
		SELECT
			id_utilisateur, nom, prenom, email, mot_de_passe,
			telephone, adresse, ville, code_postal,
			role, actif, date_creation,
			raison_sociale, siret, secteur_activite
		FROM utilisateur
	`
	args := []interface{}{}

	if role := r.URL.Query().Get("role"); role != "" {
		query += " WHERE role = ?"
		args = append(args, role)
	}

	rows, err := config.DB.Query(query, args...)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var utilisateurs []Utilisateur

	for rows.Next() {
		var u Utilisateur

		err := rows.Scan(
			&u.ID, &u.Nom, &u.Prenom, &u.Email, &u.MotDePasse,
			&u.Telephone, &u.Adresse, &u.Ville, &u.CodePostal,
			&u.Role, &u.Actif, &u.DateCreation,
			&u.RaisonSociale, &u.Siret, &u.SecteurActivite,
		)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		u.MotDePasse = ""
		utilisateurs = append(utilisateurs, u)
	}

	json.NewEncoder(w).Encode(utilisateurs)
}

// GetUtilisateur renvoie un seul utilisateur : /api/utilisateurs/get?id=3
func GetUtilisateur(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	id := r.URL.Query().Get("id")
	if id == "" {
		http.Error(w, "paramètre id manquant", http.StatusBadRequest)
		return
	}

	var u Utilisateur
	err := config.DB.QueryRow(`
		SELECT
			id_utilisateur, nom, prenom, email, mot_de_passe,
			telephone, adresse, ville, code_postal,
			role, actif, date_creation,
			raison_sociale, siret, secteur_activite
		FROM utilisateur
		WHERE id_utilisateur = ?
	`, id).Scan(
		&u.ID, &u.Nom, &u.Prenom, &u.Email, &u.MotDePasse,
		&u.Telephone, &u.Adresse, &u.Ville, &u.CodePostal,
		&u.Role, &u.Actif, &u.DateCreation,
		&u.RaisonSociale, &u.Siret, &u.SecteurActivite,
	)
	if err != nil {
		http.Error(w, "utilisateur introuvable", http.StatusNotFound)
		return
	}

	u.MotDePasse = ""
	json.NewEncoder(w).Encode(u)
}

func CreateUtilisateur(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var u Utilisateur
	if err := json.NewDecoder(r.Body).Decode(&u); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	if u.Nom == "" || u.Prenom == "" || u.Email == "" || u.MotDePasse == "" || u.Role == "" {
		http.Error(w, "nom, prenom, email, mot_de_passe et role sont obligatoires", http.StatusBadRequest)
		return
	}
	if u.Role == "COMMERCANT" && (u.Siret == nil || *u.Siret == "") {
		http.Error(w, "le SIRET est obligatoire pour un commerçant", http.StatusBadRequest)
		return
	}

	// Chiffrement du mot de passe avant stockage — jamais en clair en base.
	hash, err := bcrypt.GenerateFromPassword([]byte(u.MotDePasse), bcrypt.DefaultCost)
	if err != nil {
		http.Error(w, "erreur lors du chiffrement du mot de passe", http.StatusInternalServerError)
		return
	}

	result, err := config.DB.Exec(`
		INSERT INTO utilisateur(
			nom, prenom, email, mot_de_passe, telephone, adresse, ville, code_postal,
			role, raison_sociale, siret, secteur_activite
		)
		VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
	`,
		u.Nom, u.Prenom, u.Email, string(hash), u.Telephone, u.Adresse, u.Ville, u.CodePostal,
		u.Role, u.RaisonSociale, u.Siret, u.SecteurActivite,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	u.ID = int(id)
	u.MotDePasse = ""

	json.NewEncoder(w).Encode(u)
}

func UpdateUtilisateur(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var u Utilisateur
	if err := json.NewDecoder(r.Body).Decode(&u); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if u.ID == 0 {
		http.Error(w, "id_utilisateur manquant", http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`
		UPDATE utilisateur
		SET nom=?, prenom=?, email=?, telephone=?, adresse=?, ville=?, code_postal=?,
			role=?, actif=?, raison_sociale=?, siret=?, secteur_activite=?
		WHERE id_utilisateur=?
	`,
		u.Nom, u.Prenom, u.Email, u.Telephone, u.Adresse, u.Ville, u.CodePostal,
		u.Role, u.Actif, u.RaisonSociale, u.Siret, u.SecteurActivite,
		u.ID,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Utilisateur modifié"})
}

func DeleteUtilisateur(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var body struct {
		ID int `json:"id_utilisateur"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`DELETE FROM utilisateur WHERE id_utilisateur = ?`, body.ID)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "Utilisateur supprimé"})
}
