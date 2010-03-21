Eine schnelle Tour durch Symfony 2.0: Der View
==============================================


Templates gestalten
-------------------

Häufig enthalten die Templates eines Projektes globale Elemente, so zum Beispiel
Header und Footer. In Symfony wird dieses Problem anders gehandhabt: Templates
können durch andere Templates gefüllt werden.

Schauen wir uns doch einmal die Datei `layout.php`an:

    [php]
    # src/Application/HelloBundle/Resources/views/layout.php
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      </head>
      <body>
        <?php $view->slots->output('_content') ?>
      </body>
    </html>

Das `index`-Template wird durch die `layout.php` gefüllt. Das wird durch den
Aufruf von `extend()` ermöglicht:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    Hello <?php echo $name ?>!

Die Notation `HelloBundle::layout` kommt uns durchaus bekannt vor, oder? Es ist
die gleiche Notation, die genutzt wird, um ein Template zu referenzieren. Der
Teil `::` bedeutet einfach, dass der Controller leer ist und die entsprechende
Datei direkt in `views/` liegt.

Der Ausdruck `$view->slots->output('_content')` wird durch den Inhalt des
Kindtemplates ersetzt, in diesem Fall durch die `index.php` (mehr dazu im
nächsten Abschnitt).

Wie man sieht, stellt Symfony eine Methode auf einem mysteriösen `$view`-Objekt
zur Verfügung. Im Template bezieht sich `$view` auf ein spezielles Objekt, das
ein Bündel von Methoden und Eigenschaften bereitstellt, die Template-Engine
ausmachen.

Symfony unterstützt mehrere Ebenen der Gestaltung: Ein Layout kann durch ein
anderes gestaltet werden. Diese Technik ist insbesondere für große Projekte
nützlich und entfaltet ihre volle Kraft, wenn sie in Kombination mit Slots
verwendet wird.

Slots
-----
Was ist ein Slot? Ein Slot ist ein Codeschnipsel, der in einem Template
definiert wird und für die Gestaltung von Templates wiederverwendet werden
kann. Wir werden nun im index-Template einen `title`-Slot definieren:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php $view->slots->set('title', 'Hello World app') ?>

    Hello <?php echo $name ?>!

Und nun verändern wir das Layout so, dass der Titel im Header ausgegeben wird:

    [php]
    # src/Application/HelloBundle/Resources/views/layout.php
    <html>
      <head>
        <title><?php $view->slots->output('title', 'Default Title') ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      </head>
      <body>
        <?php $view->slots->output('_content') ?>
      </body>
    </html>

Die `output()`-Methode fügt den Inhalt eines Slots ein und nimmt einen
Standardwert für den Fall entgegen, dass der Slot nicht definiert ist.
Der String `_content` bezeichnet einen speziellen Slot, der das gerenderte
Kindtemplate enthält.

Für große Slots gibt es auch eine erweiterte Syntax:

    [php]
    <?php $view->slots->start('title') ?>
      Some large amount of HTML
    <?php $view->slots->stop() ?>

Einbinden anderer Templates
---------------------------

Die einfachste Art, ein Codeschnipsel in mehreren verschiedenen Templates
gemeinsam zu verwenden, ist das Definieren eines Templates, das in andere
eingebunden werden kann.

Legen wir also ein `hello.php`-Template an:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/hello.php
    Hello <?php echo $name ?>!

Und nun binden wir dieses in das `index.php`-Template ein:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $view->render('HelloBundle:Hello:hello', array('name' => $name)) ?>

Die `render()`-Methode wertet den Inhalt eines anderen Templates aus und gibt
ihn zurück (Das ist die gleiche Methode, die auch im Controller verwendet wird).

Einbinden anderer Actions
-------------------------

Was aber tun, wenn man das Ergebnis einer anderen Action in einem Template
ausgeben möchte? Dieser Fall tritt häufig ein, wenn man mit Ajax arbeitet
oder wenn im eingebetteten Template Variablen verwendet werden sollen, die
das Haupttemplate nicht enthält.

Hast du eine `fancy` Action und möchtest sie in das `index`-Template
einbinden, so verwendest du einfach den folgenden Code:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->actions->output('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green')) ?>

Hier bezieht sich die Zeichenkette `HelloBundle:Hello:fancy` auf die
`fancy`-Action des `Hello`-Controllers:

    [php]
    # src/Application/HelloBundle/Controller/HelloController.php
    class HelloController extends Controller
    {
      public function fancyAction($name, $color)
      {
        // create some object, based on the $color variable
        $object = ...;

        return $this->render('HelloBundle:Hello:fancy', array('name' => $name, 'object' => $object));
      }

      // ...
    }

Du solltest bedenken, dass diese Technik zwar sehr kraftvoll ist, andererseits
aber die Geschwindigkeit senken kann, da intern ein Unterrequest ausgeführt
wird. Daher sollten - wenn möglich - schnellere Alternativen eingesetzt werden.

Wo aber ist die Eigenschaft `$view->actions` definiert? Ähnlich wie bei
`$view->slots` handelt es sich um einen der Template-Helper, über die wir im
nächsten Abschnitt mehr erfahren werden.

Template-Helper
---------------

Das Symfony-Templating-System kann einfach durch Helper erweitert werden.
Helper sind PHP-Objekte, die nützliche Features im Template-Kontext
bereitstellen. `actions` und `slots` sind nichts anderes als zwei solcher
eingebauten Symfony-Helper.

### Links zwischen Seiten

Wann immer es um Webapplikationen geht, ist das Erzeugen von Links eine
Basisfunktionalität. Anstatt URLs hart im Template zu kodieren, nutzt man
den `router`-Helper, der die Links basierend auf der Router-Konfiguration
erzeugt. Auf diese Weise können all deine URLs einfach über die Konfiguration
verwaltet werden.

    [php]
    <a href="<?php echo $view->router->generate('hello', array('name' => 'Thomas')) ?>">
      Greet Thomas!
    </a>

Die `generate()`-Methode erhält den Routennamen und ein Array aus Werten
als Argumente. Der Routenname ist der Schlüssel, mit dem Routen referenziert
werden. Die Werte sollten mindestens die in der Routendefinition verwendeten
Platzhalter abdecken:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello: # The route name
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index }

### Assets verwenden: Bilder, JavaScript und Stylesheets

Was wäre das Internet ohne Bilder, JavaScript und Stylesheets? Symfony
stellt gleich drei Helper dafür bereit: `assets`, `javascripts` und
`stylesheets`.

    [php]
    <link href="<?php echo $view->assets->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

    <img src="<?php echo $view->assets->getUrl('images/logo.png') ?>" />

Der große Vorteil des `assets`-Helpers ist, dass er deine Applikation portierbar
macht. Somit kannst du das Rootverzeichnis der Applikation im Webrootverzeichnis
verschieben ohne dass irgendetwas an deinem Template-Code zu ändern ist.

Genauso einfach lassen sich die Stylesheets und Javascripte mit den Helpern
`stylesheets` und `javascripts` verwalten:

    [php]
    <?php $view->javascripts->add('js/product.js') ?>
    <?php $view->stylesheets->add('css/product.css') ?>

Die `add()`-Methode definiert Abhängigkeiten. Um diese Assets auszugeben musst
du aber noch folgenden Code im Haupt-Layout ausgeben:

    [php]
    <?php echo $view->javascripts ?>
    <?php echo $view->stylesheets ?>

Abschließende Bemerkungen
-------------------------

Das Templating-System von Symfony ist einfach und kraftvoll. Dank Layouts,
Slots, Templating und Action-Einbettungen ist es sehr einfach, die Templates
in logischer und zugleich erweiterbarer Art und Weise zu organisieren.
Später wirst du lernen, wie das Standardverhalten des Templating-Systems
angepasst und durch neue Helper erweitert werden kann.

Nun arbeitest du gerade einmal 20 Minuten mit Symfony und kannst schon jede
Menge lustige Sachen damit tun. Das ist Symfonys Stärke. Es ist einfach, die
Grundlagen zu erlernen, bald wirst du sehen, dass dieser Einfachheit eine
äußerst flexible Architektur zugrunde liegt.

Oh, ich greife wohl schon wieder zu weit vor. Im nächsten Schritt wirst du
nun erst einmal etwas über den Controller lernen müssen - und genau das ist
auch das Thema des nächsten Teiles dieses Tutorials. Bist du bereit für weitere
10 Minuten mit Symfony?
