# Changelog 

## [2.0.0-beta.7] - 03.11.2021

### Added

### Changed
- fix js error for datefilter quickselect
- datefilter is applied instantly
- fix more js errors, datatables was throwing an error when table was empty

### Removed

### Vendor Updates

### Notes


## [2.0.0-beta.6] - 01.11.2021

### Added

### Changed
- fix datefilter quickselect, month is now calculated correctly

### Removed

### Vendor Updates

### Notes

## [2.0.0-beta.5] - 31.10.2021

### Added

### Changed
- add quickselects to datefilter fragment

### Removed
- `stats_pagedetails.php\get_browser()`
- `stats_pagedetails.php\get_browsertype()`
- `stats_pagedetails.php\get_os()`

### Vendor Updates

### Notes


## [2.0.0-beta.4] - 21.10.2021

### Added

### Changed
- escape data before inserted in db / #62 
- fix data deletion / #63
- fix dashboard integration

### Removed

### Vendor Updates

### Notes



## [2.0.0-beta.3] - 14.10.2021

### Added

### Changed
- escape data during migration

### Removed

### Vendor Updates

### Notes


## [2.0.0-beta.2] - 14.10.2021

### Added

### Changed
- remove unecessary table columns

### Removed

### Vendor Updates

### Notes


## [2.0.0-beta.1] - 14.10.2021

### Added
- table ``pagestats_data``
- table ``pagestats_visits_per_day``
- table ``pagestats_visits_per_url``

### Changed
- visits are now saved directly in a more separated way to achieve better performance on pages with a hight number of visits per day
- browserdata is not any more separated by date

### Removed
- table ``pagestats_dump``

### Vendor Updates

### Notes
Auf Website mit vielen Besuchen pro Tag kam es im Backend zu extremen Ladezeiten um die Daten aufzubereiten.
Um dem Vorzubeugen werden Besuche nun in passendere Tabellenstrukturen gespeichert um eine Auswertung zu beschleunigen.

Beim Upgrade werden die Daten aus der Tabelle pagestats_dump ausgewertet und auf die neuen Tabellen verteilt.
> **Hinweis:** Dieser Migrationsvorgang kann je nach Tabellengröße länger dauern, bitte sicherstellen, dass die PHP Laufzeit ausreichend ist.


## [1.0.0-rc.3] - 06.10.2021

### Added
- add "id" column as primary key to all tables to increase performance

### Changed

### Removed

### Vendor Updates

### Notes


## [1.0.0-rc.2] - 04.10.2021

### Added
- pagedetails panel headings

### Changed
- fix for paginations / #57

### Removed

### Vendor Updates

### Notes


## [1.0.0-rc.1] - 30.09.2021

### Added
- permissions

### Changed
- change some database fields to "text" type
- change stats layout
- change table search to case-insensitive
- fix date filtering
- adjust setting names

### Removed
- ``plugins\api\lib\stats_campaign_details.php\get_page_total()``
- ``plugins\media\lib\stats_media_details.php\get_page_total()``

### Vendor Updates

### Notes


## [dev-0.0.3] - 16.09.2021

### Added
- add setting to optionally ignore url parameters
- ignore `.css.map` and `.js.map` files for logging

### Changed

### Removed

### Vendor Updates

### Notes


## [dev-0.0.2] - 16.09.2021

### Breaking changes

### Added

### Changed
- fix integration in dashboard addon
- fix search input overflow / #50
- remove dump() on backend page

### Removed

### Vendor Updates

### Notes
