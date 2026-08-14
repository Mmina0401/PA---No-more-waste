package handlers

import (
	"log"
	"time"

	"github.com/MMina040/PA-No-More-Waste/config"
)

func ArchiverEvenementsPasses() {
	_, err := config.DB.Exec(`
		UPDATE collecte
		SET statut = 'TERMINEE'
		WHERE date_collecte < CURDATE()
		AND statut NOT IN ('TERMINEE', 'ANNULEE')
	`)
	if err != nil {
		log.Println("Erreur archivage collectes :", err)
	}

	_, err = config.DB.Exec(`
		UPDATE service
		SET statut = 'TERMINE'
		WHERE date_service < CURDATE()
		AND statut NOT IN ('TERMINE', 'ANNULE')
	`)
	if err != nil {
		log.Println("Erreur archivage services :", err)
	}

	_, err = config.DB.Exec(`
		UPDATE offre_benevole ob
		SET ob.statut = 'FERMEE'
		WHERE ob.statut = 'OUVERTE'
		AND (
			(
				ob.type_evenement = 'COLLECTE'
				AND EXISTS (
					SELECT 1
					FROM collecte c
					WHERE c.id_collecte = ob.id_evenement
					AND c.date_collecte < CURDATE()
				)
			)
			OR
			(
				ob.type_evenement = 'SERVICE'
				AND EXISTS (
					SELECT 1
					FROM service s
					WHERE s.id_service = ob.id_evenement
					AND s.date_service < CURDATE()
				)
			)
		)
	`)
	if err != nil {
		log.Println("Erreur archivage offres :", err)
	}
}

func LancerArchivageAutomatique() {
	ArchiverEvenementsPasses()

	ticker := time.NewTicker(1 * time.Hour)

	go func() {
		for range ticker.C {
			ArchiverEvenementsPasses()
		}
	}()
}
