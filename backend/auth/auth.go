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

var cleSecrete = []byte("nmw-secret-key-a-changer-en-prod")

type InformationsJeton struct {
	IDUtilisateur int    `json:"id_utilisateur"`
	Role          string `json:"role"`
	jwt.RegisteredClaims
}

// Génère un token valable 24h
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

type cleContexte string

const cleInformationsConnecte cleContexte = "informationsConnecte"

// Vérifie que l'utilisateur est connecté.
// Le compte est relu en base afin qu'une désactivation prenne effet immédiatement,
// même si l'utilisateur possède encore un JWT valide.
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

		var compteActif bool
		var statutCompte string

		err = config.DB.QueryRow(`
			SELECT actif, statut_compte
			FROM utilisateur
			WHERE id_utilisateur = ?
		`, informations.IDUtilisateur).Scan(&compteActif, &statutCompte)

		if err != nil {
			http.Error(w, "compte introuvable", http.StatusUnauthorized)
			return
		}

		if !compteActif || (informations.Role == "COMMERCANT" && statutCompte != "ACTIF") {
			w.Header().Set("Content-Type", "application/json")
			w.WriteHeader(http.StatusForbidden)

			message := "Ce compte n'est pas actif."
			codeErreur := "COMPTE_INACTIF"

			if informations.Role == "COMMERCANT" {
				switch statutCompte {
				case "EN_ATTENTE":
					message = "Votre demande de compte commerçant est en attente de validation par un administrateur."
					codeErreur = "COMPTE_EN_ATTENTE"
				case "DESACTIVE":
					message = "Votre compte commerçant a été désactivé. Contactez un responsable de l'association."
					codeErreur = "COMPTE_DESACTIVE"
				}
			}

			json.NewEncoder(w).Encode(map[string]string{
				"error":   codeErreur,
				"message": message,
			})
			return
		}

		contexte := context.WithValue(r.Context(), cleInformationsConnecte, informations)
		suite(w, r.WithContext(contexte))
	}
}

// Vérifie si le rôle est autorisé
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

func RecupererInformationsConnecte(r *http.Request) *InformationsJeton {
	informations, _ := r.Context().Value(cleInformationsConnecte).(*InformationsJeton)
	return informations
}

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
	var statutCompte string
	var compteActif bool

	err := config.DB.QueryRow(`
		SELECT id_utilisateur, nom, prenom, email, mot_de_passe, role, actif, statut_compte
		FROM utilisateur
		WHERE email = ?
	`, identifiants.Email).Scan(
		&idUtilisateur,
		&nom,
		&prenom,
		&email,
		&motDePasseEnBase,
		&role,
		&compteActif,
		&statutCompte,
	)

	if err != nil {
		http.Error(w, "email ou mot de passe incorrect", http.StatusUnauthorized)
		return
	}

	motDePasseValide := false

	dejaChiffreAvecBcrypt :=
		strings.HasPrefix(motDePasseEnBase, "$2a$") ||
			strings.HasPrefix(motDePasseEnBase, "$2b$") ||
			strings.HasPrefix(motDePasseEnBase, "$2y$")

	if dejaChiffreAvecBcrypt {
		if bcrypt.CompareHashAndPassword(
			[]byte(motDePasseEnBase),
			[]byte(identifiants.MotDePasse),
		) == nil {
			motDePasseValide = true
		}
	} else {
		if motDePasseEnBase == identifiants.MotDePasse {
			motDePasseValide = true

			nouveauHash, err := bcrypt.GenerateFromPassword(
				[]byte(identifiants.MotDePasse),
				bcrypt.DefaultCost,
			)

			if err == nil {
				config.DB.Exec(
					`UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?`,
					string(nouveauHash),
					idUtilisateur,
				)
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

	if role == "COMMERCANT" {
		if statutCompte == "EN_ATTENTE" {
			w.WriteHeader(http.StatusForbidden)
			json.NewEncoder(w).Encode(map[string]string{
				"error":   "COMPTE_EN_ATTENTE",
				"message": "Votre demande de compte commerçant est en attente de validation par un administrateur.",
			})
			return
		}

		if statutCompte == "DESACTIVE" || !compteActif {
			w.WriteHeader(http.StatusForbidden)
			json.NewEncoder(w).Encode(map[string]string{
				"error":   "COMPTE_DESACTIVE",
				"message": "Votre compte commerçant a été désactivé. Contactez un responsable de l'association.",
			})
			return
		}
	} else if !compteActif {
		message := "Votre candidature bénévole est en attente de validation par un administrateur."

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
