package handlers

import (
	"net/http"

	"github.com/go-pdf/fpdf"
)

func GenererPDFLivraison(w http.ResponseWriter, r *http.Request) {

	pdf := fpdf.New("P", "mm", "A4", "")

	pdf.AddPage()

	pdf.SetFont("Arial", "B", 18)
	pdf.Cell(190, 10, "No More Waste")

	pdf.Ln(15)

	pdf.SetFont("Arial", "", 14)
	pdf.Cell(190, 10, "Tournee de distribution")

	pdf.Ln(20)

	pdf.SetFont("Arial", "", 12)
	pdf.Cell(190, 10, "PDF de test")

	w.Header().Set("Content-Type", "application/pdf")
	w.Header().Set("Content-Disposition", "attachment; filename=tournee.pdf")

	err := pdf.Output(w)
	if err != nil {
		http.Error(w, err.Error(), http.StatusInternalServerError)
		return
	}
}
