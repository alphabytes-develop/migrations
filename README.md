# Minimale Datenbankmigrationen für Git
Kleines Tool, welches Datenbankmigrationen/Datenbankversionierung vereinfachen sollte.

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
    'table' => 'migrations',
    'migrations' => 'migrations',
];
```

`table` spezifiziert die Tabelle in welche die Migrationsinfos geschrieben werden.
`migrations` gibt den Ordner an, in welchem die Migrationsdateien liegen (relativ zur config Datei).

## Migrationsverzeichnis
Im durch`migrations` definierten Migrationsverzeichnis sollte pro Migration ein Unterordner angelegt werden. Diese Ordner sollten durchnummeriert sein (beginnend bei 1).
In jedem Verzeichnis ist eine `up.sql` und eine `down.sql` Datei abzulegen, welche die Migration anwenden bzw. rückgängig machen.

## git
Das Tool speichert den git hash der jeweils letzten Änderung einer jeden Migration in die Datenbank.
Dies ermöglicht es auch bei Mergekonflikten oder Zweigwechseln immer einen sauberen Stand zu halten.

## CLI
Um die Migrationen anzuwenden sollte die `bin.php` Datei ausgeführt werden.
Das aktuelle Arbeitsverzeichnis sollte dem Projektordner entsprechen.

Folgende Operationen sind möglich:

### test <db>
Wendet alle Migrationen (beginnend mit 1) auf die angegebene Datenbank an und macht sie danach dückgängig.
Nützlich um korrektheit von Migrationen zu überprüfen.

### apply <dryrun>
Wendet alle Migrationen auf die in der Konfiguration eingestellte Datenbank an.
Ist ein weiterer Parameter angegeben, so wird nur ausgegeben, was das Tool tun würde ohne in die Datenbank zu schreiben.

### status
Zeigt die zuletzt ausgeführte Migration.

### rollback <n>
Macht die letzten n Migrationen rückgängig. Standardmäßig eine.