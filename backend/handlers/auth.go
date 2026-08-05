package handlers

import (
	"encoding/json"
	"net/http"
	"strings"

	"golang.org/x/crypto/bcrypt"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/config"
)

// Connexion vérifie l'email + mot de passe, et renvoie un jeton si c'est bon.
// POST /api/auth/login   corps attendu : {"email": "...", "mot_de_passe": "..."}
func Connexion(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	if r.Method != http.MethodPost {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	var identifiants struct {
		Email      string `json:"email"`
		MotDePasse string `json:"mot_de_passe"`
	}
	if err := json.NewDecoder(r.Body).Decode(&identifiants); err != nil {
		http.Error(w, "JSON invalide", http.StatusBadRequest)
		return
	}

	var idUtilisateur int
	var nom, prenom, email, role, motDePasseEnBase string
	var compteActif bool

	err := config.DB.QueryRow(`
		SELECT id_utilisateur, nom, prenom, email, mot_de_passe, role, actif
		FROM utilisateur WHERE email = ?
	`, identifiants.Email).Scan(&idUtilisateur, &nom, &prenom, &email, &motDePasseEnBase, &role, &compteActif)

	if err != nil {
		http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
		return
	}

	if !compteActif {
		http.Error(w, "ce compte est désactivé", http.StatusForbidden)
		return
	}

	motDePasseValide := false
	dejaChiffreAvecBcrypt := strings.HasPrefix(motDePasseEnBase, "$2a$") ||
		strings.HasPrefix(motDePasseEnBase, "$2b$") ||
		strings.HasPrefix(motDePasseEnBase, "$2y$")

	if dejaChiffreAvecBcrypt {
		// Mot de passe déjà sécurisé : comparaison normale avec bcrypt.
		if bcrypt.CompareHashAndPassword([]byte(motDePasseEnBase), []byte(identifiants.MotDePasse)) == nil {
			motDePasseValide = true
		}
	} else {
		// Ancien mot de passe en clair (données de test type "admin123") :
		// on compare tel quel une dernière fois, puis on le remplace
		// discrètement par un hash bcrypt pour la prochaine connexion.
		if motDePasseEnBase == identifiants.MotDePasse {
			motDePasseValide = true
			if nouveauHash, err := bcrypt.GenerateFromPassword([]byte(identifiants.MotDePasse), bcrypt.DefaultCost); err == nil {
				config.DB.Exec(`UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?`, string(nouveauHash), idUtilisateur)
			}
		}
	}

	if !motDePasseValide {
		http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
		return
	}

	jeton, err := auth.GenererJeton(idUtilisateur, role)
	if err != nil {
		http.Error(w, "erreur lors de la génération du jeton", http.StatusInternalServerError)
		return
	}

	json.NewEncoder(w).Encode(map[string]interface{}{
		"jeton": jeton,
		"utilisateur": map[string]interface{}{
			"id_utilisateur": idUtilisateur,
			"nom":            nom,
			"prenom":         prenom,
			"email":          email,
			"role":           role,
		},
	})
}
