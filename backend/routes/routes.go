package routes

import (
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/handlers"
)

func RegisterRoutes() {

	http.HandleFunc("/api/test", handlers.TestHandler)

	http.HandleFunc("/api/adhesions", handlers.GetAdhesions)

	http.HandleFunc("/api/utilisateurs", handlers.GetUtilisateurs)

	http.HandleFunc("/api/services", handlers.GetServices)

	http.HandleFunc("/api/services/get", handlers.GetService)

	http.HandleFunc("/api/services/create", handlers.CreateService)

	http.HandleFunc("/api/services/update", handlers.UpdateService)

	http.HandleFunc("/api/services/delete", handlers.DeleteService)

	http.HandleFunc("/api/inscriptions", handlers.GetInscriptions)

	http.HandleFunc("/api/inscriptions/create", handlers.CreateInscription)

	http.HandleFunc("/api/inscriptions/update", handlers.UpdateInscription)

	http.HandleFunc("/api/inscriptions/delete", handlers.DeleteInscription)

	http.HandleFunc("/api/produits", handlers.GetProduits)

	http.HandleFunc("/api/produits/create", handlers.CreateProduit)

	http.HandleFunc("/api/produits/update", handlers.UpdateProduit)

	http.HandleFunc("/api/produits/delete", handlers.DeleteProduit)

	http.HandleFunc("/api/adhesions/create", handlers.CreateAdhesion)

	http.HandleFunc("/api/adhesions/update", handlers.UpdateAdhesion)

	http.HandleFunc("/api/adhesions/delete", handlers.DeleteAdhesion)

	http.HandleFunc("/api/categories", handlers.GetCategories)

	http.HandleFunc("/api/categories/create", handlers.CreateCategorie)

	http.HandleFunc("/api/categories/update", handlers.UpdateCategorie)

	http.HandleFunc("/api/categories/delete", handlers.DeleteCategorie)

	http.HandleFunc("/api/collectes", handlers.GetCollectes)

	http.HandleFunc("/api/collectes/create", handlers.CreateCollecte)

	http.HandleFunc("/api/collectes/update", handlers.UpdateCollecte)

	http.HandleFunc("/api/collectes/delete", handlers.DeleteCollecte)

	http.HandleFunc("/api/detail_collectes", handlers.GetDetailCollectes)

	http.HandleFunc("/api/detail_collectes/create", handlers.CreateDetailCollecte)

	http.HandleFunc("/api/collecte_benevoles", handlers.GetCollectesBenevoles)

	http.HandleFunc("/api/collecte_benevoles/create", handlers.CreateCollecteBenevole)

	http.HandleFunc("/api/detail-collectes/update", handlers.UpdateDetailCollecte)

	http.HandleFunc("/api/detail-collectes/delete", handlers.DeleteDetailCollecte)

	http.HandleFunc("/api/collecte_benevoles/update", handlers.UpdateCollecteBenevole)

	http.HandleFunc("/api/collecte_benevoles/delete", handlers.DeleteCollecteBenevole)

	http.HandleFunc("/api/stocks", handlers.GetStocks)

	http.HandleFunc("/api/stocks/create", handlers.CreateStock)

	http.HandleFunc("/api/stocks/update", handlers.UpdateStock)

	http.HandleFunc("/api/stocks/delete", handlers.DeleteStock)

	http.HandleFunc("/api/mouvements-stock", handlers.GetMouvementsStock)

	http.HandleFunc("/api/mouvements-stock/create", handlers.CreateMouvementStock)

	http.HandleFunc("/api/mouvements-stock/update", handlers.UpdateMouvementStock)

	http.HandleFunc("/api/mouvements-stock/delete", handlers.DeleteMouvementStock)

}
