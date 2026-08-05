package middleware

import (
	"context"
	"net/http"
	"strings"

	"github.com/MMina040/PA-No-More-Waste/auth"
)

type cleContexte string

const cleInformationsConnecte cleContexte = "informationsConnecte"

// VerifierConnexion bloque la requête si aucun jeton valide n'est fourni.
// Si tout va bien, les informations de la personne connectée sont ajoutées
// à la requête pour que la suite puisse les récupérer avec RecupererInformationsConnecte(r).
func VerifierConnexion(suite http.HandlerFunc) http.HandlerFunc {
	return func(w http.ResponseWriter, r *http.Request) {
		enTete := r.Header.Get("Authorization")
		if !strings.HasPrefix(enTete, "Bearer ") {
			http.Error(w, "connexion requise", http.StatusUnauthorized)
			return
		}
		jetonTexte := strings.TrimPrefix(enTete, "Bearer ")

		informations, err := auth.VerifierJeton(jetonTexte)
		if err != nil {
			http.Error(w, "jeton invalide ou expiré", http.StatusUnauthorized)
			return
		}

		contexte := context.WithValue(r.Context(), cleInformationsConnecte, informations)
		suite(w, r.WithContext(contexte))
	}
}

// VerifierRole vérifie la connexion, puis vérifie en plus que le rôle de la
// personne connectée fait partie des rôles autorisés.
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

// RecupererInformationsConnecte récupère les informations de la personne
// connectée depuis la requête. Ne renvoie quelque chose d'utile que si
// VerifierConnexion (ou VerifierRole) a déjà validé la requête avant.
func RecupererInformationsConnecte(r *http.Request) *auth.InformationsJeton {
	informations, _ := r.Context().Value(cleInformationsConnecte).(*auth.InformationsJeton)
	return informations
}
