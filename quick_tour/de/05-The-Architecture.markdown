Eine schnelle Tour durch Symfony 2.0: Die Architektur
=====================================================

Mit den ersten vier Teilen dieses Tutorials hast du einen kurzen Einblick in
Symfony 2.0 erhalten. Aber bis jetzt gab es keinen genaueren Blick auf die
Standard-Verzeichnisstruktur eines Projektes. Da diese sich aber von denen
anderer Frameworks grundlegend unterscheidet, werden wir das nun nachholen.

Die Verzeichnisstruktur einer Symfony-Applikation ist äußerst flexibel.
Dieses Tutorial beschreibt die empfohlene Struktur, die sich jedoch - wie du
gleich sehen wirst - wie alles andere anpassen lässt.

Die Verzeichnisstruktur
-----------------------

Die Verzeichnisstruktur der Sandbox zeigt die typische und empfohlene Struktur
einer Symfony-Applikation:

 * `hello/`: Dieses Verzeichnis, welches nach deiner Applikation benannt wird,
   enthält die Konfigurationsdateien;

 * `src/`: In diesem Verzeichnis wird der PHP-Code abgelegt;

 * `web/`: Das ist dein Web-Verzeichnis.

### Das Web-Verzeichnis

Das Web-Verzeichnis ist der Ort, an dem alle öffentlichen und statischen
Dateien - wie Bilder, Stylesheets und JavaScript-Dateien - abgelegt werden.
Hier finden sich auch die Front-Controller:

    [php]
    # web/index.php
    <?php

    require_once __DIR__.'/../hello/HelloKernel.php';

    $kernel = new HelloKernel('prod', false);
    $kernel->run();

Wie jeder Front-Controller verwendet `index.php` eine Kernel-Klasse, in diesem
Fall `HelloKernel`, um die Applikation zu laden.

### Das Anwendungsverzeichnis

Die `HelloKernel`-Klasse ist der Haupteinstiegspunkt der
Anwendungskonfiguration und wird daher im `hello/`-Verzeichnis abgelegt.

Diese Klasse muss fünf Methoden implementieren:

  * `registerRootDir()`: Gibt das Wurzelverzeichnis der Konfiguration zurück;

  * `registerBundles()`: Liefert ein Array aller für den Start der Anwendung
    benötigten Bundles (mehr dazu in der Referenz zu `Application\HelloBundle\Bundle`);

  * `registerBundleDirs()`: Liefert ein Array, in dem die Namensräume ihren
    Verzeichnissen zugeordnet werden;

  * `registerContainerConfiguration()`: Gibt das Haupt-Konfigurationsobjekt
     zurück (dazu später mehr);

  * `registerRoutes()`: Liefert die Routing-Konfiguration;

Wirf einen Blick auf die Standardimplementierung dieser Methoden, um die
Flexibilität des Frameworks besser zu verstehen. Zu Beginn dieses Tutorials
hast du die `hello/config/routing.yml` geöffnet. Deren Pfad ist in
`registerRoutes()` konfiguriert:

    [php]
    public function registerRoutes()
    {
      $loader = new RoutingLoader($this->getBundleDirs());

      return $loader->load(__DIR__.'/config/routing.yml');
    }

Dies ist auch der Punkt, an dem du von YAML- auf XML- oder
PHP-Konfigurationsdateien wechseln kannst, je nachdem, was deinen Bedürfnissen
am besten entspricht.

Damit alles miteinander funktioniert, benötigt der Kernel zwei Dateien aus dem
`src/`-Ordner:

    [php]
    # hello/HelloKernel.php
    require_once __DIR__.'/../src/autoload.php';
    require_once __DIR__.'/../src/vendor/symfony/src/Symfony/Foundation/bootstrap.php';

### Der Source-Ordner

Die Datei `src/autoload.php` ist für das automatische Laden aller im
`src/`-Ordner liegenden Dateien verantwortlich:

    [php]
    # src/autoload.php
    require_once __DIR__.'/vendor/symfony/src/Symfony/Foundation/UniversalClassLoader.php';

    use Symfony\Foundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
      'Symfony'     => __DIR__.'/vendor/symfony/src',
      'Application' => __DIR__,
      'Bundle'      => __DIR__,
      'Doctrine'    => __DIR__.'/vendor/doctrine/lib',
    ));
    $loader->registerPrefixes(array(
      'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
      'Zend_'  => __DIR__.'/vendor/zend/library',
    ));
    $loader->register();

    // for Zend Framework & SwiftMailer
    set_include_path(__DIR__.'/vendor/zend/library'.PATH_SEPARATOR.__DIR__.'/vendor/swiftmailer/lib'.PATH_SEPARATOR.get_include_path());

Der `UniversalClassLoader` von Symfony wird genutzt um alle Dateien, die
entweder die [technische Interoperabilität][1] für PHP-5.3-Namespaces oder
die PEAR-[Namenskonventionen][2] für Klassen erfüllen, automatisch zu laden.
Alle Abhängigkeiten sind hier im `vendor/`-Verzeichnis abgelegt, das jedoch
ist nur eine Konvention. Du kannst sie speichern, wo immer du willst, global
auf dem Server oder lokal im Projekt.

Mehr über Bundles
-----------------
Wie wir im vorigen Teil gesehen haben, besteht eine Applikation aus den
Bundles, die in ihrer `registerBundles()`-Methode definiert sind:

    [php]
    # hello/HelloKernel.php
    public function registerBundles()
    {
      return array(
        new Symfony\Foundation\Bundle\KernelBundle(),
        new Symfony\Framework\WebBundle\Bundle(),
        new Symfony\Framework\DoctrineBundle\Bundle(),
        new Symfony\Framework\SwiftmailerBundle\Bundle(),
        new Symfony\Framework\ZendBundle\Bundle(),
        new Application\HelloBundle\Bundle(),
      );
    }

Woher aber weiß Symfony, wo es diese Bundles findet? In dieser Hinsicht ist
Symfony besonders flexibel. Die Methode `registerBundleDirs()` muss ein
assoziatives Array liefern, welches Namespaces ein gültiges (lokales oder
globales) Verzeichnis zuordnet:

    [php]
    public function registerBundleDirs()
    {
      return array(
        'Application'        => __DIR__.'/../src/Application',
        'Bundle'             => __DIR__.'/../src/Bundle',
        'Symfony\\Framework' => __DIR__.'/../src/vendor/symfony/src/Symfony/Framework',
      );
    }

Damit wird Symfony, wenn du das `HelloBundle` in einem Controller- oder
Templatenamen referenzierst, in dem angegebenen Verzeichnis nachschauen.

Verstehst du nun, warum Symfony so flexibel ist? Teile deine Bundles zwischen
Applikationen, speichere sie lokal oder global, ganz wie du willst.

Vendors
-------

In der Regel wird deine Applikation von Bibliotheken Dritter abhängen. Diese
sollten im Ordner `src/vendor/` abgelegt werden. Dieses Verzeichnis enthält
bereits die Symfony-Bibliotheken, die SwiftMailer-Bibliothek, das Doctrine-ORM
und eine Auswahl von Klassen aus dem Zend Framework.

Cache und Logs
--------------

Symfony ist wahrscheinlich eines der schnellsten Full-Stack-Frameworks. Wie
aber kann das sein, wenn es doch bei jedem Request zig YAML- und XML-Dateien
laden und interpretieren muss? Die Ursache dafür ist sein Cache-System. Die
Anwendungskonfiguration wird nur beim allerersten Request geladen und fertig
übersetzt als reiner PHP-Code im `cache/`-Anwendungsverzeichnis abgelegt.
In der Entwicklungsumgebung sorgt Symfony dafür, dass der Cache automatisch
aktualisiert wird, wenn eine Dtaei geändert wurde. Im Produktivbetrieb jedoch
solltest du darauf achten, dass du selbst den Cache leerst, sobald du deinen
Code oder die Konfiguration geändert hast.

Wenn man eine Webapplikation entwickelt, können viele Dinge schief gehen. Die
Logdateien im `logs/`-Verzeichnis werden dir alles über die Requests verraten,
so dass du Probleme schnell lösen kannst.

Die Kommandozeile
-----------------

Jede Applikation enthält auch ein Werkzeug für die Kommandozeile (`console`),
welches dir bei Entwicklung und Wartung deiner Applikation zur Seite steht. Es
stellt Kommandos bereit, die deine Produktivität erhöhen, indem es ermüdende
und sich wiederholende Aufgaben automatisiert.

Starte dieses Tool ohne Parameter um mehr über seine Fähigkeiten zu erfahren:

    $ php hello/console

Die Option `--help` beschreibt die richtige Verwendung eines Kommandos:

    $ php hello/console router:debug --help

Abschließende Gedanken
----------------------

Nenn mich verrückt, aber nachdem du diesen Teil gelesen hast, solltest du
wissen, wie die Dinge funktionieren und was Symfony für dich tun kann.
Nichts in Symfony wird dir im Weg stehen. Du kannst Verzeichnisse umbenennen
und verschieben, wie es dir sinnvoll erscheint.

Nun bist du nur noch einen Schritt davon entfernt, bis du ein Symfony-Meister
bist. Wirklich, nur ein Schritt liegt vor dir, der erklärt, wie das Framework
erweitert werden kann. Anschließend wirst du die gefragtesten Applikationen
mit Symfony entwickeln können.

[1]: http://groups.google.com/group/php-standards/web/psr-0-final-proposal
[2]: http://pear.php.net/
