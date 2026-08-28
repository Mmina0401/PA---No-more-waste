FROM golang:1.25-alpine AS construction

WORKDIR /app

COPY go.mod go.sum ./
RUN go mod download

COPY . .
RUN go build -o serveur .

FROM alpine:3.20

WORKDIR /app

COPY --from=construction /app/serveur .

EXPOSE 8080

CMD ["./serveur"]