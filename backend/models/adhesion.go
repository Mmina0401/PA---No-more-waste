package models

type Adhesion struct {
	ID            int     `json:"id"`
	IDUtilisateur int     `json:"id_utilisateur"`
	DateDebut     string  `json:"date_debut"`
	DateFin       string  `json:"date_fin"`
	Montant       float64 `json:"montant"`
	DatePaiement  string  `json:"date_paiement"`
	ModePaiement  string  `json:"mode_paiement"`
	Statut        string  `json:"statut"`
	RappelEnvoye  bool    `json:"rappel_envoye"`
}
