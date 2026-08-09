package routes

import (
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/handlers"
	"github.com/MMina040/PA-No-More-Waste/middleware"
)

// Mmina :j'ai modifié en entier pour que chaque route de l'API vérifie que la personne est connectée
// (et, pour les actions de création/modification/suppression, qu'elle a le bon rôle) avant d'y répondre (sans rien supprimer des routes existantes)
func RegisterRoutes() {

	// ---------- Publique (pas besoin d'être connecté) ----------
	http.HandleFunc("/api/test", handlers.TestHandler)
	http.HandleFunc("/api/auth/login", handlers.Connexion)
	http.HandleFunc("/api/public/services", handlers.ObtenirServicesPublics)
	http.HandleFunc("/api/public/inscription", handlers.InscrirePublique)
	http.HandleFunc("/api/public/demande-collecte", handlers.DemanderCollectePublique)
	http.HandleFunc("/api/public/candidature-benevole", handlers.CandidaterBenevole)

	// ---------- Utilisateurs / commerçants ----------
	http.HandleFunc("/api/utilisateurs", middleware.VerifierConnexion(handlers.GetUtilisateurs))
	http.HandleFunc("/api/utilisateurs/get", middleware.VerifierConnexion(handlers.GetUtilisateur))
	http.HandleFunc("/api/utilisateurs/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateUtilisateur))
	http.HandleFunc("/api/utilisateurs/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateUtilisateur))
	http.HandleFunc("/api/utilisateurs/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteUtilisateur))

	// ---------- Adhésions ----------
	http.HandleFunc("/api/adhesions", middleware.VerifierConnexion(handlers.GetAdhesions))
	http.HandleFunc("/api/adhesions/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateAdhesion))
	http.HandleFunc("/api/adhesions/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateAdhesion))
	http.HandleFunc("/api/adhesions/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteAdhesion))
	http.HandleFunc("/api/rappels/a-envoyer", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.ObtenirAdhesionsARelancer))
	http.HandleFunc("/api/rappels/marquer", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.MarquerRappelEnvoye))

	// ---------- Produits ----------
	http.HandleFunc("/api/produits", middleware.VerifierConnexion(handlers.GetProduits))
	http.HandleFunc("/api/produits/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateProduit))
	http.HandleFunc("/api/produits/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateProduit))
	http.HandleFunc("/api/produits/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteProduit))

	// ---------- Catégories ----------
	http.HandleFunc("/api/categories", middleware.VerifierConnexion(handlers.GetCategories))
	http.HandleFunc("/api/categories/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateCategorie))
	http.HandleFunc("/api/categories/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateCategorie))
	http.HandleFunc("/api/categories/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteCategorie))

	// ---------- Collectes ----------
	http.HandleFunc("/api/collectes", middleware.VerifierConnexion(handlers.GetCollectes))
	http.HandleFunc("/api/collectes/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateCollecte))
	http.HandleFunc("/api/collectes/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateCollecte))
	http.HandleFunc("/api/collectes/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteCollecte))

	http.HandleFunc("/api/detail_collectes", middleware.VerifierConnexion(handlers.GetDetailCollectes))
	http.HandleFunc("/api/detail_collectes/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateDetailCollecte))
	http.HandleFunc("/api/detail-collectes/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateDetailCollecte))
	http.HandleFunc("/api/detail-collectes/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteDetailCollecte))

	http.HandleFunc("/api/collecte_benevoles", middleware.VerifierConnexion(handlers.GetCollectesBenevoles))
	http.HandleFunc("/api/collecte_benevoles/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateCollecteBenevole))
	http.HandleFunc("/api/collecte_benevoles/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateCollecteBenevole))
	http.HandleFunc("/api/collecte_benevoles/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteCollecteBenevole))

	// ---------- Stocks ----------
	http.HandleFunc("/api/stocks", middleware.VerifierConnexion(handlers.GetStocks))
	http.HandleFunc("/api/stocks/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateStock))
	http.HandleFunc("/api/stocks/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateStock))
	http.HandleFunc("/api/stocks/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteStock))

	http.HandleFunc("/api/mouvements-stock", middleware.VerifierConnexion(handlers.GetMouvementsStock))
	http.HandleFunc("/api/mouvements-stock/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateMouvementStock))
	http.HandleFunc("/api/mouvements-stock/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateMouvementStock))
	http.HandleFunc("/api/mouvements-stock/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteMouvementStock))

	// ---------- Services & inscriptions ----------
	http.HandleFunc("/api/services", middleware.VerifierConnexion(handlers.GetServices))
	http.HandleFunc("/api/services/get", middleware.VerifierConnexion(handlers.GetService))
	http.HandleFunc("/api/services/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateService))
	http.HandleFunc("/api/services/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateService))
	http.HandleFunc("/api/services/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteService))

	http.HandleFunc("/api/inscriptions", middleware.VerifierConnexion(handlers.GetInscriptions))
	http.HandleFunc("/api/inscriptions/create", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateInscription))
	http.HandleFunc("/api/inscriptions/update", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateInscription))
	http.HandleFunc("/api/inscriptions/delete", middleware.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteInscription))
}
