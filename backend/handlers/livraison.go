package handlers

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

type Livraison struct {
	IDLivraison   int     `json:"id_livraison"`
	IDAssociation int     `json:"id_association"`
	IDVehicule    int     `json:"id_vehicule"`
	DateLivraison string  `json:"date_livraison"`
	HeureDepart   *string `json:"heure_depart"`
	HeureRetour   *string `json:"heure_retour"`
	Statut        string  `json:"statut"`
	Commentaire   *string `json:"commentaire"`

	NomAssociation  string `json:"nom_association,omitempty"`
	Immatriculation string `json:"immatriculation,omitempty"`
}

type LigneLivraison struct {
	IDLivraison int     `json:"id_livraison"`
	IDProduit   int     `json:"id_produit"`
	Quantite    float64 `json:"quantite"`

	NomProduit string `json:"nom_produit,omitempty"`
}

type Association struct {
	IDAssociation int     `json:"id_association"`
	Nom           string  `json:"nom"`
	Ville         *string `json:"ville"`
}

type Vehicule struct {
	IDVehicule      int    `json:"id_vehicule"`
	Immatriculation string `json:"immatriculation"`
	Marque          string `json:"marque"`
	Modele          string `json:"modele"`
	Etat            string `json:"etat"`
}

func GetLivraisons(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`SELECT l.id_livraison, l.id_association, l.id_vehicule, l.date_livraison, l.heure_depart, l.heure_retour, l.statut, l.commentaire, a.nom, v.immatriculation FROM livraison l JOIN association_beneficiaire a ON a.id_association = l.id_association JOIN vehicule v ON v.id_vehicule = l.id_vehicule ORDER BY l.date_livraison DESC`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var livraisons []Livraison
	for rows.Next() {
		var t Livraison
		if err := rows.Scan(
			&t.IDLivraison, &t.IDAssociation, &t.IDVehicule, &t.DateLivraison,
			&t.HeureDepart, &t.HeureRetour, &t.Statut, &t.Commentaire,
			&t.NomAssociation, &t.Immatriculation,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		livraisons = append(livraisons, t)
	}
	json.NewEncoder(w).Encode(livraisons)
}

func GetLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	id := r.URL.Query().Get("id")
	if id == "" {
		http.Error(w, "paramètre id manquant", http.StatusBadRequest)
		return
	}

	var t Livraison
	err := config.DB.QueryRow(`SELECT id_livraison, id_association, id_vehicule, date_livraison, heure_depart, heure_retour, statut, commentaire FROM livraison WHERE id_livraison = ?`, id).Scan(
		&t.IDLivraison, &t.IDAssociation, &t.IDVehicule, &t.DateLivraison,
		&t.HeureDepart, &t.HeureRetour, &t.Statut, &t.Commentaire,
	)
	if err != nil {
		http.Error(w, "tournée introuvable", http.StatusNotFound)
		return
	}
	json.NewEncoder(w).Encode(t)
}

func CreateLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var t Livraison
	if err := json.NewDecoder(r.Body).Decode(&t); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if t.IDAssociation == 0 || t.IDVehicule == 0 || t.DateLivraison == "" {
		http.Error(w, "association, véhicule et date sont obligatoires", http.StatusBadRequest)
		return
	}
	if t.Statut == "" {
		t.Statut = "PLANIFIEE"
	}

	result, err := config.DB.Exec(`INSERT INTO livraison (id_association, id_vehicule, date_livraison, heure_depart, heure_retour, statut, commentaire) VALUES (?,?,?,?,?,?,?)`, t.IDAssociation, t.IDVehicule, t.DateLivraison, t.HeureDepart, t.HeureRetour, t.Statut, t.Commentaire)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	id, _ := result.LastInsertId()
	t.IDLivraison = int(id)
	json.NewEncoder(w).Encode(t)
}

func UpdateLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var t Livraison
	if err := json.NewDecoder(r.Body).Decode(&t); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if t.IDLivraison == 0 {
		http.Error(w, "id_livraison manquant", http.StatusBadRequest)
		return
	}

	tx, err := config.DB.Begin()
	if err != nil {
		http.Error(w, "impossible de démarrer la transaction", http.StatusInternalServerError)
		return
	}
	defer tx.Rollback()

	var statutActuel string
	err = tx.QueryRow(`SELECT statut FROM livraison WHERE id_livraison = ? FOR UPDATE`, t.IDLivraison).Scan(&statutActuel)
	if err == sql.ErrNoRows {
		http.Error(w, "tournée introuvable", http.StatusNotFound)
		return
	}
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if statutActuel == "TERMINEE" && t.Statut != "TERMINEE" {
		http.Error(w, "une tournée terminée ne peut pas être rouverte", http.StatusConflict)
		return
	}

	stockDebite := false

	if statutActuel != "TERMINEE" && t.Statut == "TERMINEE" {
		type ligneAControler struct {
			IDProduit  int
			Quantite   float64
			NomProduit string
		}

		rows, err := tx.Query(`SELECT dl.id_produit, dl.quantite, p.nom FROM detail_livraison dl JOIN produit p ON p.id_produit = dl.id_produit WHERE dl.id_livraison = ?`, t.IDLivraison)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		var lignes []ligneAControler
		for rows.Next() {
			var ligne ligneAControler
			if err := rows.Scan(&ligne.IDProduit, &ligne.Quantite, &ligne.NomProduit); err != nil {
				rows.Close()
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}
			lignes = append(lignes, ligne)
		}
		rows.Close()

		if len(lignes) == 0 {
			http.Error(w, "ajoutez au moins un produit avant de terminer la tournée", http.StatusConflict)
			return
		}

		informations := auth.RecupererInformationsConnecte(r)
		if informations == nil || informations.IDUtilisateur == 0 {
			http.Error(w, "utilisateur connecté introuvable", http.StatusUnauthorized)
			return
		}

		for _, ligne := range lignes {
			var idStock int
			var quantiteDisponible float64

			err = tx.QueryRow(`SELECT id_stock, quantite FROM stock WHERE id_produit = ? FOR UPDATE`, ligne.IDProduit).Scan(&idStock, &quantiteDisponible)

			if err == sql.ErrNoRows {
				http.Error(
					w,
					fmt.Sprintf("stock indisponible pour le produit %s", ligne.NomProduit),
					http.StatusConflict,
				)
				return
			}
			if err != nil {
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}

			if quantiteDisponible < ligne.Quantite {
				http.Error(
					w,
					fmt.Sprintf(
						"stock insuffisant pour %s : %.2f disponible(s), %.2f demandé(s)",
						ligne.NomProduit,
						quantiteDisponible,
						ligne.Quantite,
					),
					http.StatusConflict,
				)
				return
			}

			_, err = tx.Exec(`UPDATE stock SET quantite = quantite - ? WHERE id_stock = ?`, ligne.Quantite, idStock)
			if err != nil {
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}

			_, err = tx.Exec(`INSERT INTO mouvement_stock (id_stock, id_utilisateur, type, quantite, motif) VALUES (?, ?, 'SORTIE', ?, ?)`,
				idStock,
				informations.IDUtilisateur,
				ligne.Quantite,
				fmt.Sprintf("Sortie automatique - tournée #%d", t.IDLivraison),
			)
			if err != nil {
				http.Error(w, err.Error(), http.StatusInternalServerError)
				return
			}
		}

		stockDebite = true
	}

	_, err = tx.Exec(`UPDATE livraison SET id_association=?, id_vehicule=?, date_livraison=?, heure_depart=?, heure_retour=?, statut=?, commentaire=? WHERE id_livraison=?`, t.IDAssociation, t.IDVehicule, t.DateLivraison, t.HeureDepart, t.HeureRetour, t.Statut, t.Commentaire, t.IDLivraison)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if err := tx.Commit(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	message := "Tournée modifiée"
	if stockDebite {
		message = "Tournée terminée : les produits ont été retirés du stock"
	}
	json.NewEncoder(w).Encode(map[string]string{"message": message})
}

func DeleteLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var body struct {
		IDLivraison int `json:"id_livraison"`
	}
	if err := json.NewDecoder(r.Body).Decode(&body); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	var statut string
	err := config.DB.QueryRow(`SELECT statut FROM livraison WHERE id_livraison = ?`, body.IDLivraison).Scan(&statut)
	if err == sql.ErrNoRows {
		http.Error(w, "tournée introuvable", http.StatusNotFound)
		return
	}
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	if statut == "TERMINEE" {
		http.Error(w, "une tournée terminée ne peut pas être supprimée", http.StatusConflict)
		return
	}

	_, err = config.DB.Exec(`DELETE FROM livraison WHERE id_livraison = ?`, body.IDLivraison)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	json.NewEncoder(w).Encode(map[string]string{"message": "Tournée supprimée"})
}

func GetLignesLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	idLivraison := r.URL.Query().Get("id_livraison")
	if idLivraison == "" {
		http.Error(w, "paramètre id_livraison manquant", http.StatusBadRequest)
		return
	}

	rows, err := config.DB.Query(`SELECT dl.id_livraison, dl.id_produit, dl.quantite, p.nom FROM detail_livraison dl JOIN produit p ON p.id_produit = dl.id_produit WHERE dl.id_livraison = ?`, idLivraison)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var lignes []LigneLivraison
	for rows.Next() {
		var l LigneLivraison
		if err := rows.Scan(&l.IDLivraison, &l.IDProduit, &l.Quantite, &l.NomProduit); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		lignes = append(lignes, l)
	}
	json.NewEncoder(w).Encode(lignes)
}

func AjouterLigneLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var l LigneLivraison
	if err := json.NewDecoder(r.Body).Decode(&l); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}
	if l.IDLivraison == 0 || l.IDProduit == 0 || l.Quantite <= 0 {
		http.Error(w, "id_livraison, id_produit et une quantité positive sont obligatoires", http.StatusBadRequest)
		return
	}

	var statut string
	err := config.DB.QueryRow(`SELECT statut FROM livraison WHERE id_livraison = ?`, l.IDLivraison).Scan(&statut)
	if err == sql.ErrNoRows {
		http.Error(w, "tournée introuvable", http.StatusNotFound)
		return
	}
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	if statut == "TERMINEE" {
		http.Error(w, "les produits d'une tournée terminée ne peuvent plus être modifiés", http.StatusConflict)
		return
	}

	_, err = config.DB.Exec(`INSERT INTO detail_livraison (id_livraison, id_produit, quantite) VALUES (?,?,?)`, l.IDLivraison, l.IDProduit, l.Quantite)

	if err != nil {
		http.Error(w, "ce produit est déjà dans cette tournée", http.StatusConflict)
		return
	}
	json.NewEncoder(w).Encode(map[string]string{"message": "Produit ajouté à la tournée"})
}

func SupprimerLigneLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")
	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var l LigneLivraison
	if err := json.NewDecoder(r.Body).Decode(&l); err != nil {
		http.Error(w, err.Error(), http.StatusBadRequest)
		return
	}

	var statut string
	err := config.DB.QueryRow(`SELECT statut FROM livraison WHERE id_livraison = ?`, l.IDLivraison).Scan(&statut)
	if err == sql.ErrNoRows {
		http.Error(w, "tournée introuvable", http.StatusNotFound)
		return
	}
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	if statut == "TERMINEE" {
		http.Error(w, "les produits d'une tournée terminée ne peuvent plus être modifiés", http.StatusConflict)
		return
	}

	_, err = config.DB.Exec(`DELETE FROM detail_livraison WHERE id_livraison = ? AND id_produit = ?`, l.IDLivraison, l.IDProduit)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	json.NewEncoder(w).Encode(map[string]string{"message": "Produit retiré de la tournée"})
}

func GetAssociations(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`SELECT id_association, nom, ville FROM association_beneficiaire ORDER BY nom`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var associations []Association
	for rows.Next() {
		var a Association
		if err := rows.Scan(&a.IDAssociation, &a.Nom, &a.Ville); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		associations = append(associations, a)
	}
	json.NewEncoder(w).Encode(associations)
}

func GetVehicules(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`SELECT id_vehicule, immatriculation, marque, modele, etat FROM vehicule ORDER BY immatriculation`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	var vehicules []Vehicule
	for rows.Next() {
		var v Vehicule
		if err := rows.Scan(&v.IDVehicule, &v.Immatriculation, &v.Marque, &v.Modele, &v.Etat); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		vehicules = append(vehicules, v)
	}
	json.NewEncoder(w).Encode(vehicules)
}
