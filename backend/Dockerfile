# Étape 1 : construire le programme Go
FROM golang:1.25-alpine AS construction

WORKDIR /app

COPY go.mod go.sum ./
RUN go mod download

COPY . .
RUN go build -o serveur .

# Étape 2 : préparer un environnement minimal pour l'exécuter
FROM alpine:3.20

WORKDIR /app

COPY --from=construction /app/serveur .

EXPOSE 8080

CMD ["./serveur"]