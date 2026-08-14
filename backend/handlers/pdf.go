package handlers

import (
	"database/sql"
	"fmt"
	"net/http"
	"strconv"

	"github.com/MMina040/PA-No-More-Waste/config"
	"github.com/go-pdf/fpdf"
)

func GenererPDFLivraison(w http.ResponseWriter, r *http.Request) {
	if r.Method != http.MethodGet {
		http.Error(w, "méthode non autorisée", http.StatusMethodNotAllowed)
		return
	}

	idTexte := r.URL.Query().Get("id")

	idLivraison, err := strconv.Atoi(idTexte)
	if err != nil || idLivraison <= 0 {
		http.Error(w, "identifiant de livraison invalide", http.StatusBadRequest)
		return
	}

	var (
		dateLivraison   string
		heureDepart     sql.NullString
		heureRetour     sql.NullString
		statut          string
		commentaire     sql.NullString
		association     string
		adresse         sql.NullString
		ville           sql.NullString
		codePostal      sql.NullString
		telephone       sql.NullString
		email           sql.NullString
		immatriculation string
		marque          string
		modele          string
	)

	err = config.DB.QueryRow(`
		SELECT
			DATE_FORMAT(l.date_livraison, '%d/%m/%Y'),
			l.heure_depart,
			l.heure_retour,
			l.statut,
			l.commentaire,
			a.nom,
			a.adresse,
			a.ville,
			a.code_postal,
			a.telephone,
			a.email,
			v.immatriculation,
			v.marque,
			v.modele
		FROM livraison l
		INNER JOIN association_beneficiaire a
			ON a.id_association = l.id_association
		INNER JOIN vehicule v
			ON v.id_vehicule = l.id_vehicule
		WHERE l.id_livraison = ?
	`, idLivraison).Scan(
		&dateLivraison,
		&heureDepart,
		&heureRetour,
		&statut,
		&commentaire,
		&association,
		&adresse,
		&ville,
		&codePostal,
		&telephone,
		&email,
		&immatriculation,
		&marque,
		&modele,
	)

	if err == sql.ErrNoRows {
		http.Error(w, "livraison introuvable", http.StatusNotFound)
		return
	}

	if err != nil {
		http.Error(w, "erreur lors de la lecture de la livraison", http.StatusInternalServerError)
		return
	}

	lignes, err := config.DB.Query(`
		SELECT
			p.nom,
			p.code_barre,
			p.unite,
			dl.quantite
		FROM detail_livraison dl
		INNER JOIN produit p
			ON p.id_produit = dl.id_produit
		WHERE dl.id_livraison = ?
		ORDER BY p.nom
	`, idLivraison)

	if err != nil {
		http.Error(w, "erreur lors de la lecture des produits", http.StatusInternalServerError)
		return
	}
	defer lignes.Close()

	pdf := fpdf.New("P", "mm", "A4", "")
	pdf.SetMargins(15, 15, 15)
	pdf.SetAutoPageBreak(true, 18)
	pdf.AddPage()

	// Cette fonction permet d'afficher correctement les caractères français.
	traduit := pdf.UnicodeTranslatorFromDescriptor("")

	// En-tête
	pdf.SetFillColor(34, 125, 52)
	pdf.SetTextColor(255, 255, 255)
	pdf.SetFont("Arial", "B", 20)
	pdf.CellFormat(
		180,
		14,
		traduit("NO MORE WASTE"),
		"",
		1,
		"C",
		true,
		0,
		"",
	)

	pdf.SetTextColor(30, 30, 30)
	pdf.Ln(4)

	pdf.SetFont("Arial", "B", 16)
	pdf.CellFormat(
		180,
		10,
		traduit("Récapitulatif de livraison"),
		"",
		1,
		"C",
		false,
		0,
		"",
	)

	pdf.SetFont("Arial", "", 10)
	pdf.CellFormat(
		180,
		7,
		traduit(fmt.Sprintf("Livraison n° %d", idLivraison)),
		"",
		1,
		"C",
		false,
		0,
		"",
	)

	pdf.Ln(5)

	// Informations sur la livraison
	pdf.SetFillColor(220, 235, 220)
	pdf.SetFont("Arial", "B", 12)
	pdf.CellFormat(
		180,
		9,
		traduit("Informations de la tournée"),
		"1",
		1,
		"L",
		true,
		0,
		"",
	)

	ajouterInformationPDF(pdf, traduit("Date"), dateLivraison)
	ajouterInformationPDF(
		pdf,
		traduit("Horaires"),
		valeurOuTiret(heureDepart)+" - "+valeurOuTiret(heureRetour),
	)
	ajouterInformationPDF(pdf, traduit("Statut"), statut)

	pdf.Ln(5)

	// Association bénéficiaire
	pdf.SetFillColor(220, 235, 220)
	pdf.SetFont("Arial", "B", 12)
	pdf.CellFormat(
		180,
		9,
		traduit("Association bénéficiaire"),
		"1",
		1,
		"L",
		true,
		0,
		"",
	)

	ajouterInformationPDF(pdf, traduit("Nom"), traduit(association))
	ajouterInformationPDF(
		pdf,
		traduit("Adresse"),
		traduit(construireAdresse(adresse, codePostal, ville)),
	)
	ajouterInformationPDF(pdf, traduit("Téléphone"), valeurOuTiret(telephone))
	ajouterInformationPDF(pdf, "Email", valeurOuTiret(email))

	pdf.Ln(5)

	// Véhicule
	pdf.SetFillColor(220, 235, 220)
	pdf.SetFont("Arial", "B", 12)
	pdf.CellFormat(
		180,
		9,
		traduit("Véhicule"),
		"1",
		1,
		"L",
		true,
		0,
		"",
	)

	ajouterInformationPDF(
		pdf,
		traduit("Véhicule"),
		traduit(marque+" "+modele),
	)
	ajouterInformationPDF(pdf, "Immatriculation", immatriculation)

	pdf.Ln(6)

	// Tableau des produits
	pdf.SetFont("Arial", "B", 12)
	pdf.CellFormat(
		180,
		9,
		traduit("Produits livrés"),
		"",
		1,
		"L",
		false,
		0,
		"",
	)

	pdf.SetFillColor(34, 125, 52)
	pdf.SetTextColor(255, 255, 255)
	pdf.SetFont("Arial", "B", 9)

	pdf.CellFormat(70, 8, traduit("Produit"), "1", 0, "C", true, 0, "")
	pdf.CellFormat(50, 8, "Code-barres", "1", 0, "C", true, 0, "")
	pdf.CellFormat(30, 8, traduit("Quantité"), "1", 0, "C", true, 0, "")
	pdf.CellFormat(30, 8, traduit("Unité"), "1", 1, "C", true, 0, "")

	pdf.SetTextColor(30, 30, 30)
	pdf.SetFont("Arial", "", 9)

	nombreProduits := 0

	for lignes.Next() {
		var (
			nomProduit string
			codeBarre  string
			unite      string
			quantite   float64
		)

		if err := lignes.Scan(
			&nomProduit,
			&codeBarre,
			&unite,
			&quantite,
		); err != nil {
			http.Error(w, "erreur lors de la lecture des produits", http.StatusInternalServerError)
			return
		}

		pdf.CellFormat(70, 8, traduit(nomProduit), "1", 0, "L", false, 0, "")
		pdf.CellFormat(50, 8, codeBarre, "1", 0, "C", false, 0, "")
		pdf.CellFormat(30, 8, fmt.Sprintf("%.2f", quantite), "1", 0, "C", false, 0, "")
		pdf.CellFormat(30, 8, unite, "1", 1, "C", false, 0, "")

		nombreProduits++
	}

	if err := lignes.Err(); err != nil {
		http.Error(w, "erreur lors de la lecture des produits", http.StatusInternalServerError)
		return
	}

	if nombreProduits == 0 {
		pdf.CellFormat(
			180,
			9,
			traduit("Aucun produit enregistré pour cette livraison"),
			"1",
			1,
			"C",
			false,
			0,
			"",
		)
	}

	// Commentaire
	if commentaire.Valid && commentaire.String != "" {
		pdf.Ln(6)
		pdf.SetFont("Arial", "B", 11)
		pdf.CellFormat(180, 7, "Commentaire :", "", 1, "L", false, 0, "")

		pdf.SetFont("Arial", "", 10)
		pdf.MultiCell(
			180,
			6,
			traduit(commentaire.String),
			"1",
			"L",
			false,
		)
	}

	pdf.Ln(10)
	pdf.SetTextColor(100, 100, 100)
	pdf.SetFont("Arial", "I", 8)
	pdf.CellFormat(
		180,
		6,
		traduit("Document généré par l'application NO MORE WASTE"),
		"",
		1,
		"C",
		false,
		0,
		"",
	)

	nomFichier := fmt.Sprintf(
		"livraison_%d.pdf",
		idLivraison,
	)

	w.Header().Set("Content-Type", "application/pdf")
	w.Header().Set(
		"Content-Disposition",
		"attachment; filename="+nomFichier,
	)

	if err := pdf.Output(w); err != nil {
		http.Error(
			w,
			"erreur pendant la génération du PDF",
			http.StatusInternalServerError,
		)
		return
	}
}

func ajouterInformationPDF(pdf *fpdf.Fpdf, titre string, valeur string) {
	pdf.SetFont("Arial", "B", 9)
	pdf.CellFormat(45, 7, titre, "1", 0, "L", false, 0, "")

	pdf.SetFont("Arial", "", 9)
	pdf.CellFormat(135, 7, valeur, "1", 1, "L", false, 0, "")
}

func valeurOuTiret(valeur sql.NullString) string {
	if valeur.Valid && valeur.String != "" {
		return valeur.String
	}

	return "-"
}

func construireAdresse(
	adresse sql.NullString,
	codePostal sql.NullString,
	ville sql.NullString,
) string {
	resultat := ""

	if adresse.Valid {
		resultat = adresse.String
	}

	if codePostal.Valid {
		if resultat != "" {
			resultat += ", "
		}
		resultat += codePostal.String
	}

	if ville.Valid {
		if resultat != "" {
			resultat += " "
		}
		resultat += ville.String
	}

	if resultat == "" {
		return "-"
	}

	return resultat
}
