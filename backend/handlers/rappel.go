package handlers

import (
	"encoding/json"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
)

// AdhesionARelancer représente une adhésion qui arrive bientôt à expiration,
// avec les informations du commerçant pour pouvoir le recontacter.
type AdhesionARelancer struct {
	IDAdhesion    int    `json:"id_adhesion"`
	IDUtilisateur int    `json:"id_utilisateur"`
	NomCommercant string `json:"nom_commercant"`
	Email         string `json:"email"`
	DateFin       string `json:"date_fin"`
	JoursRestants int    `json:"jours_restants"`
}

// ObtenirAdhesionsARelancer renvoie les adhésions actives qui expirent dans
// les 30 prochains jours et qui n'ont pas encore été relancées.
// GET /api/rappels/a-envoyer
func ObtenirAdhesionsARelancer(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	lignes, err := config.DB.Query(`
		SELECT
			a.id_adhesion,
			a.id_utilisateur,
			COALESCE(u.raison_sociale, CONCAT(u.prenom, ' ', u.nom)) AS nom_commercant,
			u.email,
			a.date_fin,
			DATEDIFF(a.date_fin, CURDATE()) AS jours_restants
		FROM adhesion a
		JOIN utilisateur u ON u.id_utilisateur = a.id_utilisateur
		WHERE a.statut = 'ACTIVE'
		  AND a.rappel_envoye = FALSE
		  AND a.date_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
		ORDER BY a.date_fin ASC
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer lignes.Close()

	var resultat []AdhesionARelancer

	for lignes.Next() {
		var a AdhesionARelancer
		if err := lignes.Scan(
			&a.IDAdhesion, &a.IDUtilisateur, &a.NomCommercant, &a.Email,
			&a.DateFin, &a.JoursRestants,
		); err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}
		resultat = append(resultat, a)
	}

	json.NewEncoder(w).Encode(resultat)
}

// MarquerRappelEnvoye indique qu'une relance a été envoyée pour une adhésion,
// pour qu'elle n'apparaisse plus dans la liste à relancer.
// POST /api/rappels/marquer   corps attendu : {"id_adhesion": 3}
func MarquerRappelEnvoye(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var corps struct {
		IDAdhesion int `json:"id_adhesion"`
	}
	if err := json.NewDecoder(r.Body).Decode(&corps); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	_, err := config.DB.Exec(`UPDATE adhesion SET rappel_envoye = TRUE WHERE id_adhesion = ?`, corps.IDAdhesion)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]string{"message": "rappel marqué comme envoyé"})
}
