package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

type CollecteVue struct {
	IDCollecte    int     `json:"id_collecte"`
	IDUtilisateur int     `json:"id_utilisateur"`
	IDVehicule    *int    `json:"id_vehicule"`
	DateCollecte  string  `json:"date_collecte"`
	HeureDebut    string  `json:"heure_debut"`
	HeureFin      string  `json:"heure_fin"`
	Adresse       string  `json:"adresse"`
	Ville         string  `json:"ville"`
	CodePostal    string  `json:"code_postal"`
	Commentaire   *string `json:"commentaire"`
	Statut        string  `json:"statut"`

	DemandeurNom           string  `json:"demandeur_nom"`
	DemandeurPrenom        string  `json:"demandeur_prenom"`
	DemandeurEmail         string  `json:"demandeur_email"`
	DemandeurTelephone     *string `json:"demandeur_telephone"`
	DemandeurRole          string  `json:"demandeur_role"`
	DemandeurRaisonSociale *string `json:"demandeur_raison_sociale"`

	VehiculeImmatriculation *string `json:"vehicule_immatriculation"`
	VehiculeMarque          *string `json:"vehicule_marque"`
	VehiculeModele          *string `json:"vehicule_modele"`
}

func scannerCollecteVue(scanner interface {
	Scan(dest ...interface{}) error
}, c *CollecteVue) error {
	return scanner.Scan(
		&c.IDCollecte,
		&c.IDUtilisateur,
		&c.IDVehicule,
		&c.DateCollecte,
		&c.HeureDebut,
		&c.HeureFin,
		&c.Adresse,
		&c.Ville,
		&c.CodePostal,
		&c.Commentaire,
		&c.Statut,
		&c.DemandeurNom,
		&c.DemandeurPrenom,
		&c.DemandeurEmail,
		&c.DemandeurTelephone,
		&c.DemandeurRole,
		&c.DemandeurRaisonSociale,
		&c.VehiculeImmatriculation,
		&c.VehiculeMarque,
		&c.VehiculeModele,
	)
}

func GetCollecte(w http.ResponseWriter, r *http.Request) {
	id := r.URL.Query().Get("id")

	var c CollecteVue

	err := scannerCollecteVue(config.DB.QueryRow(`
		SELECT
			c.id_collecte,
			c.id_utilisateur,
			c.id_vehicule,
			DATE_FORMAT(c.date_collecte, '%Y-%m-%d'),
			TIME_FORMAT(c.heure_debut, '%H:%i'),
			TIME_FORMAT(c.heure_fin, '%H:%i'),
			c.adresse,
			c.ville,
			c.code_postal,
			c.commentaire,
			c.statut,
			u.nom,
			u.prenom,
			u.email,
			u.telephone,
			u.role,
			u.raison_sociale,
			v.immatriculation,
			v.marque,
			v.modele
		FROM collecte c
		JOIN utilisateur u
			ON u.id_utilisateur = c.id_utilisateur
		LEFT JOIN vehicule v
			ON v.id_vehicule = c.id_vehicule
		WHERE c.id_collecte = ?
	`, id), &c)

	if err != nil {
		http.Error(w, "collecte introuvable", http.StatusNotFound)
		return
	}

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(c)
}

func CreateCollecte(w http.ResponseWriter, r *http.Request) {

	var c models.Collecte

	err := json.NewDecoder(r.Body).Decode(&c)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	result, err := config.DB.Exec(`
        INSERT INTO collecte (
            id_utilisateur,
            id_vehicule,
            date_collecte,
            heure_debut,
            heure_fin,
            adresse,
            ville,
            code_postal,
            commentaire,
            statut
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `,
		c.IDUtilisateur,
		c.IDVehicule,
		c.DateCollecte,
		c.HeureDebut,
		c.HeureFin,
		c.Adresse,
		c.Ville,
		c.CodePostal,
		c.Commentaire,
		c.Statut,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	c.IDCollecte = int(id)

	w.Header().Set("Content-Type", "application/json")
	json.NewEncoder(w).Encode(c)
}

func transitionCollecteAutorisee(statutActuel, nouveauStatut string) bool {
	if statutActuel == nouveauStatut {
		return true
	}

	switch statutActuel {
	case "EN_ATTENTE":
		return nouveauStatut == "VALIDEE" || nouveauStatut == "ANNULEE"
	case "VALIDEE":
		return nouveauStatut == "PLANIFIEE" || nouveauStatut == "ANNULEE"
	case "PLANIFIEE":
		return nouveauStatut == "EN_COURS" || nouveauStatut == "ANNULEE"
	case "EN_COURS":
		return nouveauStatut == "TERMINEE" || nouveauStatut == "ANNULEE"
	case "TERMINEE", "ANNULEE":
		return false
	default:
		return false
	}
}

func statutCollecteArchive(statut string) bool {
	return statut == "TERMINEE" || statut == "ANNULEE"
}

func UpdateCollecte(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var c models.Collecte

	if err := json.NewDecoder(r.Body).Decode(&c); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	if c.IDCollecte <= 0 {
		http.Error(w, "collecte invalide", http.StatusBadRequest)
		return
	}

	var statutActuel string
	err := config.DB.QueryRow(`
		SELECT statut
		FROM collecte
		WHERE id_collecte = ?
	`, c.IDCollecte).Scan(&statutActuel)

	if err != nil {
		http.Error(w, "collecte introuvable", http.StatusNotFound)
		return
	}

	if statutCollecteArchive(statutActuel) {
		http.Error(
			w,
			"cette collecte est archivée et ne peut plus être modifiée",
			http.StatusConflict,
		)
		return
	}

	if !transitionCollecteAutorisee(statutActuel, c.Statut) {
		http.Error(
			w,
			"transition de statut non autorisée : "+statutActuel+" -> "+c.Statut,
			http.StatusBadRequest,
		)
		return
	}

	if c.Statut == "PLANIFIEE" && (c.IDVehicule == nil || *c.IDVehicule <= 0) {
		http.Error(
			w,
			"un véhicule doit être attribué avant de planifier la collecte",
			http.StatusBadRequest,
		)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE collecte
		SET
			id_vehicule=?,
			date_collecte=?,
			heure_debut=?,
			heure_fin=?,
			adresse=?,
			ville=?,
			code_postal=?,
			commentaire=?,
			statut=?
		WHERE id_collecte=?
	`,
		c.IDVehicule,
		c.DateCollecte,
		c.HeureDebut,
		c.HeureFin,
		c.Adresse,
		c.Ville,
		c.CodePostal,
		c.Commentaire,
		c.Statut,
		c.IDCollecte,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Collecte modifiée",
	})
}

func DeleteCollecte(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	var body struct {
		IDCollecte int `json:"id_collecte"`
	}

	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	var statut string
	err := config.DB.QueryRow(`
		SELECT statut
		FROM collecte
		WHERE id_collecte = ?
	`, body.IDCollecte).Scan(&statut)

	if err != nil {
		http.Error(w, "collecte introuvable", http.StatusNotFound)
		return
	}

	if statutCollecteArchive(statut) {
		http.Error(
			w,
			"une collecte archivée ne peut pas être supprimée",
			http.StatusConflict,
		)
		return
	}

	_, err = config.DB.Exec(
		"DELETE FROM collecte WHERE id_collecte=?",
		body.IDCollecte,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Collecte supprimée",
	})
}

func GetCollectes(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT
			c.id_collecte,
			c.id_utilisateur,
			c.id_vehicule,
			DATE_FORMAT(c.date_collecte, '%Y-%m-%d'),
			TIME_FORMAT(c.heure_debut, '%H:%i'),
			TIME_FORMAT(c.heure_fin, '%H:%i'),
			c.adresse,
			c.ville,
			c.code_postal,
			c.commentaire,
			c.statut,
			u.nom,
			u.prenom,
			u.email,
			u.telephone,
			u.role,
			u.raison_sociale,
			v.immatriculation,
			v.marque,
			v.modele
		FROM collecte c
		JOIN utilisateur u
			ON u.id_utilisateur = c.id_utilisateur
		LEFT JOIN vehicule v
			ON v.id_vehicule = c.id_vehicule
		ORDER BY c.date_collecte DESC, c.id_collecte DESC
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	collectes := []CollecteVue{}

	for rows.Next() {
		var c CollecteVue

		if err := scannerCollecteVue(rows, &c); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		collectes = append(collectes, c)
	}

	if err := rows.Err(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(collectes)
}
