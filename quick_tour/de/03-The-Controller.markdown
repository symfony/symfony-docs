Eine schnelle Tour durch Symfony 2.0: Der Controller
====================================================

Du bist auch nach den ersten beiden Teilen dabei? Du wirst langsam
Symfony-süchtig! Dann lass uns nun im dritten Teil kurzerhand erfahren, wie uns
Controller helfen können.

Formate
-------

Heutzutage sollte eine Webapplikation mehr können als nur HTML-Seiten
auszuliefern. Von XML für RSS-Feeds oder Webservices über JSON für
AJAX-Requests bis hin zu vielen weiteren Formaten, aus denen man wählen kann
und die von Symfony einfach unterstützt werden. Öffne die `routing.yml` und
füge einen Eintrag `_format` mit dem Wert `xml` hinzu:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index, _format: xml }

Anschließend schreibe ein Template `index.xml.php` und lege es neben die
`index.php`:

    [xml]
    # src/Application/HelloBundle/Resources/views/Hello/index.xml.php
    <hello>
      <name><?php echo $name ?></name>
    </hello>

Das war es schon. Der Controller muss nicht angefasst werden. Für
Standardformate wählt Symfony automatisch den besten `Content-Type`-Header für
die Response. Möchtest du mehrere verschiedene Formate für eine einzige Action
haben, so nutze stattdessen den Platzhalter `:_format`:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:      /hello/:name.:_format
      defaults:     { _bundle: HelloBundle, _controller: Hello, _action: index, _format: html }
      requirements: { _format: (html|xml|json) }

Der Controller wird nun für URLs wie `/hello/Fabien.xml` oder
`/hello/Fabien.json` aufgerufen. Da der Standardwert für `_format` `html` ist,
werden sowohl `/hello/Fabien` als auch `/hello/Fabien.html` das Format `html`
liefern.

Die `requirements`-Angabe definiert einen regulären Ausdruck, auf den die
Platzhalter passen müssen. In diesem Beispiel wirst du einen 404-HTTP-Fehler
bekommen, wenn du versuchst `/hello/Fabien.js` aufzurufen, da die
`_route`-Bedingung nicht erfüllt wird.

Das Response-Objekt
-------------------

Lass uns nun zum `Hello`-Controller zurückkehren:

    [php]
    public function indexAction($name)
    {
      return $this->render('HelloBundle:Hello:index', array('name' => $name));
    }

Die `render()`-Methode rendert ein Template und liefert ein `Response`-Objekt
Die Antwort kann noch optimiert werden, bevor sie an den Browser gesendet wird,
beispielsweise indem der `Content-Type` geändert wird:

    [php]
    public function indexAction($name)
    {
      $response = $this->render('HelloBundle:Hello:index', array('name' => $name));
      $response->setHeader('Content-Type', 'text/plain');

      return $response;
    }

Für ein einfaches Template kannst du das `Response`-Objekt auch per Hand anlegen
und ein paar Millisekunden sparen:

    [php]
    public function indexAction($name)
    {
      return $this->createResponse('Hello '.$name);
    }

Das ist besonders praktisch, wenn ein Controller - zum Beispiel für einen
AJAX-Request - per JSON antworten soll.

Fehlerbehandlung
----------------

Wenn etwas nicht gefunden wurde, sollte man dem HTTP-Protokoll folgen und
eine 404-Antwort liefern. Das kann einfach durch Werfen der eingebauten
HTTP-Exception passieren:

    [php]
    use Symfony\Components\RequestHandler\Exception\NotFoundHttpException;

    public function indexAction()
    {
      $product = // retrieve the object from database
      if (!$product)
      {
        throw new NotFoundHttpException('The product does not exist.');
      }

      return $this->render(...);
    }

Die `NotFoundHttpException` wird eine 404-HTTP-Antwort an den Browser zurück
geben. Entsprechend liefert die `ForbiddenHttpException` einen 403-Fehler
und die `UnauthorizedHttpException` eine 401. Für jeden anderen HTTP-Fehlercode
kann die Basis-`HttpException` genutzt und der HTTP-Fehler als Exception-Code
übergeben werden:

    [php]
    throw new HttpException('Unauthorized access.', 401);

Um- und Weiterleitung
---------------------

Wenn man den Nutzer auf eine andere Seite umleiten möchte, nutzt man
einfach die `redirect()`-Methode:

    [php]
    $this->redirect($this->generateUrl('hello', array('name' => 'Lucas')));

Hinter `generateUrl()` verbirgt sich im Übrigen nichts anderes als die
`generate()`-Methode, die wir bereits beim `router`-Helper verwendet haben.
Sie nimmt den Routennamen sowie ein Array von Parametern entgegen und gibt die
zugehörige, menschenlesbare URL zurück.

Ebenso einfach ist die Weiterleitung von einer Action auf eine andere, was mit
der `forward()`-Methode erfolgt. Wie der `$view->actions`-Helper wird ein
interner Subrequest durchgeführt, als Ergebnis erhält man jedoch ein
Response-Objekt, was nach Belieben modifiziert werden kann:

    [php]
    $response = $this->forward('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green'));

    // do something with the response or return it directly

Das Request-Objekt
------------------

Der Controller erhält nicht nur Zugriff auf die Routing-Platzhalter, sondern
auch auf das gesamte `Request`-Objekt:

    [php]
    $request = $this->getRequest();

    $request->isXmlHttpRequest(); // is it an Ajax request?

    $request->getPreferredLanguage(array('en', 'fr'));

    $request->getQueryParameter('page'); // get a $_GET parameter

    $request->getRequestParameter('page'); // get a $_POST parameter

Auch im Template kann mittels `request`-Helpter einfach auf das
`Request`-Objekt zugegriffen werden:

    [php]
    <?php echo $view->request->getParameter('page') ?>

Abschließende Gedanken
----------------------

Nun ist schon alles gesagt, wahrscheinlich sind noch nicht einmal 10 Minuten
um. Im vergangenen Teil haben wir gesehen, wie das Templatingsystem durch die
Helper erweitert werden kann. Dank Bundles ist das auch beim Controller möglich
- das wird das Thema im nächsten Abschnitt dieses Tutorials sein.
