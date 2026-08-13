package handlers

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"
	"strconv"
	"strings"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/models"
)

func repondreErreurDetailCollecte(w http.ResponseWriter, statut int, message string) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(statut)
	_ = json.NewEncoder(w).Encode(map[string]string{
		"error":   "DETAIL_COLLECTE",
		"message": message,
	})
}

func GetDetailCollectes(w http.ResponseWriter, r *http.Request) {
	details := []models.DetailCollecte{}

	requete := `
		SELECT
			dc.id_collecte,
			dc.id_produit,
			p.nom,
			dc.quantite,
			DATE_FORMAT(dc.date_dlc, '%Y-%m-%d'),
			dc.etat,
			COALESCE(dc.observation, '')
		FROM detail_collecte dc
		JOIN produit p ON p.id_produit = dc.id_produit
	`

	arguments := []interface{}{}

	if idTexte := strings.TrimSpace(r.URL.Query().Get("id_collecte")); idTexte != "" {
		idCollecte, err := strconv.Atoi(idTexte)
		if err != nil || idCollecte <= 0 {
			repondreErreurDetailCollecte(w, http.StatusBadRequest, "Identifiant de collecte invalide.")
			return
		}

		requete += " WHERE dc.id_collecte = ?"
		arguments = append(arguments, idCollecte)
	}

	requete += " ORDER BY dc.id_collecte DESC, p.nom ASC, dc.date_dlc ASC"

	rows, err := config.DB.Query(requete, arguments...)
	if err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de charger les produits des collectes.")
		return
	}
	defer rows.Close()

	for rows.Next() {
		var d models.DetailCollecte

		if err := rows.Scan(
			&d.IDCollecte,
			&d.IDProduit,
			&d.NomProduit,
			&d.Quantite,
			&d.DateDLC,
			&d.Etat,
			&d.Observation,
		); err != nil {
			repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de lire les produits de la collecte.")
			return
		}

		details = append(details, d)
	}

	if err := rows.Err(); err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de terminer la lecture des produits de la collecte.")
		return
	}

	w.Header().Set("Content-Type", "application/json")
	_ = json.NewEncoder(w).Encode(details)
}

func CreateDetailCollecte(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPost {
		repondreErreurDetailCollecte(w, http.StatusMethodNotAllowed, "Méthode non autorisée.")
		return
	}

	var d models.DetailCollecte
	if err := json.NewDecoder(r.Body).Decode(&d); err != nil {
		repondreErreurDetailCollecte(w, http.StatusBadRequest, "Données invalides.")
		return
	}

	d.Etat = strings.ToUpper(strings.TrimSpace(d.Etat))
	d.Observation = strings.TrimSpace(d.Observation)
	d.Emplacement = strings.TrimSpace(d.Emplacement)
	d.DateDLC = strings.TrimSpace(d.DateDLC)

	if d.IDCollecte <= 0 || d.IDProduit <= 0 {
		repondreErreurDetailCollecte(w, http.StatusBadRequest, "Collecte ou produit invalide.")
		return
	}

	if d.Quantite <= 0 {
		repondreErreurDetailCollecte(w, http.StatusBadRequest, "La quantité doit être supérieure à 0.")
		return
	}

	if d.DateDLC == "" {
		repondreErreurDetailCollecte(w, http.StatusBadRequest, "La DLC est obligatoire.")
		return
	}

	etatsAutorises := map[string]bool{
		"EXCELLENT": true,
		"BON":       true,
		"MOYEN":     true,
		"ABIME":     true,
	}
	if !etatsAutorises[d.Etat] {
		repondreErreurDetailCollecte(w, http.StatusBadRequest, "État du produit invalide.")
		return
	}

	informations := auth.RecupererInformationsConnecte(r)
	if informations == nil || informations.IDUtilisateur <= 0 {
		repondreErreurDetailCollecte(w, http.StatusUnauthorized, "Connexion requise.")
		return
	}

	tx, err := config.DB.BeginTx(r.Context(), nil)
	if err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de démarrer l'enregistrement.")
		return
	}
	defer tx.Rollback()

	var statutCollecte string
	if err := tx.QueryRowContext(r.Context(), `
		SELECT statut
		FROM collecte
		WHERE id_collecte = ?
		FOR UPDATE
	`, d.IDCollecte).Scan(&statutCollecte); err != nil {
		if err == sql.ErrNoRows {
			repondreErreurDetailCollecte(w, http.StatusNotFound, "Collecte introuvable.")
			return
		}
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de vérifier la collecte.")
		return
	}

	if statutCollecte != "TERMINEE" {
		repondreErreurDetailCollecte(w, http.StatusConflict, "La collecte doit être terminée avant d'enregistrer les produits récupérés.")
		return
	}

	var nomProduit string
	if err := tx.QueryRowContext(r.Context(), `
		SELECT nom
		FROM produit
		WHERE id_produit = ? AND actif = TRUE
	`, d.IDProduit).Scan(&nomProduit); err != nil {
		if err == sql.ErrNoRows {
			repondreErreurDetailCollecte(w, http.StatusNotFound, "Produit introuvable ou inactif.")
			return
		}
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de vérifier le produit.")
		return
	}

	var detailExistant int
	if err := tx.QueryRowContext(r.Context(), `
		SELECT COUNT(*)
		FROM detail_collecte
		WHERE id_collecte = ? AND id_produit = ? AND date_dlc = ?
	`, d.IDCollecte, d.IDProduit, d.DateDLC).Scan(&detailExistant); err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de vérifier le détail de collecte.")
		return
	}

	if detailExistant > 0 {
		repondreErreurDetailCollecte(w, http.StatusConflict, "Ce produit avec cette DLC est déjà enregistré pour cette collecte.")
		return
	}

	if _, err := tx.ExecContext(r.Context(), `
		INSERT INTO detail_collecte (
			id_collecte,
			id_produit,
			quantite,
			date_dlc,
			etat,
			observation
		)
		VALUES (?, ?, ?, ?, ?, ?)
	`,
		d.IDCollecte,
		d.IDProduit,
		d.Quantite,
		d.DateDLC,
		d.Etat,
		d.Observation,
	); err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible d'enregistrer le produit dans la collecte.")
		return
	}

	var idStock int
	var emplacementStock string

	err = tx.QueryRowContext(r.Context(), `
		SELECT id_stock, emplacement
		FROM stock
		WHERE id_produit = ?
		FOR UPDATE
	`, d.IDProduit).Scan(&idStock, &emplacementStock)

	switch {
	case err == nil:
		if _, err := tx.ExecContext(r.Context(), `
			UPDATE stock
			SET quantite = quantite + ?
			WHERE id_stock = ?
		`, d.Quantite, idStock); err != nil {
			repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de mettre à jour le stock.")
			return
		}

	case err == sql.ErrNoRows:
		if d.Emplacement == "" {
			repondreErreurDetailCollecte(w, http.StatusBadRequest, "L'emplacement est obligatoire pour un produit qui n'est pas encore en stock.")
			return
		}

		resultat, err := tx.ExecContext(r.Context(), `
			INSERT INTO stock (id_produit, quantite, emplacement)
			VALUES (?, ?, ?)
		`, d.IDProduit, d.Quantite, d.Emplacement)
		if err != nil {
			repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de créer le stock du produit.")
			return
		}

		idNouveauStock, err := resultat.LastInsertId()
		if err != nil {
			repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de récupérer le nouveau stock.")
			return
		}

		idStock = int(idNouveauStock)
		emplacementStock = d.Emplacement

	default:
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de rechercher le stock du produit.")
		return
	}

	motif := fmt.Sprintf("Entrée automatique - collecte #%d", d.IDCollecte)
	if _, err := tx.ExecContext(r.Context(), `
		INSERT INTO mouvement_stock (
			id_stock,
			id_utilisateur,
			type,
			quantite,
			motif
		)
		VALUES (?, ?, 'ENTREE', ?, ?)
	`, idStock, informations.IDUtilisateur, d.Quantite, motif); err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de créer le mouvement de stock.")
		return
	}

	var nouvelleQuantite float64
	if err := tx.QueryRowContext(r.Context(), `
		SELECT quantite
		FROM stock
		WHERE id_stock = ?
	`, idStock).Scan(&nouvelleQuantite); err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de vérifier la nouvelle quantité en stock.")
		return
	}

	if err := tx.Commit(); err != nil {
		repondreErreurDetailCollecte(w, http.StatusInternalServerError, "Impossible de valider l'entrée en stock.")
		return
	}

	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(http.StatusCreated)
	_ = json.NewEncoder(w).Encode(map[string]interface{}{
		"message":           "Produit ajouté à la collecte et au stock.",
		"id_stock":          idStock,
		"nom_produit":       nomProduit,
		"quantite_ajoutee":  d.Quantite,
		"nouvelle_quantite": nouvelleQuantite,
		"emplacement":       emplacementStock,
	})
}

func UpdateDetailCollecte(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodPut {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var d models.DetailCollecte

	err := json.NewDecoder(r.Body).Decode(&d)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		UPDATE detail_collecte
		SET
			quantite = ?,
			date_dlc = ?,
			etat = ?,
			observation = ?
		WHERE
			id_collecte = ?
			AND id_produit = ?
	`,
		d.Quantite,
		d.DateDLC,
		d.Etat,
		d.Observation,
		d.IDCollecte,
		d.IDProduit,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Détail de collecte modifié",
	})
}

func DeleteDetailCollecte(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodDelete {
		http.Error(w, "Méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var d models.DetailCollecte

	err := json.NewDecoder(r.Body).Decode(&d)
	if err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	_, err = config.DB.Exec(`
		DELETE FROM detail_collecte
		WHERE
			id_collecte = ?
			AND id_produit = ?
	`,
		d.IDCollecte,
		d.IDProduit,
	)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{
		"message": "Détail de collecte supprimé",
	})
}
