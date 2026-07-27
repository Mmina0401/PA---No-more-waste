package handlers

import (
	"fmt"
	"net/http"
)

func TestHandler(w http.ResponseWriter, r *http.Request) {

	w.Header().Set("Content-Type", "application/json")

	fmt.Fprint(w, `{
		"success": true,
		"message": "Bienvenue sur l'API No More Waste"
	}`)
}
