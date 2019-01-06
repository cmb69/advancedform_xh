# Advancedform\_XH

Advancedform\_XH ermöglicht es Ihnen Ihre eigenen E-Mail-Formulare zur
Integration in CMSimple\_XH zu erstellen. Die Möglichkeiten reichen von
angepassten Kontaktformularen bis zu komplexen Bestell- oder
Buchungsformularen. Selbst komplexe Formulare können im Formular-Editor
konstruiert werden, so dass Sie kein HTML, CSS oder PHP schreiben
müssen, wenn Ihnen die grundlegende Formular-Funktionalität genügt.
Weitergehende Anpassungen sind durch das Template- bzw. Hook-System
möglich.

  - [Vorraussetzungen](#vorraussetzungen)
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
  - [Beschränkungen](#beschränkungen)
  - [Lizenz](#lizenz)
  - [Danksagung](#danksagung)

## Vorraussetzungen

Advancedform\_XH ist ein Plugin für CMSimple\_XH. Es benötigt eine UTF-8
kodierte Version und PHP ≥ 5.3.0.

## Installation

Die Installation erfolgt wie bei vielen anderen CMSimple\_XH-Plugins
auch. Im [CMSimple\_XH
Wiki](http://www.cmsimple-xh.org/wiki/doku.php/de:installation) finden
sie ausführliche Hinweise.

1.  Sichern Sie die Daten auf Ihrem Server.
2.  Entpacken Sie die ZIP-Datei auf Ihrem Computer.
3.  Laden Sie das gesamte Verzeichnis advancedform/ auf Ihren Server in
    das CMSimple\_XH Plugin-Verzeichnis hoch.
4.  Vergeben Sie falls nötig Schreibrechte für die Unterverzeichnisse
    config/, css/, languages/ und den Daten-Ordner des Plugins.
5.  Schützen Sie den Daten-Ordner von Advancedform\_XH vor direktem
    Zugriff auf eine Weise, die Ihr Webserver unterstützt.
    .htaccess-Dateien für Apache Server sind bereits im voreingestellten
    Daten-Ordner enthalten. Beachten Sie, dass die Unterordner css/ und
    js/ öffentlichen Zugriff erlauben müssen.
6.  Gehen Sie zu Advancedform im Administrationsbereich, und prüfen Sie,
    ob alle Vorraussetzungen erfüllt sind.

## Einstellungen

Die Konfiguration des Plugins erfolgt wie bei vielen anderen
CMSimple\_XH-Plugins auch im Administrationsbereich der Homepage. Wählen
Sie unter "Plugins" "Advancedform" aus.

Sie können die Original-Einstellungen von Advancedform\_XH unter
"Konfiguration" ändern. Beim Überfahren der Hilfe-Icons mit der Maus
werden Hinweise zu den Einstellungen angezeigt. Die Einstellung
"php\_extension" bietet zusätzliche Sicherheit in Verbindung mit dem
Vorlagen-System und den Hooks, wenn sie aktiviert wird. Allerdings
funktionieren dann einige Demo-Formulare nicht.

Die Lokalisierung wird unter "Sprache" vorgenommen. Sie können die
Zeichenketten in Ihre eigene Sprache übersetzen, oder sie entsprechend
Ihren Anforderungen anpassen.

Das Aussehen von Advancedform\_XH kann unter "Stylesheet" angepasst
werden. Der obere Teil enthält das Styling der Formulare, die im
Front-End angezeigt werden. Das Formular wird als Tabelle mit zwei
Spalten angezeigt. Die linke Spalte enthält die Beschriftungen, die
rechte Spalte die Felder. Sie können einfach die Stile der Klassen
"div.advfrm-mailform td.label" und "div.advfrm-mailform td.field"
anpassen. Das Aussehen einzelner Formulare kann durch Auswahl von
"form\[name=FORMULAR\_NAME\]", das Aussehen einzelner Felder durch
Auswahl von "\#advfrm-FORMULAR\_NAME-FELD\_NAME" angepasst werden. Wenn
Sie ein einspaltiges Layout bevorzugen, müssen Sie auf das
[Vorlagen-System](#vorlagen-system) zurückgreifen.

Der untere Teil des Stylesheets enthält das Styling der
Formular-Verwaltung und des Formular-Editors. Wollen Sie, dass das
Aussehen der Eigenschafts-Dialoge zu Ihrem Template passt, sollten Sie
in Erwägung ziehen, jQueryUI-Theming zu Ihrem Template hinzuzufügen. Wie
das geht, wird im [CMSimple\_XH
Forum](http://www.cmsimpleforum.com/viewtopic.php?f=29&t=3435&start=2)
erklärt.

Nur der erste Teil des Stylesheets oberhalb der Zeile

    /* END OF MAIL CSS */

wird in den versandten E-Mails eingebunden. Definieren Sie also alle
Stile, die für die E-Mail erforderlich sind, im oberen Teil des
Stylesheets.

## Verwendung

### Formular-Verwaltung

Im Administrationsbereich finden Sie unter "E-Mail-Formulare" die
Übersicht aller definierten E-Mail-Formulare. Sie können neue
hinzufügen und importieren sowie bestehende bearbeiten, löschen,
kopieren und exportieren. Rechts daneben befindet sich der Skript-Code,
der nötig ist, um das jeweilige Formular auf einer Seite anzuzeigen.
Kopieren Sie einfach den Code und fügen Sie ihn auf der gewünschten
Seite ein.

### Formular-Editor

Im Formular-Editor können Sie Ihre Formulare erstellen. Die Details
werden in den folgenden Abschnitten erklärt.

#### Allgemeine Formular-Eigenschaften

Im oberen Teil des Formular-Editors können Sie die allgemeinen
Formular-Eigenschaften bearbeiten.

  - **Name**:
    Der Name eines Formulars darf nur alphanumerische Zeichen und
    Unterstriche enthalten. Er muss eindeutig für alle definierten
    Formulare sein. Er wird verwendet, um das Formular zu
    identifizieren.
  - **Titel**:
    Der Titel des Formulars wird nur im Betreff der E-Mail verwendet.
  - **An (Name)**:
    Der Name des Empfängers der E-Mail.
  - **An (E-Mail)**:
    Die Adresse des Empfängers der E-Mail.
  - **CC**:
    Die durch Strichpunkt getrennten Adressen der CC Empfänger der
    E-Mail.
  - **BCC**:
    Die durch Strichpunkt getrennten Adressen der BCC Empfänger der
    E-Mail.
  - **CAPTCHA**:
    Ob ein CAPTCHA im Formular integriert werden soll.
  - Daten speichern  
    Ob die abgeschickten Daten zusätzlich in einer CSV-Datei gespeichert
    werden sollen.
  - **Dank-Seite**:
    Wenn leer, wird nach dem E-Mail-Versand die gesendete Information
    angezeigt. Wenn gesetzt und eine Absender E-Mail-Adresse eingegeben
    wurde, wird der Besucher nach dem E-Mail-Versand auf diese Seite
    weiter geleitet, und eine Bestätigungs-E-Mail mit den gesendeten
    Information wird an ihn geschickt.

#### Formular-Felder

Im unteren Teil des Formular-Editors können Sie die Felder des Formulars
bearbeiten. Verwenden sie die Tool-Icons um Felder hinzuzufügen, zu
löschen oder zu verschieben.

  - **Name**:
    Der Name des Felds darf nur alphanumerische Zeichen und Unterstriche
    enthalten. Er muss für alle definierten Felder des aktuellen
    Formulars eindeutig sein. Er wird verwendet, um das Feld zu
    identifizieren.
  - **Beschriftung**:
    Die Beschriftung die neben dem Feld angezeigt werden soll.
  - **Typ**:
    Der Feld-Typ. Rechts vom Auswahlfeld befindet sich das
    Eigenschaften-Tool-Icon. Klicken Sie es an, um den Dialog zum
    Bearbeiten der Eigenschaften des ausgewählten Feld-Typs zu öffnen.
  - **Erf.**:
    Ob das Feld erforderlich ist, d.h. vom Besucher ausgefüllt werden
    muss.

#### Feld-Typen

  - **Text**:
    Ein allgemeines Text-Feld.
  - **Absender (Name)**:
    Ein Feld zur Eingabe des Namens des Absenders. Diese Information
    wird im E-Mail-Header eingefügt. Höchstens ein Feld des Typs
    "Absender (Name)" darf für jedes Formular verwendet werden.
  - **Absender (E-Mail)**:
    Ein Feld zur Eingabe der E-Mail-Adresse des Absenders, welche
    validiert wird. Diese Information wird als From-Header der Mail
    verwendet und als To-Header der Bestätigungs-E-Mail. Höchstens ein
    Feld des Typs "Absender (E-Mail)" darf für jedes Formular verwendet
    werden.
  - **E-mail**:
    Ein Feld zur Eingabe einer allgemeinen E-Mail-Adresse, die validiert
    wird.
  - **Datum**:
    Ein Feld zur Eingabe eines Datums, das validiert wird. Wenn
    JavaScript im Browser des Besuchers aktiviert ist, steht ein
    Datepicker zur Verfügung.
  - **Zahl**:
    Ein Feld zur Eingabe einer nicht negativen Ganzzahl, die validiert
    wird.
  - **Textbereich**:
    Ein Feld zur Eingabe von mehrzeiligen Texten.
  - **Radio-Button**:
    Ein Feld zur Auswahl einer von mehreren Optionen.
  - **Checkbox**:
    Ein Feld zur Auswahl einer beliebigen Anzahl mehrerer Optionen.
  - **Auswahlliste**:
    Ein Feld zur Auswahl einer von mehreren Optionen.
  - **Mehrfach-Auswahlliste**:
    Ein Feld zur Auswahl einer beliebigen Anzahl mehrerer Optionen.
  - **Kennwort**:
    Ein Feld zur Eingabe eines Kennworts.
  - **Datei**:
    Ein Feld, das es dem Besucher ermöglicht eine Datei als
    E-Mail-Anhang zur versenden. Der Anhang wird in der
    Bestätigungs-E-Mail nicht zum Besucher zurück geschickt.
  - **Versteckt**:
    Ein verstecktes Feld. Versteckte Felder werden dem Besucher niemals
    angezeigt. Sie können in Verbindung mit dem Vorlagen-System und den
    Hooks nützlich sein.
  - **Ausgabe**:
    Ein Feld um beliebiges HTML auszugeben.
  - **Benutzerdefiniert**:
    Ein Feld, das gegen einen anzugebenden regulären Ausdruck validiert
    wird.

#### Feld-Eigenschaften

Die Feld-Eigenschaften werden in einem Dialog bearbeitet, der durch
Anklicken des Eigenschaften-Icons geöffnet werden kann. Die
unterschiedlichen Feld-Typen haben verschiedene Eigenschaften.

  - **Größe**:
    Für Textfelder im weiteren Sinn die Breite des Felds gemessen in
    Zeichen. Für Auswahllisten die Höhe der Liste. "1" erzeugt eine
    Dropdown-Auswahlliste.
  - **Ausrichtung**:
    Nur für Radio-Buttons und Checkboxen: ob diese horizontal oder
    vertikal dargestellt werden sollen.
  - **Max. Länge**:
    Die Höchstanzahl der Zeichen, die eingegeben werden können. Für
    Dateifelder wird damit die maximale Größe der Datei in Bytes
    angegeben.
  - **Spalten**:
    Die Breite des Textbereichs in Zeichen.
  - **Zeilen**:
    Die Höhe des Textbereichs in Zeichen.
  - **Vorbelegung**:
    Die Vorbelegung des Felds.
  - **Wert**:
    Das HTML für Ausgabe-Felder.
  - **Dateitypen**:
    Nur für Datei-Felder: eine durch Komma getrennte Liste erlaubter
    Dateierweiterungen, z.B. *jpeg,jpg,png,gif,bmp* für Bilder.
  - **Beschränkung**:
    Nur für benutzerdefinierte Felder: der reguläre Ausdruck gegen den
    die Eingabe geprüft werden soll.
  - **Fehlermeldung**:
    Nur für benutzerdefinierte Felder: die Fehlermeldung, die angezeigt
    werden soll, wenn die Eingabe nicht zum regulären Ausdruck passt.
    Verwenden Sie *%s* um die Beschriftung des Felds in der Meldung
    einzufügen.

Radio-Buttons, Checkboxen and Auswahllisten erlauben die Eingabe
verschiedener Optionen. Verwenden sie die Tool-Buttons um diese
hinzuzufügen, zu löschen und umzustellen. Durch Aktivieren der
Radio-Buttons bzw. Checkboxen neben den Optionen, werden diese als
Vorbelegung gewählt. Verwenden Sie das Tool "Vorbelegung entfernen" um
diese Auswahl zurück zu setzen.

### Verwenden des Formulars

Bearbeiten Sie die Seite, auf der das E-Mail-Formular angezeigt werden
soll, und fügen Sie den Plugin-Aufruf ein:

    advancedform('FORMULAR_NAME');

Das Einfachste ist den nötigen Code aus der Formular-Verwaltung zu
kopieren und einzufügen.

Nun ist das Formular bereit von den Besuchern Ihrer Homepage verwendet
zu werden. Diese können das Formular ausfüllen und absenden. Wenn sie
dabei einen Fehler machen, z.B. ein erforderliches Feld nicht ausfüllen,
eine ungültige E-Mail-Adresse oder Zahl eingeben oder eine Datei
angeben, die größer ist als erlaubt, wird das Formular mit den bereits
getätigten Eingaben und den Fehlermeldungen darüber erneut angezeigt, so
dass die Besucher die Fehler korrigieren und das Formular erneut
absenden können. Es ist nicht nötig, dass JavaScript im Browser des
Besucher aktiviert ist, aber falls doch, wird das erste fehlerhafte Feld
fokusiert, und für Datum-Felder ist ein Datepicker verfügbar. Allerdings
ist keine der Feld-Validierungen auf JavaScript angewiesen.

Nach dem erfolgreichen Absenden des Formulars wird eine E-Mail an die
Empfänger (An, CC und BCC), die im Formular-Editor angegeben wurden,
versendet. Dann wird die versendete Information im Browser des Besuchers
als Bestätigung angezeigt, oder, falls eine Dank-Seite angegeben wurde,
wird der Besucher dorthin weiter geleitet, und eine Bestätigungs-E-Mail
wird an ihn versendet. Die Weiterleitung auf die Dank-Seite mit
Bestätigungs-E-Mail ist nur möglich, wenn ein erforderliches Feld des
Typs "Absender (E-Mail)" im Formular existiert.

Unter CMSimple\_XH 1.6 und höher, werden Versuche eine E-Mail per
Advancedform zu versenden im System-Protokoll von CMSimple\_XH
(Einstellungen → Log-Datei) aufgezeichnet.

Beachten Sie, dass es möglich ist mehrere Formulare auf einer einzelnen
Seite zu platzieren, die unabhängig voneinander abgeschickt werden
können.

### Ersetzen des eingebauten Kontakt-Formulars

Es ist möglich das eingebaute Kontakt-Formular von CMSimple\_XH durch
ein benutzerdefiniertes zu ersetzen. Erstellen Sie dazu einfach das
gewünschte Formular, und tragen Sie dessen Namen in Advancedform\_XHs
Spracheinstellungen als "contact form" ein. Nun wird CMSimple\_XHs
Kontakt-Formular-Link direkt Ihr eigenes Formular aufrufen. Beachten
Sie, dass für CMSimple\_XH eine E-Mail-Adresse konfiguriert sein muss,
damit der Kontaktformular-Link angezeigt wird, aber diese von
Advancedform\_XH ignoriert wird.

Alternativ fügen Sie den erforderlichen Skript-Code zum Aufruf des
Formulars auf einer versteckten CMSimple-Seite ein. Dann müssen Sie Ihr
Template ändern. Ersetzen Sie

    <?php echo mailformlink()?>

durch

    <?php echo advancedformlink('SEITEN_URL')?>

wobei SEITEN\_URL der Teil der URL der Seite nach dem Fragezeichen ist.
Es ist möglich auf diese Weise mehrere advancedformlink()s anzugeben.

### Vorlagen-System

Das Vorlagen-System ermöglich die Erstellung höchst individueller
Formulare. Power-User, die Formulare häufig erstellen oder verändern
müssen, sollten sich den [Form
Mailer](http://simplesolutions.dk/?Form_Mailer) von Jerry Jakobsfeld
ansehen, der noch flexibler einzusetzen ist als Advancedform\_XH.

Wenn eine Datei mit dem Namen FORMULAR\_NAME.tpl(.php) in
Advancedform\_XHs Daten-Ordner vorliegt, wird es als Vorlagen-Datei
verwendet. Zusätzlich wird die Datei css/FORMULAR\_NAME.css, falls sie
existiert, als Stylesheet in die CMSimple\_XH-Seite und der obere Teil
dieses Stylesheets (abgetrennt wie für das Plugin-Stylesheet) in die
E-Mail eingebunden. Und wenn eine Datei js/FORMULAR\_NAME.js existiert,
wird diese ebenfalls in die Seite eingebunden.

Sie können die Vorlagen-Datei und deren Stylesheet selbst schreiben,
aber vielleicht ist es einfacher, diese in der Formular-Verwaltung von
Advancedform\_XH erzeugen zu lassen. Auf diese Weise erzeugte
Vorlagen-Dateien stellen das Formular ähnlich des einspaltigen Layouts
des Original Advancedform-Plugins dar. Wenn Ihnen das genügt, sind Sie
bereits fertig.

Wenn Sie das Aussehen anpassen möchten, schauen Sie sich die erzeugten
Dateien an. In der Vorlagen-Datei sehen Sie deren einfachen Aufbau. Aus
Gründen der Flexibilität ist alles in \<div\>s eingeschlossen. Beachten
Sie die Klasse der Container-divs. Diese ist auf "break" voreingestellt,
so dass jedes Feld in einer neuen Zeile platziert wird. Ändern Sie sie
in "float", dann werden die Felder nebeneinander angezeigt. Wenn Sie die
Beschriftung links von den Feldern haben möchten, entfernen Sie einfach
die Kommentare in div.label und div.field

Eine Vorlagen-Datei ist prinzipiell eine PHP-Datei mit einer Erweiterung
der Syntax:

    <?field FELD_NAME?>

gibt das Feld mit dem Namen FELD\_NAME aus. Verwenden Sie keine weiteren
Zeichen wie Leerzeichen außer einem einzigen Leerzeichen zwischen field
und FELD\_NAME. Diese Notation ist eigentlich eine Abkürzung für

    <?php echo Advancedform_displayField('FORMULAR_NAME', 'FELD_NAME')?>

Die Vorlagen-Datei wird im Kontext von CMSimple\_XH ausgewertet, so dass
alle globalen Variablen, Konstanten und Funktionen verwendet werden
können. Allerdings ist es nicht möglich globale Variablen zu ändern
(abgesehen von den Superglobalen, was aber die Funktion des Systems
stören könnte). Und rufen Sie keine nicht existierenden Funktionen auf,
da dies einen Fehler im PHP-Interpreter auslösen würde. **Sie sollten
besonders vorsichtig im Umgang mit Vorlagen-Dateien aus nicht
vertrauenswürdigen Quellen sein, da diese bösartigen Code enthalten
könnten, der Ihre CMSimple\_XH-Installation beschädigen könnte.**

Eine besonders nützliche Funktion ist

    Advancedform_focusField($formular_name, $feld_name)

die den Focus auf das angegebene Feld setzt.

### Hooks

Die Hooks sind verfügbar, um noch mehr Flexibilität zu haben, wenn sie
etwas PHP programmieren können. Definieren Sie sie in einer Datei
FORMULAR\_NAME.inc(.php) in Advancedform\_XHs Daten-Ordner. Anmerkung:
diese Datei wird per include() eingebunden, so dass sie als echte
PHP-Datei notiert werden muss. Die Hooks werden von Advancedform\_XH bei
bestimmten Anlässen aufgerufen. Sie sind nicht an das Vorlagen-System
gebunden.

    function advfrm_custom_field_default($form_name, $field_name, $opt, $is_resent)

Dies wird aufgerufen bevor das Formular an den Browser geschickt wird.
Es erlaubt Vorgabewerte für Felder dynamisch zu setzen. Geben Sie
einfach den Wert, der als Vorgabe für ein Feld gelten soll zurück. Soll
der Vorgabewert nicht geändert werden, geben Sie einfach NULL zurück.
Der dritte Parameter gilt nur für Radio-Buttons, Checkboxen und
Auswahllisten. Er enthält die Option, die gerade verarbeitet wird. Geben
sie TRUE zurück, um die Option zu markieren, FALSE um die Markierung
aufzuheben, oder NULL um die Vorgabe aus dem Formular-Editor zu
übernehmen. Der Parameter $is\_resent gibt an, ob das Formular nach dem
Absenden zum Browser zurück geschickt wurde, da Fehler bei der
Überprüfung festgestellt wurden. Wenn das der Fall ist, werden die
Werte, die der Benutzer bereits eingegeben hat, anstelle der Vorgaben
aus dem Formular-Editor zurück gesendet. In diesem Fall sollten Sie ggf.
NULL zurück geben, um die Eingaben des Benutzers nicht zu überschreiben.

    function advfrm_custom_valid_field($form_name, $field_name, $value)

Dies wird aufgerufen nachdem das Formular abgesandt wurde, und
ermöglicht zusätzliche Überprüfungen der Feld-Werte. Geben Sie TRUE
zurück, wenn der gegebene $value erlaubt ist; andernfalls sollten Sie
eine Fehlermeldung zurück geben, die dem Benutzer angezeigt wird. Für
Felder des Typs "Datei" ist $value das $\_FILES\[\]-Array des
angegebenen Felds.

    function advfrm_custom_mail($form_name, $mail, $is_confirmation)

Dies wird aufgerufen nachdem das $mail-Objekt mit allen Informationen
initialisiert wurden, und gerade bevor die E-Mail verschickt wird, und
ermöglicht das $mail-Objekt zu ändern.
herunter geladen werden. Der Parameter $form\_name gibt das gerade
verarbeitete Formular an, und der Parameter $is\_confirmation gibt an,
ob das $mail-Objekt die Information für die E-Mail oder die
Bestätigungs-E-Mail enthält. Um das Versenden zu unterdrücken, geben
Sie einfach FALSE zurück.

    function advfrm_custom_thanks_page($form_name, $fields)

Dies wird aufgerufen nachdem die E-Mail versendet wurde, und kann
genutzt werden, um zu einer individualisierten Dank-Seite zu wechseln.
Geben Sie den Query-String (d.h. den Teil der URL der Seite nach dem
Fragezeichen) der Seite, auf die gewechselt werden soll, zurück. Bei
Rückgabe eines leeren Strings wird zu der Dank-Seite weiter geleitet,
die im Formular-Editor angegeben wurde. Wenn keine Dank-Seite
vordefiniert wurde, werden die versendeten Informationen angezeigt. Der
Parameter $fields ist ein Array, das die Werte aller abgeschickten
Formular-Felder enthält.

### Demo-Formulare

Sie sollten sich die ausgelieferten Demo-Formulare (in data/README
finden Sie weitere Details) anschauen, um zu sehen, was möglich ist, und
wie es gemacht wird.

**Vorsicht:** natürlich können Sie die Demo-Formulare als Basis für Ihre
eigenen verwenden. Da aber die meisten Demo-Formulare das Vorlagen- bzw.
Hook-System verwenden, könnte das unerwartete Ergenisse zur Folge haben.
Entweder entfernen Sie nicht gewünschte Template-/Hook-Dateien manuell,
oder Sie erzeugen eine Kopie des Formulars in der Formular-Verwaltung
und verwenden diese Kopie.

## Beschränkungen

### Mailversand scheitert

Wenn das Versenden von E-Mails mit der Meldung "Mail Funktion konnte
nicht initialisiert werden" fehlschlägt, testen Sie, ob der Mail-Versand
mit CMSimple\_XHs eingebautem Mailformular funktioniert. Falls ja,
fragen Sie im [CMSimple\_XH Forum](http://cmsimpleforum.com/) nach
Hilfe; ansonsten fragen Sie den Provider Ihres Webspace, ob es dort
irgendwelche Beschränkungen für den Mailversand mit PHP's mail()
Funktion gibt.

### jQuery

Advancedform\_XH *könnte* in Installationen mit jQuery abhängigen
Plugins/Addons/Vorlagen, die jQuery4CMSimple nicht verwenden, sondern
ihre eigene jQuery Bibliothek importieren, nicht funktionieren. Dieses
Problem wird nicht behoben werden (es ist ohnehin nicht möglich, es für
alle Fälle zu beheben), weil allen Entwicklern geraten wird,
ausschließlich jQuery4CMSimple in Verbindung mit ihrem jQuery basierten
Code für CMSimple\_XH zu verwenden.

### Alternative Mailer

Das ursprüngliche AdvancedForm-Plugin hat verschiedene Arten von Mailern
unterstützt. M.E. ist das aber nicht nötig. Die meisten Webhoster
stellen die Möglichkeit zur Verfügung E-Mails per mail() zu versenden,
welches leicht konfiguriert werden kann, und für die Zwecke von
Advancedform\_XH mehr als ausreichend ist.

### Spam-Schutz

Das ursprüngliche Advanceform-Plugin bot mehrere Möglichkeiten zum
Spam-Schutz: IP-Blacklists, einen "bad word" Filter, eine
XSS-Erkennungsmöglichkeit. Ich bin nicht sicher, ob diese Mechanismen
wirklich der beste Weg zur Spam-Bekämpfung sind. Daher habe ich keinen
davon implementiert (abgesehen vom Schutz vor XSS), sondern statt dessen
ein CAPTCHA verfügbar gemacht. Dieses stellt nur eine minimalistische
textbasierte Variante dar, aber bessere CAPTCHAs können als zusätzliches
kompatibles CAPTCHA-Plugin genutzt werden. Soweit ich weiß, sind dies
zur Zeit nur
[Recaptcha\_XH](http://3-magi.net/?CMSimple_XH/Recaptcha_XH) und
[Cryptographp\_XH](http://3-magi.net/?CMSimple_XH/Cryptographp_XH).

## Lizenz

Dieses Programm ist freie Software. Sie können es unter den Bedingungen
der GNU General Public License, wie von der Free Software Foundation
veröffentlicht, weitergeben und/oder modifizieren, entweder gemäß
Version 3 der Lizenz oder (nach Ihrer Option) jeder späteren Version.

Die Veröffentlichung dieses Programms erfolgt in der Hoffnung, daß es
Ihnen von Nutzen sein wird, aber *ohne irgendeine Garantie*, sogar ohne
die implizite Garantie der *Marktreife* oder der *Verwendbarkeit für einen
bestimmten Zweck*. Details finden Sie in der GNU General Public License.

Sie sollten ein Exemplar der GNU General Public License zusammen mit
diesem Programm erhalten haben. Falls nicht, siehe
<http://www.gnu.org/licenses/>.

© 2005-2010 Jan Kanters  
© 2011-2018 Christoph M. Becker

Dänische Übersetzung © 2012 Jens Maegard  
Estnische Übersetzung © 2012 Alo Tänavots  
Französische Übersetzung © 2014 Patrick Varlet  
Slovakische Übersetzung © 2012 Dr. Martin Sereday  
Tschechische Übersetzung © 2011-2012 Josef Němec

## Danksagung

Advancedform\_XH basiert auf AdvancedForm Pro von Jan Kanters. Vielen
Dank an ihn, dass er die Erlaubnis gegeben hat, seinen Code für eine
CMSimple\_XH kompatible Version zu verwenden, und an Holger und
johnjdoe, die diese Erlaubnis ausgehandelt haben.

Der reguläre Ausdruck um auf gültige E-Mail-Adressen zu prüfen, stammt
von [Jan Goyvaerts](http://www.regular-expressions.info/email.html).
Vielen Dank für das großartige Tutorial zu regulären Ausdrücken und die
Beispiele.

Das Plugin-Icon wurde von [Jack Cai](http://www.doublejdesign.co.uk/)
entworfen. Vielen Dank für die Veröffentlichung unter CC BY-ND.

Dieses Plugin verwendet freie Anwendungs-Icons von
[Aha-Soft](http://www.aha-soft.com/). Vielen Dank für die freie
Verwendbarkeit dieser Icons.

Vielen Dank an die Community im [CMSimple\_XH
Forum](http://cmsimpleforum.com/) für Tipps, Vorschläge und das Testen.
Besonders möchte ich Tata für die Idee danken, dass Advancedform\_XH
eine grundlegende Vorlagen-Datei mit Stylesheet erzeugen sollte, und
manu für die konkreten Vorschläge für das Hook-System. Und vielen Dank
an maeg, der es mir ermöglicht hat auf seinem Server zu debuggen, so
dass ich einen Fehler finden und beheben konnte, der den Mailversand auf
manchen Servern scheitern ließ.

Und zu guter Letzt vielen Dank an [Peter Harteg](http://www.harteg.dk/),
den "Vater" von CMSimple, und alle Entwickler von
[CMSimple\_XH](http://www.cmsimple-xh.org/), ohne die dieses
phantastische CMS nicht existieren würde.
