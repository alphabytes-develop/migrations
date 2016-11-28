# Minimale Datenbankmigrationen für Git
Ein kleines Tool, welches Datenbankmigrationen/Datenbankversionierung vereinfachen sollte.

## Installation
```bash
composer install alphabytes/migrations
```

## config.php
Zunächst sollte eine `config.php` Datei im Projektverzeichnis unter `migrations/config.php` angelegt werden.
Folgende Daten sollten zurückgegeben werden:

```php
<?php
return [
    'host' => '',
    'database' => '',
    'user' => '',
    'password' => '',
    'table' => 'migrations'
];
```

`table` spezifiziert die Tabelle in welche die Migrationsinfos geschrieben werden.

## Migrationsverzeichnis
Im selben Migrationsverzeichnis sollte pro Migration ein Unterordner angelegt werden. Diese Ordner sollten durchnummeriert sein (beginnend bei 1).
In jedem Verzeichnis ist eine `up.sql` und eine `down.sql` Datei abzulegen, welche die Migration anwenden bzw. rückgängig machen.

## Dateihashes
Das Tool speichert den Dateihash der jeweils letzten Änderung einer jeden Migration in die Datenbank.
Dies ermöglicht es auch bei Mergekonflikten oder Zweigwechseln immer einen sauberen Stand zu halten.

## CLI
Um die Migrationen anzuwenden sollte das Tool via `./vendor/bin/migrations` ausgeführt werden.
Das aktuelle Arbeitsverzeichnis sollte dem Projektordner entsprechen.

Folgende Operationen sind möglich:

### test <db>
Wendet alle Migrationen (beginnend mit 1) auf die angegebene Datenbank an und macht sie danach rückgängig.
Nützlich um Korrektheit von Migrationen zu überprüfen.

### apply <dryrun>
Wendet alle Migrationen auf die in der Konfiguration eingestellte Datenbank an.
Ist ein weiterer Parameter angegeben, so wird nur ausgegeben, was das Tool tun würde ohne in die Datenbank zu schreiben.

### status
Zeigt die zuletzt ausgeführte Migration.

### rollback <n>
Macht die letzten n Migrationen rückgängig. Standardmäßig eine.
