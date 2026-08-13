package auth

import (
	"context"
	"encoding/json"
	"net/http"
	"strings"
	"time"

	"github.com/golang-jwt/jwt/v5"
	"golang.org/x/crypto/bcrypt"

	"github.com/MMina040/PA-No-More-Waste/config"
)

// ========== Jeton (JWT) ==========

// Clé secrète utilisée pour signer les jetons.
var cleSecrete = []byte("nmw-secret-key-a-changer-en-prod")

// InformationsJeton = ce qui est écrit à l'intérieur d'un jeton de connexion.
type InformationsJeton struct {
	IDUtilisateur int    `json:"id_utilisateur"`
	Role          string `json:"role"`
	jwt.RegisteredClaims
}

// GenererJeton crée un nouveau jeton de connexion, valable 24h.
func GenererJeton(idUtilisateur int, role string) (string, error) {
	informations := InformationsJeton{
		IDUtilisateur: idUtilisateur,
		Role:          role,
		RegisteredClaims: jwt.RegisteredClaims{
			ExpiresAt: jwt.NewNumericDate(time.Now().Add(24 * time.Hour)),
			IssuedAt:  jwt.NewNumericDate(time.Now()),
		},
	}

	jeton := jwt.NewWithClaims(jwt.SigningMethodHS256, informations)
	return jeton.SignedString(cleSecrete)
}

// VerifierJeton vérifie qu'un jeton est authentique et pas expiré.
func VerifierJeton(jetonTexte string) (*InformationsJeton, error) {
	informations := &InformationsJeton{}

	jeton, err := jwt.ParseWithClaims(jetonTexte, informations, func(j *jwt.Token) (interface{}, error) {
		return cleSecrete, nil
	})
	if err != nil || !jeton.Valid {
		return nil, err
	}

	return informations, nil
}

// ========== Middleware (protection des routes) ==========

type cleContexte string

const cleInformationsConnecte cleContexte = "informationsConnecte"

// VerifierConnexion bloque la requête si aucun jeton valide n'est fourni.
func VerifierConnexion(suite http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		enTete := r.Header.Get("Authorization")
		if !strings.HasPrefix(enTete, "Bearer ") {
			http.Error(w, "connexion requise", http.StatusUnauthorized)
			return
		}
		jetonTexte := strings.TrimPrefix(enTete, "Bearer ")

		informations, err := VerifierJeton(jetonTexte)
		if err != nil {
			http.Error(w, "jeton invalide ou expiré", http.StatusUnauthorized)
			return
		}

		contexte := context.WithValue(r.Context(), cleInformationsConnecte, informations)
		suite(w, r.WithContext(contexte))
	}
}

// VerifierRole vérifie la connexion, puis le rôle de la personne connectée.
func VerifierRole(rolesAutorises ...string) func(http.HandlerFunc) http.HandlerFunc {
	return func(suite http.HandlerFunc) http.HandlerFunc {
		return VerifierConnexion(func(w http.ResponseWriter, r *http.Request) {
			informations := RecupererInformationsConnecte(r)
			for _, role := range rolesAutorises {
				if informations.Role == role {
					suite(w, r)
					return
				}
			}
			http.Error(w, "accès refusé pour ce rôle", http.StatusForbidden)
		})
	}
}

// RecupererInformationsConnecte récupère les infos de la personne connectée.
func RecupererInformationsConnecte(r *http.Request) *InformationsJeton {
	informations, _ := r.Context().Value(cleInformationsConnecte).(*InformationsJeton)
	return informations
}

// ========== Connexion (login) ==========

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

	motDePasseValide := false
	dejaChiffreAvecBcrypt := strings.HasPrefix(motDePasseEnBase, "$2a$") ||
		strings.HasPrefix(motDePasseEnBase, "$2b$") ||
		strings.HasPrefix(motDePasseEnBase, "$2y$")

	if dejaChiffreAvecBcrypt {
		if bcrypt.CompareHashAndPassword([]byte(motDePasseEnBase), []byte(identifiants.MotDePasse)) == nil {
			motDePasseValide = true
		}
	} else {
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

	if role == "VISITEUR" {
		w.WriteHeader(http.StatusForbidden)
		json.NewEncoder(w).Encode(map[string]string{
			"error":   "COMPTE_PUBLIC",
			"message": "Ce compte technique est utilisé uniquement pour les demandes publiques.",
		})
		return
	}

	if !compteActif {
		message := "Votre candidature bénévole est en attente de validation par un administrateur."
		if role == "COMMERCANT" {
			message = "Votre demande de compte commerçant est en attente de validation par un administrateur."
		}

		w.WriteHeader(http.StatusForbidden)
		json.NewEncoder(w).Encode(map[string]string{
			"error":   "COMPTE_EN_ATTENTE",
			"message": message,
		})
		return
	}

	jeton, err := GenererJeton(idUtilisateur, role)
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
