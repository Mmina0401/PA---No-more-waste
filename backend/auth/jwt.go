package auth

import (
	"time"

	// outil installé pour fabriquer les badges
	"github.com/golang-jwt/jwt/v5"
)

// Clé secrète utilisée pour signer les jetons (comme un tampon officiel).
var cleSecrete = []byte("nmw-secret-key-a-changer-en-prod")

// Ce qui est écrit à l'intérieur d'un jeton de connexion.
type InformationsJeton struct {
	IDUtilisateur int    `json:"id_utilisateur"`
	Role          string `json:"role"`
	jwt.RegisteredClaims
}

// GenererJeton crée un nouveau jeton de connexion, valable 24h, pour un utilisateur donné.
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

// VerifierJeton vérifie qu'un jeton est authentique (signature correcte, pas expiré) et renvoie les informations qu'il contient.
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
