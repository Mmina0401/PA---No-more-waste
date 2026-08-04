package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

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

	var utilisateurs []models.Utilisateur

	for rows.Next() {
		var u models.Utilisateur

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

	var u models.Utilisateur
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

	var u models.Utilisateur
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

	result, err := config.DB.Exec(`
		INSERT INTO utilisateur(
			nom, prenom, email, mot_de_passe, telephone, adresse, ville, code_postal,
			role, raison_sociale, siret, secteur_activite
		)
		VALUES(?,?,?,?,?,?,?,?,?,?,?,?)
	`,
		u.Nom, u.Prenom, u.Email, u.MotDePasse, u.Telephone, u.Adresse, u.Ville, u.CodePostal,
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

	var u models.Utilisateur
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
