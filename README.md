# Analytics Addon für REDAXO CMS


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
- Referrer
- Anzahl besuchter Seiten in einer Sitzung
- Besuchsdauer in einer Sitzung
- Ausstiegsseiten

Dieses Addon arbeitet **OHNE** clientseitige Cookies.

Die IP Adresse des Besuchers wird gehasht gespeichert.  
Allerdings wird diese zu Datenschutzzwecken nicht roh gespeichert, sondern mit dem User-Agent des Besuchers gehasht.  
`hash = sha1(ipAdresseClient + userAgentClient)`.  
Der User Agent wird nicht auf dem Server gespeichert. Um an die IP zu kommen, muss dieser Hash also aufwendig per Brute Force geknackt werden. 

Die IP Adresse des Besuchers wird genutzt, um ein wiederholtes Aufrufen von Seiten nicht in die Statistik einfließen zu lassen.

Um den Datenschutz zu erhöhen kann der mitgelieferte Cronjob eingesetzt werden (benötigt das Cronjob Addon von Redaxo).
Diesen am besten auf 00:05 täglich einstellen. 
Dadurch werden alte, nicht länger benötigte Hashes automatisch gelöscht um Datensparsamkeit zu gewährleisten (löscht alle Hashes die älter sind als der aktuelle Tag).


### Medien-Tracking:
Um Aufrufe von Medien (Bilder, Dokumente, etc.) zu loggen.
Dieses kann auf zwei Arten verwendet werden:
1. Alle Medien tracken  
   Dabei werden alle Aufrufe zu Medien in der Statistik erfasst.
2. Medien mittles Media-Manager-Effekt tracken  
   Um gezielt Medien erfassen zu können, kann ein Media-Manager-Effekt genutzt werden.
   Dieser wird einfach als weiterer Effekt hinzugefügt und erfasst dann nur die Medien die tatsächlich für die Statistik interessant sind.


### Events:
Erlaubt es, einen API Request zu nutzen um im Frontend oder Backend ein bestimmtes Ereigniss zu tracken (beispielsweise das Anklicken eines Links oder das Absenden eines Formulars).



## Installation:

Das Repository herunterladen und im Ordner `redaxo > src > addons` entpacken.  
Danach den Ordner in `statistics` umbenennen.  
oder über den Installer in Redaxo


## Beispiele:

### Frontend Counter:
Falls man im Frontend einen Besucher-Counter einfügen möchte klappt das mittles der folgenden Modul-Ausgabe:
```php
<?php

use AndiLeni\Statistics\VisitorCounter;

?>

<p>Besucher: <code><?php echo VisitorCounter::getText() ?></code><p>
```
Der Ausgegebene Text kann dann nach Belieben gestaltet werden.

### Backend Event loggen:
```php
<?php

use AndiLeni\Statistics\Event;

Event::log("my_event_name");

```


### Download-Counter:
1. Im Media Manager einen neuen Medientyp anlegen mit dem Namen "log"
2. Zu diesem den Effekt "Datei in Statistik loggen" hinzufügen
3. Ein Modul anlegen  
   Eingabe:
   ```
    <label>Downloads:</label>
    REX_MEDIALIST[id="1" widget="1"]
   ```
   Ausgabe:
   ```
    <div class="container">
        <h2>Downloads:</h2>
        <table class="table">
    
        <tr>
            <th>Name</th>
            <th>Link</th>
        </tr>

        <?php
        foreach (explode(',', "REX_MEDIALIST[1]") as $img)
        {
            echo '<tr>';
                echo '<td>'. $img .'</td>';
                echo '<td><a href="'.rex_media_manager::getUrl('log',$img).'">Download</a></td>';
            echo '</tr>';
        }
        ?>
            
        </table>
    </div>
   ```
4. In den Einstellungen das tracken aktivieren.
5. Das Beipiel erzeugt dann eine solche Tabelle:  
   ![Beispiel1](https://raw.githubusercontent.com/andileni/statistics/main/preview/6.png "Beispiel1")
   Klickt der Besucher auf den Link "Download" wird dieser Aufruf in der Statistik gespeichert.


### Kampagnen-Tracking:
Ziel: Das Klicken eines Links im Frontend soll erfasst werden.

1. Ein Modul anlegen
   Eingabe:
   ```html
    <label>Kampagnen-Name:</label>
    <input type="text" name="REX_INPUT_VALUE[1]" value="REX_VALUE[1]">
   ```

   Ausgabe:
   ```html
    <a class="btn btn-primary" onclick="myFunction()" href="http://example.com/">Link</a>

    <script>
    function myFunction() {
        fetch('/?rex-api-call=stats&name=REX_VALUE[1]');
    }
    </script>
   ```


## Preview:

### Startseite des Addons mit den wichtigsten Metriken:
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/1.png "Preview")

### Seite mit Details über eine URL:
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/2.png "Preview")

### Statistiken über Medien Aufrufe:
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/5.png "Preview")
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/7.png "Preview")

### Verweise:
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/8.png "Preview")

### API / Kampagnen:
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/9.png "Preview")


### Integration in das Addon https://github.com/yakamara/dashboard
![Preview](https://raw.githubusercontent.com/andileni/statistics/main/preview/4.png "Preview")
