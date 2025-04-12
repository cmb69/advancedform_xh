# Advancedform\_XH

Advancedform\_XH ermöglicht es Ihnen Ihre eigenen E-Mail-Formulare zur
Integration in CMSimple\_XH zu erstellen. Die Möglichkeiten reichen von
angepassten Kontaktformularen bis zu komplexen Bestell- oder
Buchungsformularen. Selbst komplexe Formulare können im Formular-Editor
konstruiert werden, so dass Sie kein HTML, CSS oder PHP schreiben
müssen, wenn Ihnen die grundlegende Formular-Funktionalität genügt.
Weitergehende Anpassungen sind durch das Template- bzw. Hook-System
möglich.

- [Voraussetzungen](#voraussetzungen)
- [Download](#download)
- [Installation](#installation)
- [Einstellungen](#einstellungen)
- [Verwendung](#verwendung)
    - [Formular-Verwaltung](#formular-verwaltung)
    - [Formular-Editor](#formular-editor)
    - [Verwenden des Formulars](#verwenden-des-formulars)
    - [Ersetzen des eingebauten Kontakt-Formulars](#ersetzen-des-eingebauten-kontakt-formulars)
    - [Vorlagen-System](#vorlagen-system)
    - [Hooks](#hooks)
    - [Demo-Formulare](#demo-formulare)
- [Einschränkungen](#einschränkungen)
- [Fehlerbehebung](#fehlerbehebung)
- [Lizenz](#lizenz)
- [Danksagung](#danksagung)

## Voraussetzungen

Advancedform_XH ist ein Plugin für [CMSimple_XH](https://cmsimple-xh.org/de/).
Es benötigt CMSimple_XH ≥ 1.7.0 und PHP ≥ 7.1.0 mit den ctype, filter und hash Extensions.
Advancedform_XH benötigt weiterhin [Plib_XH](https://github.com/cmb69/plib_xh) ≥ 1.7;
ist dieses noch nicht installiert (siehe `Einstellungen` → `Info`),
laden Sie das [aktuelle Release](https://github.com/cmb69/plib_xh/releases/latest)
herunter, und installieren Sie es.

## Download

Das [aktuelle Release](https://github.com/cmb69/advancedform_xh/releases/latest)
kann von Github herunter geladen werden.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple\_XH-Plugins auch.

1. Sichern Sie die Daten auf Ihrem Server.
1. Entpacken Sie die ZIP-Datei auf Ihrem Computer.
1. Laden Sie das gesamte Verzeichnis `advancedform/` auf Ihren Server in
   das `plugins/` Verzeichnis von CMSimple\_XH hoch.
1. Vergeben Sie falls nötig Schreibrechte für die Unterverzeichnisse
   `config/`, `css/`, `languages/` und den Daten-Ordner von Advancedform\_XH.
1. Schützen Sie den Daten-Ordner von Advancedform\_XH vor direktem
   Zugriff auf eine Weise, die Ihr Webserver unterstützt.
   `.htaccess`-Dateien für Apache Server sind bereits im voreingestellten
   Daten-Ordner enthalten. Beachten Sie, dass die Unterordner `css/` und
   `js/` öffentlichen Zugriff erlauben müssen.
1. Navigieren Sie zu `Plugins` → `Advancedform` → `Konfiguration`
   und Speichern Sie die Konfiguration.
1. Navigieren Sie zu `Plugins` → `Advancedform` im Administrationsbereich,
   und prüfen Sie, ob alle Vorraussetzungen erfüllt sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple\_XH-Plugins auch im Administrationsbereich der Website.
Wählen Sie `Plugins` → `Advancedform`.

Sie können die Original-Einstellungen von Advancedform\_XH
unter `Konfiguration` ändern.
Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt.
Die Einstellung `php_extension` bietet zusätzliche Sicherheit
in Verbindung mit dem Vorlagen-System und den Hooks, wenn sie aktiviert wird.
Allerdings funktionieren dann einige Demo-Formulare nicht.

Die Lokalisierung wird unter `Sprache` vorgenommen.
Sie können die Zeichenketten in Ihre eigene Sprache übersetzen,
falls keine entsprechende Sprachdatei zur Verfügung steht,
oder sie entsprechend Ihren Anforderungen anpassen.

Das Aussehen von Advancedform\_XH kann unter `Stylesheet` angepasst werden.
Der obere Teil enthält das Styling der Formulare,
die im Front-End angezeigt werden.
Das Formular wird als Tabelle mit zwei Spalten angezeigt.
Die linke Spalte enthält die Beschriftungen,
die rechte Spalte die Felder.
Sie können einfach die Stile der Klassen `div.advfrm-mailform td.label`
und `div.advfrm-mailform td.field` anpassen.
Das Aussehen einzelner Formulare kann durch Auswählen von
`form[name=FORMULAR_NAME]`,
das Aussehen einzelner Felder durch Auswählen von
`#advfrm-FORMULAR_NAME-FELD_NAME` angepasst werden.
Wenn Sie ein einspaltiges Layout bevorzugen, müssen Sie auf das
[Vorlagen-System](#vorlagen-system) zurückgreifen.

Der untere Teil des Stylesheets enthält das Styling der
Formular-Verwaltung und des Formular-Editors.
Wollen Sie, dass das Aussehen der Eigenschafts-Dialoge zu Ihrem Template passt,
sollten Sie in Erwägung ziehen, jQueryUI-Theming zu Ihrem Template hinzuzufügen.
Wie das geht, wird im
[CMSimple\_XH Forum](https://www.cmsimpleforum.com/viewtopic.php?f=29&t=3435&start=2)
erklärt.

Nur der erste Teil des Stylesheets oberhalb der Zeile

    /* END OF MAIL CSS */

wird in den versendeten E-Mails eingebunden.
Definieren Sie also alle Stile, die für die E-Mail erforderlich sind,
im oberen Teil des Stylesheets.

## Verwendung

### Formular-Verwaltung

Im Administrationsbereich finden Sie unter `E-Mail-Formulare`
die Übersicht aller definierten E-Mail-Formulare.
Sie können neue hinzufügen und importieren
sowie bestehende bearbeiten, löschen, kopieren und exportieren.
Rechts daneben befindet sich der Skript-Code,
der nötig ist, um das jeweilige Formular auf einer Seite anzuzeigen.
Kopieren Sie einfach den Code und fügen Sie ihn auf der gewünschten
Seite ein.

### Formular-Editor

Im Formular-Editor können Sie Ihre Formulare erstellen.
Die Details werden in den folgenden Abschnitten erklärt.

#### Allgemeine Formular-Eigenschaften

Im oberen Teil des Formular-Editors können Sie die allgemeinen
Formular-Eigenschaften bearbeiten.

- `Name`:
  Der Name eines Formulars darf nur alphanumerische Zeichen und Unterstriche enthalten.
  Er muss eindeutig für alle definierten Formulare sein.
  Er wird verwendet, um das Formular zu identifizieren.
- `Titel`:
  Der Titel des Formulars wird nur im Betreff der E-Mail verwendet.
- `An (Name)`:
  Der Name des Empfängers der E-Mail.
- `An (E-Mail)`:
  Die Adresse des Empfängers der E-Mail.
- `CC`:
  Die durch Strichpunkte getrennten Adressen der CC Empfänger der E-Mail.
- `BCC`:
  Die durch Strichpunkte getrennten Adressen der BCC Empfänger der E-Mail.
- `CAPTCHA`:
  Ob ein CAPTCHA im Formular integriert werden soll.
- `Daten speichern`:
  Ob die abgeschickten Daten zusätzlich in einer CSV-Datei gespeichert
  werden sollen.
- `Dank-Seite`:
  Wenn leer, wird nach dem E-Mail-Versand die gesendete Information angezeigt.
  Wenn gesetzt und eine Absender E-Mail-Adresse eingegeben wurde,
  werden Besucher nach dem E-Mail-Versand auf diese Seite weiter geleitet,
  und eine Bestätigungs-E-Mail mit den gesendeten Information wird ihnen zugeschickt.

#### Formular-Felder

Im unteren Teil des Formular-Editors können Sie die Felder des Formulars bearbeiten.
Verwenden sie die Tool-Icons um Felder hinzuzufügen, zu löschen oder zu verschieben.

- `Name`:
  Der Name des Felds darf nur alphanumerische Zeichen und Unterstriche enthalten.
  Er muss für alle definierten Felder des aktuellen Formulars eindeutig sein.
  Er wird verwendet, um das Feld zu identifizieren.
- `Beschriftung`:
  Die Beschriftung die neben dem Feld angezeigt werden soll.
- `Typ`:
  Der Feld-Typ.
  Rechts vom Auswahlfeld befindet sich das Eigenschaften-Tool-Icon.
  Klicken Sie es an, um den Dialog zum Bearbeiten
  der Eigenschaften des ausgewählten Feld-Typs zu öffnen.
- `Erf.`:
  Ob das Feld erforderlich ist, d.h. vom Besucher ausgefüllt werden muss.

#### Feld-Typen

- `Text`:
  Ein allgemeines Text-Feld.
- `Absender (Name)`:
  Ein Feld zur Eingabe des Namens des Absenders.
  Diese Information wird im E-Mail-Header eingefügt.
  Höchstens ein Feld des Typs `Absender (Name)` darf pro Formular verwendet werden.
- `Absender (E-Mail)`:
  Ein Feld zur Eingabe der E-Mail-Adresse des Absenders, welche validiert wird.
  Diese Information wird als Antwort-An-Header der Mail verwendet
  und als An-Header der Bestätigungs-E-Mail.
  Höchstens ein Feld des Typs `Absender (E-Mail)` darf pro Formular verwendet werden.
- `E-mail`:
  Ein Feld zur Eingabe einer allgemeinen E-Mail-Adresse, die validiert wird.
- `Datum`:
  Ein Feld zur Eingabe eines Datums, das validiert wird.
  In zeitgemäßen Browsern steht ein Datepicker zur Verfügung.
- `Zahl`:
  Ein Feld zur Eingabe einer nicht negativen Ganzzahl, die validiert wird.
- `Textbereich`:
  Ein Feld zur Eingabe von mehrzeiligen Texten.
- `Radio-Button`:
  Ein Feld zur Auswahl einer von mehreren Optionen.
- `Checkbox`:
  Ein Feld zur Auswahl einer beliebigen Anzahl mehrerer Optionen.
- `Auswahlliste`:
  Ein Feld zur Auswahl einer von mehreren Optionen.
- `Mehrfach-Auswahlliste`:
  Ein Feld zur Auswahl einer beliebigen Anzahl mehrerer Optionen.
- `Kennwort`:
  Ein Feld zur Eingabe eines Kennworts.
- `Datei`:
  Ein Feld, das es dem Besucher ermöglicht eine Datei als E-Mail-Anhang zur versenden.
  Der Anhang wird in der Bestätigungs-E-Mail nicht zum Besucher zurück geschickt.
- `Versteckt`:
  Ein verstecktes Feld.
  Versteckte Felder werden dem Besucher niemals angezeigt.
  Sie können in Verbindung mit dem Vorlagen-System und den Hooks nützlich sein.
- `Ausgabe`:
  Ein Feld um beliebiges HTML auszugeben.
- `Benutzerdefiniert`:
  Ein Feld, das gegen einen anzugebenden regulären Ausdruck validiert wird.

#### Feld-Eigenschaften

Die Feld-Eigenschaften werden in einem Dialog bearbeitet,
der durch Anklicken des Eigenschaften-Icons geöffnet werden kann.
Die unterschiedlichen Feld-Typen haben verschiedene Eigenschaften.

- `Größe`:
  Für Textfelder im weiteren Sinn die Breite des Felds gemessen in Zeichen.
  Für Auswahllisten die Höhe der Liste. `1` erzeugt eine Dropdown-Auswahlliste.
- `Ausrichtung`:
  Nur für Radio-Buttons und Checkboxen:
  ob diese horizontal oder vertikal dargestellt werden sollen.
- `Max. Länge`:
  Die Höchstanzahl der Zeichen, die eingegeben werden können.
  Für Dateifelder wird damit die maximale Größe der Datei in Bytes angegeben.
- `Spalten`:
  Die Breite des Textbereichs in Zeichen.
- `Zeilen`:
  Die Höhe des Textbereichs in Zeichen.
- `Vorbelegung`:
  Die Vorbelegung des Felds.
  Für `date` Felder ist dies eines der unterstüzten
  [Datumsformate](https://www.php.net/manual/de/datetime.formats.date.php)
  oder [relativen Formate](https://www.php.net/manual/de/datetime.formats.relative.php).
  Typische Anwendungsfälle sind ein festes Datum wie "2021-10-31",
  oder ein relatives Datum wie "today" (heute), "tomorrow" (morgen) oder "next saturday" (nächster Samstag).
- `Wert`:
  Das HTML für Ausgabe-Felder.
- `Dateitypen`:
  Nur für Datei-Felder:
  eine durch Komma getrennte Liste erlaubter Dateierweiterungen,
  z.B. `jpeg,jpg,png,gif,bmp` für Bilder.
- `Beschränkung`:
  Nur für benutzerdefinierte Felder:
  der reguläre Ausdruck gegen den die Eingabe geprüft werden soll.
- `Fehlermeldung`:
  Nur für benutzerdefinierte Felder:
  die Fehlermeldung, die angezeigt werden soll,
  wenn die Eingabe nicht zum regulären Ausdruck passt.
  Verwenden Sie `%s` um die Beschriftung des Felds in der Meldung einzufügen.

Radio-Buttons, Checkboxen and Auswahllisten erlauben die Eingabe verschiedener Optionen.
Verwenden sie die Tool-Buttons um diese hinzuzufügen, zu löschen und umzustellen.
Durch Aktivieren der Radio-Buttons bzw. Checkboxen neben den Optionen,
werden diese als Vorbelegung gewählt.
Verwenden Sie das Tool `Vorbelegung entfernen` um diese Auswahl zurückzusetzen.

### Verwenden des Formulars

Bearbeiten Sie die Seite, auf der das E-Mail-Formular angezeigt werden soll,
und fügen Sie den Plugin-Aufruf ein:

    {{{advancedform('FORMULAR_NAME')}}}

Das Einfachste ist den nötigen Code aus der Formular-Verwaltung
zu kopieren und einzufügen.

Nun ist das Formular bereit von den Besuchern Ihrer Website verwendet zu werden.
Diese können das Formular ausfüllen und absenden.
Wenn sie dabei einen Fehler machen, z.B. ein erforderliches Feld nicht ausfüllen,
eine ungültige E-Mail-Adresse oder Zahl eingeben
oder eine Datei angeben, die größer ist als erlaubt,
wird das Formular mit den bereits getätigten Eingaben
und den Fehlermeldungen darüber erneut angezeigt,
so dass die Besucher die Fehler korrigieren
und das Formular erneut absenden können.
Es ist nicht nötig, dass JavaScript im Browser des Besucher aktiviert ist,
aber falls doch, wird das erste fehlerhafte Feld fokusiert.
Allerdings ist keine der Feld-Validierungen auf JavaScript angewiesen.

Nach dem erfolgreichen Absenden des Formulars wird eine E-Mail an die
Empfänger (An, CC und BCC), die im Formular-Editor angegeben wurden, versendet.
Dann wird die versendete Information im Browser der Besuchers als Bestätigung angezeigt,
oder, falls eine Dank-Seite angegeben wurde,
werden die Besucher dorthin weiter geleitet,
und eine Bestätigungs-E-Mail wird an sie versendet.
Die Weiterleitung auf die Dank-Seite mit Bestätigungs-E-Mail ist nur möglich,
wenn ein erforderliches Feld des Typs `Absender (E-Mail)` im Formular existiert,
und `Mail` → `Confirmation` in der Konfiguration aktiviert ist.

Das Bestätigung-E-Mail-Feature kann von jedermann missbraucht werden,
nämlich durch Versenden beleidigender Inhalte an beliebige Empfänger,
indem die E-Mail-Addresse dieses Empfängers genutzt wird.
**Aus diesem Grund wird unbedingt empfohlen,
`Mail` → `Confirmation` zu deaktivieren.**

Versuche eine E-Mail per Advancedform zu versenden werden im System-Protokoll
von CMSimple\_XH (`Einstellungen` → `Log-Datei`) aufgezeichnet.

Beachten Sie, dass es möglich ist mehrere Formulare auf einer einzelnen Seite zu platzieren,
die unabhängig voneinander abgeschickt werden können.

### Ersetzen des eingebauten Kontakt-Formulars

Es ist möglich das eingebaute Kontakt-Formular von CMSimple\_XH
durch ein benutzerdefiniertes zu ersetzen.
Erstellen Sie dazu einfach das gewünschte Formular, und tragen Sie dessen Namen
in den Spracheinstellungen von Advancedform\_XH als `contact form` ein.
Nun wird der Kontakt-Formular-Link von CMSimple\_XH direkt Ihr eigenes Formular aufrufen.
Beachten Sie, dass für CMSimple\_XH eine E-Mail-Adresse konfiguriert sein muss,
damit der Kontaktformular-Link angezeigt wird,
aber diese von Advancedform\_XH ignoriert wird.

Alternativ fügen Sie den erforderlichen Skript-Code zum Aufruf
des Formulars auf einer versteckten CMSimple-Seite ein.
Dann müssen Sie Ihr Template ändern. Ersetzen Sie

    <?=mailformlink()?>

durch

    <?=advancedformlink('SEITEN_URL')?>

wobei SEITEN\_URL der Teil der URL der Seite nach dem Fragezeichen ist.
Es ist möglich auf diese Weise mehrere `advancedformlink()`s anzugeben.

### Vorlagen-System

Das Vorlagen-System ermöglich die Erstellung höchst individueller Formulare.
Power-User, die Formulare häufig erstellen oder verändern müssen,
sollten sich den [Form Mailer](https://simplesolutions.dk/?Form_Mailer)
von Jerry Jakobsfeld ansehen,
der noch flexibler einzusetzen ist als Advancedform\_XH.

Wenn eine Datei mit dem Namen `FORMULAR_NAME.tpl(.php)`
(wobei `FORMULAR_NAME` durch den Formularnamen ersetzt werden muss,
z.B. für ein Formular namens `kontakt`, ist der Dateiname `kontakt.tpl(.php)`)
im Daten-Ordner von Advancedform\_XH vorliegt,
wird es als Vorlagen-Datei verwendet.
Zusätzlich wird die Datei `css/FORMULAR_NAME.css`,
falls sie existiert, als Stylesheet in die CMSimple\_XH-Seite,
und der obere Teil dieses Stylesheets (abgetrennt wie für das Plugin-Stylesheet)
in die E-Mail eingebunden.
Und wenn eine Datei `js/FORMULAR_NAME.js` existiert,
wird diese ebenfalls in die Seite eingebunden.

Sie können die Vorlagen-Datei und deren Stylesheet selbst schreiben,
aber vielleicht ist es einfacher,
diese in der Formular-Verwaltung von Advancedform\_XH erzeugen zu lassen.
Auf diese Weise erzeugte Vorlagen-Dateien stellen das Formular
ähnlich des einspaltigen Layouts des Original Advancedform-Plugins dar.
Wenn Ihnen das genügt, sind Sie bereits fertig.

Wenn Sie das Aussehen anpassen möchten, schauen Sie sich die erzeugten Dateien an.
In der Vorlagen-Datei sehen Sie deren einfachen Aufbau.
Aus Gründen der Flexibilität ist alles in `<div>`s eingeschlossen.
Beachten Sie die Klasse der Container-`div`s.
Diese ist auf `break` voreingestellt,
so dass jedes Feld in einer neuen Zeile platziert wird.
Ändern Sie sie in `float`, dann werden die Felder nebeneinander angezeigt.
Wenn Sie die Beschriftung links von den Feldern haben möchten,
entfernen Sie einfach die Kommentare in `div.label` und `div.field`.

Eine Vorlagen-Datei ist prinzipiell eine PHP-Datei mit einer Erweiterung
der Syntax:

    <?field FELD_NAME?>

gibt das Feld mit dem Namen `FELD_NAME` aus.
Verwenden Sie keine weiteren Zeichen wie Leerzeichen außer
einem einzigen Leerzeichen zwischen `field` und `FELD_NAME`.

Die Vorlagen-Datei wird im Kontext von CMSimple\_XH ausgewertet,
so dass alle globalen Variablen, Konstanten und Funktionen verwendet werden können.
Allerdings ist es nicht möglich globale Variablen zu ändern
(abgesehen von den Superglobalen, was aber die Funktion des Systems stören könnte).
Und rufen Sie keine nicht existierenden Funktionen auf,
da dies einen Fehler im PHP-Interpreter auslösen würde.
**Sie sollten besonders vorsichtig im Umgang mit Vorlagen-Dateien aus nicht
vertrauenswürdigen Quellen sein, da diese bösartigen Code enthalten könnten,
der Ihre CMSimple\_XH-Installation beschädigen könnte.**

Eine besonders nützliche Funktion ist

    Advancedform_focusField($formular_name, $feld_name)

die den Fokus auf das angegebene Feld setzt.

### Hooks

Die Hooks sind verfügbar, um noch mehr Flexibilität zu haben,
wenn sie etwas PHP programmieren können.
Definieren Sie sie in einer Datei `FORMULAR_NAME.inc(.php)`
(wobei `FORMULAR_NAME` durch den Formularnamen ersetzt werden muss,
z.B. für ein Formular names `kontakt`, ist der Dateiname `kontakt.inc(.php)`)
im Daten-Ordner von Advancedform\_XH.
Beachten Sie, dass diese Datei wird per `include()` eingebunden wird,
so dass sie als echte PHP-Datei notiert werden muss.
Die Hooks werden von Advancedform\_XH bei bestimmten Anlässen aufgerufen.
Sie sind nicht an das Vorlagen-System gebunden.

    function advfrm_custom_field_default($form_name, $field_name, $opt, $is_resent)

Dies wird aufgerufen bevor das Formular an den Browser geschickt wird.
Es erlaubt Vorgabewerte für Felder dynamisch zu setzen.
Geben Sie einfach den Wert, der als Vorgabe für ein Feld gelten soll zurück.
Soll der Vorgabewert nicht geändert werden, geben Sie einfach `null` zurück.
Der dritte Parameter gilt nur für Radio-Buttons, Checkboxen und Auswahllisten.
Er enthält die Option, die gerade verarbeitet wird.
Geben sie `true` zurück, um die Option zu markieren,
`false` um die Markierung aufzuheben,
oder `null` um die Vorgabe aus dem Formular-Editor zu übernehmen.
Der Parameter `$is_resent` gibt an,
ob das Formular nach dem Absenden zum Browser zurück geschickt wurde,
da Fehler bei der Überprüfung festgestellt wurden.
Wenn das der Fall ist, werden die Werte,
die der Benutzer bereits eingegeben hat,
anstelle der Vorgaben aus dem Formular-Editor zurück gesendet.
In diesem Fall sollten Sie ggf. `null` zurück geben,
um die Eingaben des Benutzers nicht zu überschreiben.

    function advfrm_custom_valid_field($form_name, $field_name, $value)

Dies wird aufgerufen nachdem das Formular abgesandt wurde,
und ermöglicht zusätzliche Überprüfungen der Feld-Werte.
Geben Sie `true` zurück, wenn der gegebene `$value` erlaubt ist;
andernfalls sollten Sie eine Fehlermeldung zurück geben,
die dem Benutzer angezeigt wird.
Für Felder des Typs `Datei`
ist `value` das `$_FILES[]`-Array des angegebenen Felds.

    function advfrm_custom_mail($form_name, $mail, $is_confirmation)

Dies wird aufgerufen nachdem das `$mail`-Objekt
mit allen Informationen initialisiert wurde,
und gerade bevor die E-Mail verschickt wird,
und ermöglicht es das `$mail`-Objekt zu ändern.
Der Parameter `$form_name` gibt das gerade verarbeitete Formular an,
und der Parameter `$is_confirmation` gibt an,
ob das `$mail`-Objekt die Information für die E-Mail
oder die Bestätigungs-E-Mail enthält.
Um das Versenden ganz zu unterdrücken, geben Sie einfach `false` zurück.

    function advfrm_custom_thanks_page($form_name, $fields)

Dies wird aufgerufen nachdem die E-Mail versendet wurde,
und kann genutzt werden, um zu einer individualisierten Dank-Seite zu wechseln.
Geben Sie den Query-String
(d.h. den Teil der URL der Seite nach dem Fragezeichen) der Seite,
auf die gewechselt werden soll, zurück.
Bei Rückgabe eines leeren Strings wird zu der Dank-Seite weiter geleitet,
die im Formular-Editor angegeben wurde.
Wenn keine Dank-Seite vordefiniert wurde,
werden die versendeten Informationen angezeigt.
Der Parameter `$fields` ist ein Array,
das die Werte aller abgeschickten Formular-Felder enthält.
Für Details zum `$fields` Parameter siehe `Advancedform_fields()`.

Folgende Funktionen können für benutzerdefinierte Hooks nützlich sein:

- `Advancedform_fields()`
  gibt ein Array zurück, das die Werte aller übermittelten Formularfelder enthält.
  Das Format ist identisch zum Inhalt der PHP Superglobalen `$_POST` und `$_FILES`,
  außer das die `advfrm-` Prefixe in den Schlüsseln entfernt wurden,
  d.h. die Schlüssel sind genau die Namen der entsprechenden Felder.
  Außerdem werden aus historischen Gründen die Werte von
  `Checkbox`, `Radiobutton`, `Selectbox` und `Multi-Selectbox` Feldern
  als Strings und nicht als Unterarrays zurückgegeben.
  Gibt es mehrere Werte für diese Felder,
  dann werden sie durch unterbrochene Striche (`¦`) getrennt.
  Obgleich es möglich ist, auf die Superglobalen in Hooks direkt zuzugreifen,
  wird empfohlen statt dessen `Advancedform_fields()` zu verwenden,
  da sich die Namen der Schlüssel in Zukunft ändern könnten.
- `Advancedform_readCsv()`
  gibt ein Array von Datensätzen der Daten,
  die bereits in der CSV-Datei gespeichert sind, zurück.
  Die Datensätze sind Arrays, wo jedes Element ein einziges Feld repräsentiert;
  der Schlüssel ist der Name des Feldes.

### Demo-Formulare

Sie sollten sich die ausgelieferten Demo-Formulare (in `data/README`
finden Sie weitere Details) anschauen, um zu sehen, was möglich ist, und
wie es gemacht wird.

**Vorsicht:** natürlich können Sie die Demo-Formulare als Basis für Ihre
eigenen verwenden. Da aber die meisten Demo-Formulare das Vorlagen- bzw.
Hook-System verwenden, könnte das unerwartete Ergenisse zur Folge haben.
Entweder entfernen Sie nicht gewünschte Template-/Hook-Dateien manuell,
oder Sie erzeugen eine Kopie des Formulars in der Formular-Verwaltung
und verwenden diese Kopie.

## Einschränkungen

### Alternative Mailer

Das ursprüngliche AdvancedForm-Plugin unterstützte verschiedene Arten von Mailern.
Dies scheint aber nicht nötig.
Die meisten Webhoster stellen die Möglichkeit zur Verfügung E-Mails per `mail()` zu versenden,
welches leicht konfiguriert werden kann,
und für die Zwecke von Advancedform\_XH mehr als ausreichend ist.

### Spam-Schutz

Das ursprüngliche Advanceform-Plugin bot mehrere Möglichkeiten zum Spam-Schutz:
IP-Blacklists, einen „bad word“ Filter, eine XSS-Erkennungsmöglichkeit.
Die Autoren von Advancedform\_XH sind nicht davon überzeugt,
dass dies vernünftige Mechanismen zur Spam-Bekämpfung sind.
Daher wurde keiner davon implementiert (abgesehen vom Schutz vor XSS),
sondern statt dessen ein CAPTCHA verfügbar gemacht.
Dieses stellt nur eine minimalistische textbasierte Variante dar,
aber bessere CAPTCHAs können als zusätzliches
kompatibles CAPTCHA-Plugin genutzt werden.
Zur Zeit ist
[Cryptographp\_XH](https://github.com/cmb69/Cryptographp_XH)
verfügbar.

## Fehlerbehebung

Melden Sie Programmfehler und stellen Sie Supportanfragen entweder auf
[Github](https://github.com/cmb69/advancedform_xh/issues)
oder im [CMSimple\_XH Forum](https://cmsimpleforum.com/).

## Lizenz

Advancedform\_XH ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung von Advancedform\_XH erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
Advancedform\_XH erhalten haben. Falls nicht, siehe
<https://www.gnu.org/licenses/>.

© 2005-2010 Jan Kanters  
© 2011-2022 Christoph M. Becker

Dänische Übersetzung © 2012 Jens Maegard  
Estnische Übersetzung © 2012 Alo Tänavots  
Französische Übersetzung © 2014 Patrick Varlet  
Slovakische Übersetzung © 2012 Dr. Martin Sereday  
Tschechische Übersetzung © 2011-2012 Josef Němec

## Danksagung

Advancedform\_XH basiert auf AdvancedForm Pro von Jan Kanters.
Vielen Dank an ihn, dass er die Erlaubnis gegeben hat,
seinen Code für eine CMSimple\_XH kompatible Version zu verwenden,
und an *Holger* und *johnjdoe*, die diese Erlaubnis ausgehandelt haben.

Das Erstellen und Versenden der E-Mails wird durch
[PHPMailer](https://github.com/PHPMailer/PHPMailer) ermöglicht.
Vielen Dank für die Veröffentlichung
dieser fortgeschrittenen E-Mail-Bibliothek unter LGPL-2.1.

Der reguläre Ausdruck um auf gültige E-Mail-Adressen zu prüfen, stammt
von [Jan Goyvaerts](https://www.regular-expressions.info/email.html).
Vielen Dank für das großartige Tutorial zu regulären Ausdrücken und die
Beispiele.

Das Plugin-Icon wurde von Jack Cai entworfen.
Vielen Dank für die Veröffentlichung unter CC BY-ND.

Die Icons im Backend stammen von [Font Awesome](https://fontawesome.com/).
Vielen Dank für die Veröffentlichung dieser SVGs unter CC BY 4.0.

Vielen Dank an die Community im [CMSimple\_XH Forum](https://cmsimpleforum.com/)
für Tipps, Vorschläge und das Testen.
Besonders möchte ich *Tata* für die Idee danken, dass Advancedform\_XH
eine grundlegende Vorlagen-Datei mit Stylesheet erzeugen sollte,
und *manu* für die konkreten Vorschläge für das Hook-System.
Und vielen Dank an *maeg*,
der es mir ermöglicht hat auf seinem Server zu debuggen,
so dass ich einen Fehler finden und beheben konnte,
der den Mailversand auf manchen Servern scheitern ließ.
Ebenfalls besonderen Dank an *knollsen* und *frase*
für das prompte Melden von schweren Regressionen in Advancedform_XH 2.1.

Und zu guter Letzt vielen Dank an
[Peter Harteg](https://www.harteg.dk/), den „Vater“ von CMSimple,
und alle Entwickler von [CMSimple\_XH](https://www.cmsimple-xh.org/de/),
ohne die dieses phantastische CMS nicht existieren würde.
