package config

import (
	"database/sql"
	"fmt"
	"os"

	_ "github.com/go-sql-driver/mysql"
)

var DB *sql.DB

func ConnectDatabase() {

	var err error

	adresseMySQL := os.Getenv("ADRESSE_MYSQL")
	if adresseMySQL == "" {
		adresseMySQL = "127.0.0.1:3306"
	}

	dsn := "root:root@tcp(" + adresseMySQL + ")/no_more_waste?parseTime=true"

	DB, err = sql.Open("mysql", dsn)

	if err != nil {
		panic(err)
	}

	err = DB.Ping()

	if err != nil {
		panic(err)
	}

	fmt.Println(" Connexion MySQL réussie")
}
