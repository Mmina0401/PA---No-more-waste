package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
)

// Livraison représente une tournée de distribution vers une association bénéficiaire.
type Livraison struct {
	IDLivraison   int     `json:"id_livraison"`
	IDAssociation int     `json:"id_association"`
	IDVehicule    int     `json:"id_vehicule"`
	DateLivraison string  `json:"date_livraison"`
	HeureDepart   *string `json:"heure_depart"`
	HeureRetour   *string `json:"heure_retour"`
	Statut        string  `json:"statut"`
	Commentaire   *string `json:"commentaire"`

	// Renseignés uniquement par GetLivraisons (jointure), pour affichage.
	NomAssociation  string `json:"nom_association,omitempty"`
	Immatriculation string `json:"immatriculation,omitempty"`
}

// LigneLivraison représente un produit chargé pour une tournée donnée.
type LigneLivraison struct {
	IDLivraison int     `json:"id_livraison"`
	IDProduit   int     `json:"id_produit"`
	Quantite    float64 `json:"quantite"`

	// Renseigné uniquement par GetLignesLivraison (jointure), pour affichage.
	NomProduit string `json:"nom_produit,omitempty"`
}

// Association représente une structure bénéficiaire des tournées.
type Association struct {
	IDAssociation int     `json:"id_association"`
	Nom           string  `json:"nom"`
	Ville         *string `json:"ville"`
}

// Vehicule représente un véhicule disponible pour les tournées.
type Vehicule struct {
	IDVehicule      int    `json:"id_vehicule"`
	Immatriculation string `json:"immatriculation"`
	Marque          string `json:"marque"`
	Modele          string `json:"modele"`
	Etat            string `json:"etat"`
}

// ---------- Tournées (livraisons) ----------

func GetLivraisons(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	rows, err := config.DB.Query(`
		SELECT
			l.id_livraison, l.id_association, l.id_vehicule, l.date_livraison,
			l.heure_depart, l.heure_retour, l.statut, l.commentaire,
			a.nom, v.immatriculation
		FROM livraison l
		JOIN association_beneficiaire a ON a.id_association = l.id_association
		JOIN vehicule v ON v.id_vehicule = l.id_vehicule
		ORDER BY l.date_livraison DESC
	`)
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

// GetLivraison renvoie une seule tournée : /api/livraisons/get?id=3
func GetLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	id := r.URL.Query().Get("id")
	if id == "" {
		http.Error(w, "paramètre id manquant", http.StatusBadRequest)
		return
	}

	var t Livraison
	err := config.DB.QueryRow(`
		SELECT id_livraison, id_association, id_vehicule, date_livraison,
			   heure_depart, heure_retour, statut, commentaire
		FROM livraison WHERE id_livraison = ?
	`, id).Scan(
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

	result, err := config.DB.Exec(`
		INSERT INTO livraison (id_association, id_vehicule, date_livraison, heure_depart, heure_retour, statut, commentaire)
		VALUES (?,?,?,?,?,?,?)
	`, t.IDAssociation, t.IDVehicule, t.DateLivraison, t.HeureDepart, t.HeureRetour, t.Statut, t.Commentaire)

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

	_, err := config.DB.Exec(`
		UPDATE livraison
		SET id_association=?, id_vehicule=?, date_livraison=?, heure_depart=?, heure_retour=?, statut=?, commentaire=?
		WHERE id_livraison=?
	`, t.IDAssociation, t.IDVehicule, t.DateLivraison, t.HeureDepart, t.HeureRetour, t.Statut, t.Commentaire, t.IDLivraison)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	json.NewEncoder(w).Encode(map[string]string{"message": "Tournée modifiée"})
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

	// ON DELETE CASCADE supprime aussi les lignes (produits) de cette tournée.
	_, err := config.DB.Exec(`DELETE FROM livraison WHERE id_livraison = ?`, body.IDLivraison)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	json.NewEncoder(w).Encode(map[string]string{"message": "Tournée supprimée"})
}

// ---------- Produits chargés dans une tournée ----------

// GetLignesLivraison renvoie les produits chargés pour une tournée précise.
// /api/lignes-livraison?id_livraison=3
func GetLignesLivraison(w http.ResponseWriter, r *http.Request) {
	w.Header().Set("Content-Type", "application/json")

	idLivraison := r.URL.Query().Get("id_livraison")
	if idLivraison == "" {
		http.Error(w, "paramètre id_livraison manquant", http.StatusBadRequest)
		return
	}

	rows, err := config.DB.Query(`
		SELECT dl.id_livraison, dl.id_produit, dl.quantite, p.nom
		FROM detail_livraison dl
		JOIN produit p ON p.id_produit = dl.id_produit
		WHERE dl.id_livraison = ?
	`, idLivraison)
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

	_, err := config.DB.Exec(`
		INSERT INTO detail_livraison (id_livraison, id_produit, quantite)
		VALUES (?,?,?)
	`, l.IDLivraison, l.IDProduit, l.Quantite)

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

	_, err := config.DB.Exec(`
		DELETE FROM detail_livraison WHERE id_livraison = ? AND id_produit = ?
	`, l.IDLivraison, l.IDProduit)

	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	json.NewEncoder(w).Encode(map[string]string{"message": "Produit retiré de la tournée"})
}

// ---------- Listes utilitaires (pour remplir les menus déroulants) ----------

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
