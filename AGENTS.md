# Statistics Addon - REDAXO

Ein REDAXO-Addon zur serverseitigen Erfassung und Auswertung von Seitenaufrufen, Besuchern, Medien-Downloads, Referrern, Events, Bots und Session-Metriken. Die Backend-Auswertung basiert auf ECharts und DataTables.

## Tech Stack

- Sprache: PHP
- CMS: REDAXO 5
- Frontend/Backend-Visualisierung: ECharts, DataTables, jQuery, Bootstrap-Collapse im REDAXO-Backend
- Datenbank: MySQL/MariaDB
- Namespace: `AndiLeni\Statistics`

## Projektstruktur

```text
statistics/
├── boot.php                          # Asset-Registrierung, Hooks, Tracking-Aktivierung
├── install.php                       # Datenbankschema und manuelle Zusatzindizes
├── update.php                        # Update-Handler
├── uninstall.php                     # Cleanup
├── README.md                         # Nutzer- und Architektur-Dokumentation
├── AGENTS.md                         # Projektwissen für Agenten
├── assets/
│   ├── statistics.js                 # DateFilter, DataTables, Lazy Loading
│   ├── statistics.css                # Backend-Styles
│   └── vendor/                       # ECharts, DataTables, Themes
├── fragments/
│   ├── filter.php                    # Datumsfilter
│   ├── overview.php                  # Kennzahlen oben auf stats
│   ├── main_chart.php                # Tages-/Monats-/Jahrescharts
│   ├── data_vertical.php             # Standardlayout für Chart + Tabelle
│   └── collapse.php                  # Einklappbare Tabellen, optional lazy
├── lib/
│   ├── ChartData.php                 # Hauptchart-Aggregationen
│   ├── StatsChartConfig.php          # Renderer/Builder für zentrale Chart-Optionen per JSON-Script
│   ├── StatsDashboard.php            # Komposition der stats-Startseite (Fragmente + Page-Config)
│   ├── StatsMainChartSection.php     # Renderer für den Hauptchart-Bereich der stats-Seite
│   ├── StatsLazySection.php          # Renderer für die unteren Lazy-Placeholder der stats-Seite
│   ├── StatsSubpageRenderer.php      # Gemeinsame Renderer für Datumsfilter und Sections auf Unterseiten
│   ├── ListData.php                  # Tabellen unter den Hauptcharts
│   ├── Summary.php                   # Overview-Kennzahlen
│   ├── Pages.php                     # Seiten-Auswertungen
│   ├── PageDetails.php               # URL-Details
│   ├── RefererDetails.php            # Referrer-Details
│   ├── StatsLazyBlockRenderer.php    # Renderer für lazy geladene Blöcke
│   ├── rex_api_statistics_lazy_block.php # REDAXO-API für Lazy Loading
│   ├── api/                          # Event-API und Detailklassen
│   ├── media/                        # Medien-Detailklassen
│   └── data/                         # Browser, OS, Country, Sessionstats etc.
└── pages/
    ├── stats.php                     # Hauptdashboard
    ├── pages.php                     # URL-Übersicht
    ├── referer.php                   # Referrer-Übersicht
    ├── media.php                     # Medien-Übersicht
    └── events.php                    # Event-Übersicht
```

## Coding Conventions

- Bestehenden Stil im Addon beibehalten, auch wenn er historisch nicht überall vollständig vereinheitlicht ist.
- Kleine, gezielte Änderungen bevorzugen. Keine großflächigen Umbauten ohne klaren Performance- oder Wartungsvorteil.
- Bei Backend-Ausgabe HTML möglichst direkt und einfach halten. Viele Bereiche rendern Tabellen bewusst manuell statt über `rex_list`.
- Änderungen an großen Statistikseiten zuerst auf gemeinsame Datenquellen und doppelte SQL-Abfragen prüfen.

## Wichtige Architekturregeln

### Performance zuerst an der Datenquelle lösen

Wenn eine Seite langsam ist, zuerst diese Punkte prüfen:

1. Fehlen passende Indizes?
2. Wird dieselbe Aggregation mehrfach abgefragt?
3. Wird ein Block bereits gerendert, obwohl er initial nicht sichtbar ist?
4. Wird `rex_list` für große Aggregationen verwendet, obwohl eine direkte Tabelle günstiger wäre?

### Prefix-Indizes für lange Spalten

- URL- und Referrer-Spalten sind lang genug, dass normale Vollindizes unter `utf8mb4` scheitern können.
- Diese Indizes werden in `install.php` per SQL und mit Prefix-Länge angelegt.
- Vor Änderungen an diesen Indizes immer die MySQL-/MariaDB-Key-Length-Grenzen mitdenken.

### Startseite `pages/stats.php`

Die Statistik-Startseite ist absichtlich in synchron und lazy getrennt:

- synchron: Filter, Overview, Hauptcharts, Heatmap
- lazy beim Tab-Wechsel: Monats- und Jahreschart
- lazy bei Sichtbarkeit: Geräteblock, erweiterte Session-/Länder-Statistik, Bot-Tabelle
- lazy bei Benutzeraktion: Tabellen unter den Hauptcharts erst beim Aufklappen

Neue große Sektionen auf dieser Seite sollten standardmäßig nicht direkt synchron gerendert werden.

Die Seitenkomposition selbst läuft über `StatsDashboard.php`. `pages/stats.php` sollte möglichst nur noch Daten beschaffen und die Helfermethoden für Filter, Overview, Hauptchart-Bereich, Lazy-Placeholder und JSON-Konfiguration aufrufen.

Der eigentliche Bereich für Tages-, Monats- und Jahrescharts ist separat in `StatsMainChartSection.php` gekapselt. Änderungen an Tabs, Collapse-Blöcken oder Chart-Containern sollten bevorzugt dort erfolgen statt in `StatsDashboard.php`.

Die unteren Lazy-Container für Geräte-, erweiterte Session-/Länder-Statistik und Bots sind separat in `StatsLazySection.php` gekapselt. `StatsDashboard.php` soll diese nur noch orchestrieren, nicht mehr selbst zusammenbauen.

### Lazy-Loading-Architektur

- Serverseitige Blockausgabe über `StatsLazyBlockRenderer`
- Transport über `rex_api_statistics_lazy_block`
- Frontend-Trigger in `assets/statistics.js`
- Sichtbarkeits-Lazy-Loading via `IntersectionObserver`
- Collapse-Lazy-Loading via `show.bs.collapse`

Wenn ein neuer Block lazy werden soll, die Logik nicht direkt in `pages/stats.php` einbauen, sondern über Renderer + API + JS führen.

### Zentrale Chart-Initialisierung

- Seitenübergreifende Chart-Optionen sollten möglichst in PHP als JSON-Script ausgeliefert und in `assets/statistics.js` zentral initialisiert werden.
- `StatsChartConfig.php` baut dafür wiederverwendbare ECharts-Optionen, statt ähnliche Inline-Skripte pro Seite zu duplizieren.
- Referer-, Medien- und Event-Seiten sollen keine eigene DataTable- oder Chart-Initialisierung mehr einbetten, wenn die zentrale Logik ausreicht.
- Die Pages-Seite soll ihren Domain-Filter ebenfalls nicht mehr selbst mit eigener Tabelleninitialisierung bootstrappen, sondern an die bereits zentral initialisierte DataTable andocken.

### Gemeinsame Unterseiten-Bausteine

- Filter- und Standard-Section-Rendering für `pages.php`, `referer.php`, `media.php` und `events.php` sollte über `StatsSubpageRenderer.php` laufen.
- Dadurch bleiben die Seiten auf Datenbeschaffung und Seitenspezifika beschränkt, statt REDAXO-Section-Boilerplate zu duplizieren.

### Gemeinsame Datenbasis für Chart und Tabelle

In Detail- und Statistikklassen ist das bevorzugte Muster:

- eine Abfrage laden
- Daten im Objekt cachen
- daraus sowohl Chartdaten als auch Tabellenausgabe ableiten

Ein separater SQL-Read nur für die zweite Darstellung ist hier meistens ein Bug oder mindestens unnötige Last.

## Bekannte Optimierungsmuster im Projekt

- `ChartData.php` nutzt Bulk-Aggregation statt N+1 pro Zeitraum
- `Summary.php` bündelt Summenabfragen in wenige konditionale Queries
- Detailklassen wie `PageDetails`, `RefererDetails`, `MediaDetails`, `EventDetails` verwenden wiederverwendete Datenzeilen
- `ListData.php` rendert Tabellen direkt und kann Inhalte mittlerweile auch lazy ausliefern

## Testing / Validierung

Es gibt keine automatisierte Test-Suite. Nach Änderungen mindestens prüfen:

1. `page=statistics/stats` ohne Filter
2. `page=statistics/stats` mit Datumsfilter
3. Scroll-basiertes Nachladen der unteren Blöcke
4. Aufklappen der Tabellen unter Tages-, Monats- und Jahreschart
5. Seiten-, Referrer-, Medien- und Event-Detailansichten
6. Installation/Update bei vorhandenen Tabellen

## AGENTS.md Maintenance

Wenn neue Erkenntnisse zu Architektur, Lazy-Loading, Datenbank-Hotspots, Indexanforderungen oder typischen Regressionen entstehen, diese Datei aktualisieren.
