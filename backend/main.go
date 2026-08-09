package main

import (
	"fmt"
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/MMina040/PA-No-More-Waste/routes"
)

func main() {

	// Connect to the database
	config.ConnectDatabase()

	// Route of test
	routes.RegisterRoutes()

	// Message affiché au démarrage
	fmt.Println("===================================")
	fmt.Println("  No More Waste API")
	fmt.Println("  Serveur démarré sur le port 8080")
	fmt.Println("===================================")

	// Lancement du serveur
	err := http.ListenAndServe(":8080", nil)

	if err != nil {
		fmt.Println("Erreur :", err)
	}
}
