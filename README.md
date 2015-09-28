# Minimale Datenbankmigrationen für Git
Kleines Tool, welches Datenbankmigrationen/Datenbankversionierung vereinfachen sollte. Nur vorwärts migrationen möglich.

Migrationen sollten im Projektverzeichnis unter `sql/migrations` zu finden sein. Migrationen sollten durchnummeriert sein (`1.sql`, `2.sql` etc.).

Die Datenbank konfiguration wird mittels eines Skripts `sql/env` übergeben. Dieses Skript sollte eine Textausgabe wie folgt erzeugen:

```sh
MYSQL_DB=dbname
MYSQL_HOST=hostname
MYSQL_USER=username
MYSQL_PASS=password
```

## Git hook
Kann/sollte derzeit nur als post-merge hook verwendet werden, da nur vorwärts migriert werden kann.
