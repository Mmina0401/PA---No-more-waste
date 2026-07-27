package config

import (
	"database/sql"
	"fmt"

	_ "github.com/go-sql-driver/mysql"
)

var DB *sql.DB

func ConnectDatabase() {

	var err error

	dsn := "root:root@tcp(127.0.0.1:3306)/no_more_waste?parseTime=true"

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
