Eine schnelle Tour durch Symfony 2.0: Der Überblick
===================================================

Du möchtest Symfony ausprobieren, hast aber nur 10 Minuten Zeit?
Dann ist dieser erste Teil des Tutorials wie für dich geschaffen.
Er erklärt den schnellen Einstieg in Symfony, indem die Struktur eines
einfachen vorgefertigten Projektes gezeigt wird.

Wenn du jemals ein Webframework verwendet hast, solltest du dich dabei wie zu
Hause fühlen.

Download and Installation
-------------------------

Zunächst solltest du prüfen, ob du PHP in der Version 5.3.0 oder höher installiert
und für die Arbeit mit einem Webserver wie Apache korrekt konfiguriert hast.

Fertig? Beginnen wir mit dem Download von Symfony. Um schnell vorwärts zu
kommen, nutzen wir die "Symfony sandbox". Das ist ein Symfony-Projekt, welches
bereits alle benötigten Bibliotheken sowie einige einfache Controller beinhaltet
und auch eine fertige Grundkonfiguration mit sich bringt. Der große Vorteil der
Sandbox gegenüber anderen Installationsarten ist, dass du sofort beginnen kannst
mit Symfony zu experimentieren.

Lade die [sandbox][1] herunter und entpacke sie im Webverzeichnis.
Nun solltest du ein `sandbox/`-Verzeichnis haben:

    www/ <- dein Webverzeichnis
      sandbox/ <- das entpackte Archiv
        hello/
          cache/
          config/
          logs/
        src/
          Application/
            HelloBundle/
              Controller/
              Resources/
          vendor/
            symfony/
        web/

Konfiguration prüfen
--------------------

Um späterem Kopfzerbrechen vorzubeugen, solltest du schon jetzt sicherstellen,
dass die Konfiguration für den Einsatz eines Symfony-Projektes vorbereitet ist,
indem du einfach folgende URL aufrufst:

    http://localhost/sandbox/web/check.php

Lies das Ergebnis ganz genau und löse die Probleme, die es findet.

Nun ist es Zeit, die erste "echte" Symfony-Website aufzurufen:

    http://localhost/sandbox/web/index_dev.php/

Symfony sollte dir zu deiner bisherigen harten Arbeit gratulieren!

Deine erste Applikation
-----------------------

Die Sandbox enthält eine einfache "Hello-World-Applikation" - und die nutzen
wir, um etwas über Symfony zu lernen. Rufe die folgende URL auf und du wirst
von Symfony begrüßt werden (ersetze Fabien durch deinen Vornamen):

    http://localhost/sandbox/web/index_dev.php/hello/Fabien

Wie funktioniert das? Lass uns die URL auseinander nehmen:

 * `index_dev.php`: Das ist der sogenannte Frontcontroller. Er ist der einzige
   Einstiegspunkt in die hello-Applikation und beantwortet alle Nutzeranfragen.

 * `/hello/Fabien`: Das ist der "virtuelle" Pfad zu der Ressource, die der
   Nutzer aufrufen möchte.

Deine Aufgabe als Entwickler ist es, den Code so zu schreiben, dass die Anfrage
des Nutzers (`/hello/Fabien`) mit der entsprechenden Ressource (`Hello Fabien!`)
verknüpft wird.

### Routing

Wie aber leitet Symfony die Anfrage auf deinen Code? Lesen wir doch einfach mal
in der Routing-Konfigurationsdatei:

    [yml]
    # hello/config/routing.yml
    homepage:
      pattern:  /
      defaults: { _bundle: WebBundle, _controller: Default, _action: index }

    hello:
      resource: HelloBundle/Resources/config/routing.yml

Die Datei ist in [YAML](http://www.yaml.org/) geschrieben, einem einfachen
Format, das die Beschreibung von Konfigurationsbeschreibungen sehr leicht
macht. Alle Konfigurationsdateien können in XML, YAML oder blankem PHP-Code
geschrieben werden. Dieses Tutorial nutzt das YAML-Format, da es sehr präzise
und gerade für Einsteiger leicht zu lesen ist. Natürlich würden "Profis"
vermutlich überall XML verwenden.

Die ersten drei Zeilen der Routing-Konfigurationsdate definieren, welcher
Code beim Aufruf von "`/`" aufzurufen ist. Interessanter ist für uns die
letzte Zeile, die nämlich die folgende Routing-Konfigurationsdatei einbindet:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index }

Das ist es! Wie man sieht, wird das Anfragemuster "`/hello/:name`" (mit
Doppelpunkt beginnende Zeichenketten wie z.B. `:name` dienen als Platzhalter)
mit einem Controller verknüpft, der durch die Werte `_bundle`, `_controller`,
und `_action` spezifiziert wird.

### Controller

Der Controller ist verantwortlich für das Ausliefern einer Repräsentation
der aufgerufenen Ressource (was in den meisten Fällen HTML sein sollte) und
wird als PHP-Klasse definiert:

    [php]
    # src/Application/HelloBundle/Controller/HelloController.php
    namespace Application\HelloBundle\Controller;

    use Symfony\Framework\WebBundle\Controller;

    class HelloController extends Controller
    {
      public function indexAction($name)
      {
        return $this->render('HelloBundle:Hello:index', array('name' => $name));
      }
    }

Der Code ist überschaubar, dennoch soll er Zeile für Zeile erklärt werden:

 * `namespace Application\HelloBundle\Controller;`: Symfony nutzt die
   Vorteile des neuen PHP 5.3, daher werden auch alle Controller sauber mit
   Namensräumen versehen (der Namensraum enthält den `_bundle`-Wert aus der
   Routing-Konfiguration: `HelloBundle`).

 * `class HelloController extends Controller`: Der Name des Controllers setzt
   sich zusammen aus dem `_controller`-Wert aus der Routing-Konfiguration und
   dem Wort `Controller`. Der Controller erweitert die eingebaute Klasse
   `Controller`, die (wie wir später in diesem Tutorial noch sehen werden)
   nützliche Shortcuts bereitstellt.

 * `public function indexAction($name)`: Jeder Controller besteht aus mehreren
   Actions. Entsprechend der Konfiguration wird die hello-Seite von der
   `index`-Action behandelt. Diese Methode erhält die oben Platzhalter der
   Ressource als Argumente (in unserem Fall ist das `$name`).

 * `return $this->render('HelloBundle:Hello:index', array('name' => $name));`:
   Die `render()`-Methode lädt und rendert das Template
   (`HelloBundle:Hello:index`) mit den Variablen, die sie als zweites Argument
   erhält.

Was aber ist ein Bundle? Sämtlicher Code, den man in einem Symfony-Projekt
schreibt, wird in Bundles organisiert. In Symfony bezeichnet ein Bundle eine
strukturierte Menge von Dateien (PHP-Dateien, Stylesheets, JavaScripts, Bilder,
...), die ein einziges Feature implementieren und das einfach mit anderen
Entwicklern ausgetauscht werden kann. In unserem Beispiel haben wir nur ein
Bundle, nämlich das `HelloBundle`.

### Templates

Nun also rendert der Controller das Template `HelloBundle:Hello:index`.
Wie aber setzt sich dieser Template-Name zusammen? `HelloBundle` ist der Name
des Bundles, `Hello` ist der Controller und `index` ist der Dateiname des
Templates. Das Template selbst besteht aus HTML und einfachen PHP-Ausdrücken:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    Hello <?php echo $name ?>!

Herzlichen Glückwunsch! Du hast nun die ersten Stücke Symfony-Code gesehen.
Das war doch gar nicht so schwer, oder? Mit Symfony kann man wirklich einfacher
und schneller Webseiten implementieren.


Umgebungen
----------

Nun verstehst du etwas besser, wie Symfony funktioniert; lass uns jetzt einen
Blick auf die Fußzeile der Website werfen, da wirst du eine schmale Leiste mit
Symfony- und PHP-Logos sehen. Diese Leiste nennt man die "Web Debug Toolbar"
und sie macht Entwicklern das Leben um einiges leichter. Natürlich darf sie auf
dem Produktivserver niemals zu sehen sein. Das erklärt auch, warum im
`web/`-Verzeichnis ein weiterer Frontcontroller liegt, der für die
Produktivumgebung gedacht ist:

    http://localhost/sandbox/web/index.php/hello/Fabien

Und wenn `mod_rewrite` installiert ist, dann kann selbst das `index.php` in der
URL weggelassen werden:

    http://localhost/sandbox/web/hello/Fabien

Nicht zuletzt sollte auf Produktivservern das `web/`-Verzeichnis als
Document Root eingestellt sein, um die Installation abzusichern, sodass wir nun
folgende, viel schönere URL erhalten:

    http://localhost/hello/Fabien

Um die Produktivumgebung so schnell wie möglich zu machen, betreibt Symfony
unter `hello/cache/` ein Cache-Verzeichnis. Wenn du Änderungen vornimmst, wirst
du die gecachten Dateien manuell entfernen müssen. Darum solltest du während
der Entwicklung immer den dafür vorgesehenen Frontcontroller (`index_dev.php`)
nutzen.

Abschließende Bemerkungen
-------------------------

Die 10 Minuten sind vorbei. Du solltest nun in der Lage sein, deine eigenen
einfachen Routen, Controller und Templates zu erstellen. Versuche doch zur
Übung etwas sinnvolleres als die Hello-Applikation zu entwickeln! Wenn du mehr
über Symfony lernen möchtest, kannst du einfach den nächsten Schritt dieses
Tutorials lesen, in dem wir tiefer in das Templating-System hineintauchen
werden.

[1]: http://symfony-reloaded.org/code#sandbox
