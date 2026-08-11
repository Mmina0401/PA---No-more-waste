package handlers

import (
	"fmt"
	"net/http"
	"time"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/xuri/excelize/v2"
)

func ExportPlanning(w http.ResponseWriter, r *http.Request) {
	rows, err := config.DB.Query(`
		SELECT
			c.date_collecte,
			c.heure_debut,
			c.heure_fin,
			u.nom,
			u.prenom,
			cb.role_collecte,
			c.adresse,
			c.ville,
			c.code_postal,
			c.statut
		FROM collecte c
		INNER JOIN collecte_benevole cb
			ON c.id_collecte = cb.id_collecte
		INNER JOIN utilisateur u
			ON cb.id_utilisateur = u.id_utilisateur
		ORDER BY c.date_collecte, c.heure_debut, u.nom
	`)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
	defer rows.Close()

	f := excelize.NewFile()
	defer f.Close()

	sheet := "Planning"
	f.SetSheetName("Sheet1", sheet)

	headers := []string{
		"Date",
		"Heure début",
		"Heure fin",
		"Nom",
		"Prénom",
		"Rôle",
		"Adresse",
		"Ville",
		"Code postal",
		"Statut",
	}

	for i, header := range headers {
		cell, _ := excelize.CoordinatesToCellName(i+1, 1)
		f.SetCellValue(sheet, cell, header)
	}

	row := 2

	for rows.Next() {
		var date time.Time
		var heureDebut string
		var heureFin string
		var nom string
		var prenom string
		var role string
		var adresse string
		var ville string
		var codePostal string
		var statut string

		err := rows.Scan(
			&date,
			&heureDebut,
			&heureFin,
			&nom,
			&prenom,
			&role,
			&adresse,
			&ville,
			&codePostal,
			&statut,
		)
		if err != nil {
			http.Error(w, err.Error(), http.StatusInternalServerError)
			return
		}

		values := []interface{}{
			date.Format("02/01/2006"),
			heureDebut,
			heureFin,
			nom,
			prenom,
			role,
			adresse,
			ville,
			codePostal,
			statut,
		}

		for i, value := range values {
			cell, _ := excelize.CoordinatesToCellName(i+1, row)
			f.SetCellValue(sheet, cell, value)
		}

		row++
	}

	if err := rows.Err(); err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}

	f.SetColWidth(sheet, "A", "A", 14)
	f.SetColWidth(sheet, "B", "C", 14)
	f.SetColWidth(sheet, "D", "F", 18)
	f.SetColWidth(sheet, "G", "G", 30)
	f.SetColWidth(sheet, "H", "H", 20)
	f.SetColWidth(sheet, "I", "J", 15)

	filename := fmt.Sprintf(
		"planning_%s.xlsx",
		time.Now().Format("2006-01-02"),
	)

	w.Header().Set(
		"Content-Type",
		"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
	)

	w.Header().Set(
		"Content-Disposition",
		"attachment; filename="+filename,
	)

	if err := f.Write(w); err != nil {
		http.Error(w, "Erreur génération Excel", http.StatusInternalServerError)
		return
	}
}
