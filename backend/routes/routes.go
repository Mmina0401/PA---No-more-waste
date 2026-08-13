package routes

import (
	"net/http"

	"github.com/MMina040/PA-No-More-Waste/auth"
	"github.com/MMina040/PA-No-More-Waste/handlers"
)

func RegisterRoutes() {

	// ---------- Publique ----------
	http.HandleFunc("/api/test", handlers.TestHandler)
	http.HandleFunc("/api/auth/login", auth.Connexion)
	http.HandleFunc("/api/public/services", handlers.ObtenirServicesPublics)
	http.HandleFunc("/api/public/inscription", handlers.InscrirePublique)
	http.HandleFunc("/api/public/demande-collecte", handlers.DemanderCollectePublique)
	http.HandleFunc("/api/public/candidature-benevole", handlers.CandidaterBenevole)

	// ---------- Espace bénévole ----------
	http.HandleFunc(
		"/api/benevole/planning",
		auth.VerifierRole("BENEVOLE")(handlers.GetPlanningBenevole),
	)

	// ---------- Gestion des bénévoles ----------
	http.HandleFunc(
		"/api/benevoles",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.GetBenevoles),
	)

	http.HandleFunc(
		"/api/benevoles/update",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateBenevole),
	)

	http.HandleFunc(
		"/api/benevoles/delete",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteBenevole),
	)

	http.HandleFunc(
		"/api/benevoles/disponibilites",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.AddDisponibiliteBenevole),
	)

	// ---------- Planning Excel ----------
	http.HandleFunc(
		"/api/planning/export",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.ExportPlanning),
	)

	// ---------- Utilisateurs / commerçants ----------
	http.HandleFunc("/api/utilisateurs", auth.VerifierConnexion(handlers.GetUtilisateurs))
	http.HandleFunc("/api/utilisateurs/get", auth.VerifierConnexion(handlers.GetUtilisateur))
	http.HandleFunc("/api/utilisateurs/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateUtilisateur))
	http.HandleFunc("/api/utilisateurs/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateUtilisateur))
	http.HandleFunc("/api/utilisateurs/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteUtilisateur))

	// ---------- Adhésions ----------
	http.HandleFunc("/api/adhesions", auth.VerifierConnexion(handlers.GetAdhesions))
	http.HandleFunc("/api/adhesions/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateAdhesion))
	http.HandleFunc("/api/adhesions/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateAdhesion))
	http.HandleFunc("/api/adhesions/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteAdhesion))

	// ---------- Rappels ----------
	http.HandleFunc("/api/rappels/a-envoyer", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.ObtenirAdhesionsARelancer))
	http.HandleFunc("/api/rappels/marquer", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.MarquerRappelEnvoye))

	// ---------- Produits ----------
	http.HandleFunc("/api/produits", auth.VerifierConnexion(handlers.GetProduits))
	http.HandleFunc("/api/produits/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateProduit))
	http.HandleFunc("/api/produits/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateProduit))
	http.HandleFunc("/api/produits/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteProduit))

	// ---------- Catégories ----------
	http.HandleFunc("/api/categories", auth.VerifierConnexion(handlers.GetCategories))
	http.HandleFunc("/api/categories/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateCategorie))
	http.HandleFunc("/api/categories/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateCategorie))
	http.HandleFunc("/api/categories/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteCategorie))

	// ---------- Collectes ----------
	http.HandleFunc("/api/collectes", auth.VerifierConnexion(handlers.GetCollectes))
	http.HandleFunc("/api/collectes/get", auth.VerifierConnexion(handlers.GetCollecte))
	http.HandleFunc("/api/collectes/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateCollecte))
	http.HandleFunc("/api/collectes/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateCollecte))
	http.HandleFunc("/api/collectes/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteCollecte))

	http.HandleFunc("/api/detail_collectes", auth.VerifierConnexion(handlers.GetDetailCollectes))
	http.HandleFunc("/api/detail_collectes/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateDetailCollecte))
	http.HandleFunc("/api/detail-collectes/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateDetailCollecte))
	http.HandleFunc("/api/detail-collectes/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteDetailCollecte))

	http.HandleFunc("/api/collecte_benevoles", auth.VerifierConnexion(handlers.GetCollectesBenevoles))
	http.HandleFunc("/api/collecte_benevoles/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateCollecteBenevole))
	http.HandleFunc("/api/collecte_benevoles/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateCollecteBenevole))
	http.HandleFunc("/api/collecte_benevoles/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteCollecteBenevole))

	// ---------- Stocks ----------
	http.HandleFunc("/api/stocks", auth.VerifierConnexion(handlers.GetStocks))
	http.HandleFunc("/api/stocks/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateStock))
	http.HandleFunc("/api/stocks/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateStock))
	http.HandleFunc("/api/stocks/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteStock))

	http.HandleFunc("/api/mouvements-stock", auth.VerifierConnexion(handlers.GetMouvementsStock))
	http.HandleFunc("/api/mouvements-stock/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateMouvementStock))
	http.HandleFunc("/api/mouvements-stock/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateMouvementStock))
	http.HandleFunc("/api/mouvements-stock/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteMouvementStock))

	// ---------- Services & inscriptions ----------
	http.HandleFunc("/api/services", auth.VerifierConnexion(handlers.GetServices))
	http.HandleFunc("/api/services/get", auth.VerifierConnexion(handlers.GetService))
	http.HandleFunc("/api/services/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateService))
	http.HandleFunc("/api/services/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateService))
	http.HandleFunc("/api/services/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteService))

	http.HandleFunc("/api/inscriptions", auth.VerifierConnexion(handlers.GetInscriptions))
	http.HandleFunc("/api/inscriptions/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateInscription))
	http.HandleFunc("/api/inscriptions/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateInscription))
	http.HandleFunc("/api/inscriptions/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteInscription))

	// ---------- Tournées ----------
	http.HandleFunc("/api/livraisons", auth.VerifierConnexion(handlers.GetLivraisons))
	http.HandleFunc("/api/livraisons/get", auth.VerifierConnexion(handlers.GetLivraison))
	http.HandleFunc("/api/livraisons/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateLivraison))
	http.HandleFunc("/api/livraisons/update", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.UpdateLivraison))
	http.HandleFunc("/api/livraisons/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.DeleteLivraison))

	http.HandleFunc("/api/lignes-livraison", auth.VerifierConnexion(handlers.GetLignesLivraison))
	http.HandleFunc("/api/lignes-livraison/create", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.AjouterLigneLivraison))
	http.HandleFunc("/api/lignes-livraison/delete", auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.SupprimerLigneLivraison))

	http.HandleFunc("/api/associations", auth.VerifierConnexion(handlers.GetAssociations))
	http.HandleFunc("/api/vehicules", auth.VerifierConnexion(handlers.GetVehicules))

	http.HandleFunc("/api/livraisons/pdf", auth.VerifierConnexion(handlers.GenererPDFLivraison))

	http.HandleFunc(
		"/api/offres-benevoles",
		auth.VerifierRole("ADMIN", "RESPONSABLE", "BENEVOLE")(handlers.GetOffresBenevoles),
	)

	http.HandleFunc(
		"/api/offres-benevoles/create",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.CreateOffreBenevole),
	)

	http.HandleFunc(
		"/api/offres-benevoles/repondre",
		auth.VerifierRole("BENEVOLE")(handlers.RepondreOffreBenevole),
	)

	http.HandleFunc(
		"/api/offres-benevoles/reponses",
		auth.VerifierRole("ADMIN", "RESPONSABLE")(handlers.GetReponsesOffreBenevole),
	)

	http.HandleFunc(
		"/api/offres-benevoles/mes-reponses",
		auth.VerifierRole("BENEVOLE")(handlers.GetMesReponsesOffresBenevole),
	)

}
