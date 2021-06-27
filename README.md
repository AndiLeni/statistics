# Analytics Addon für REDAXO CMS

## Work in Progress - nicht für den produktiven Einsatz geeignet

----

## Features:

Dieses Addon stellt im REDAXO CMS eine Besucherzählung bereit.  

Dabei werden folgende Metriken erfasst und optisch dargestellt:  
- Tag des Besuches (Datum und Wochentag)
- Besuchszeit
- Browser
- Gerätetyp
- Betriebssystem
- Gerätemarke und Gerätemodell
- Bots (Crawler etc.)

Dieses Addon arbeitet **OHNE** Cookies und kann somit Datenschutzkonform eingesetzt werden.

Persönlichen Daten (z.B. die IP Adresse des Besuchers) werden nur gehasht gespeichert und können somit nicht ohne großen Aufwand dechiffriert werden.

Die IP Adresse wird genutzt, um ein wiederholtes Aufrufen von Seiten nicht in die Statistik einfließen zu lassen.


## Installation:

Das Repository herunterladen und im Ordner `redaxo > src > addons` entpacken.  
Danach den Ordner `redaxo_analytics-main` in `stats` umbenennen.  


## Einstellungen:
Es können folgende Einstellungen getroffen werden:
- Besuchsdauer, bestimmt innerhalb welches Zeitraumes ein Benutzer nur einmal pro Url erfasst werden soll
- Ignore-Liste für URLs, hier kann eine Reihe an Urls angegeben werden welche nicht in der Statistik erfasst werden sollen
- Ignore-Liste für IPs, hier kann eine Reihe an IP Adressen angegeben werden von denen Besuche nicht erfasst werden sollen


Preview:

### Startseite des Addons mit den wichtigsten Metriken:
![Preview](./preview/1.png "Preview")

### Seite für Details über eine URL:
![Preview](./preview/2.png "Preview")

### Statistiken über Medien Aufrufe:
![Preview](./preview/5.png "Preview")

### Einstellungen:
![Preview](./preview/3.png "Preview")

### Integration in das Addon https://github.com/yakamara/dashboard
![Preview](./preview/4.png "Preview")