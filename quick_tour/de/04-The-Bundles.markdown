Eine schnelle Tour durch Symfony 2.0: Die Bundles
=================================================

Du bist mein Held! Wer hätte gedacht, dass du auch nach den ersten drei Teilen
noch dabei sein wirst? Deine Mühen werden sich schon bald lohnen. Dieser Teil
wird an der Oberfläche des großartigsten und leistungsfähigsten Features von
Symfony kratzen: dem Bundle-System.

Das Bundle-System
-----------------

Ein Bundle ist vergleichbar mit Plugins bei anderer Software. Warum wird es
dann aber Bundle genannt? Weil alles in Symfony ein Bundle ist - vom
Core-Framework bis hin zu dem Code, den du für deine eigene Anwendung
schreibst. Bundles stehen bei Symfony an erster Stelle. Sie geben dir die
Flexibilität vorgefertigte Features von Dritten zu übernehmen oder aber deine
eigenen zu verbreiten. Sie erlauben eine gezielte Auswahl von Features und
deren Optimierung auf den gewünschten Einsatzzweck.

Anwendungen bestehen aus Bundles. Welche das sind, wird in `registerBundles()`
in unserer `HelloKernel`-Klasse angegeben:

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

Neben dem `HelloBundle`, über das wir bereits gesprochen haben, werden also
auch `KernelBundle`, `WebBundle`, `DoctrineBundle`, `SwiftmailerBundle` und
`ZendBundle` eingebunden. Diese Bundles sind allesamt Teile des Frameworks.

Jedes Bundle kann über YAML- oder XML-Konfigurationsdateien angepasst werden.
Schauen wir uns mal die Standardkonfiguration an:

    [yml]
    # hello/config/config.yml
    kernel.config: ~
    web.web: ~
    web.templating: ~

Jeder Eintrag - wie zum Beispiel `kernel.config` - definiert die Konfiguration
eines Bundles. Manche Bundles können mehrere Einträge haben, wenn sie mehrere
Features bereitstellen - so wie hier das `WebBundle`, welches zwei Einträge
hat: `web.web` und `web.templating`.

Jede Umgebung kann die Standardkonfiguration überschreiben, indem sie eine
eigene Konfigurationsdatei bereitstellt:

    [yml]
    # hello/config/config_dev.yml
    imports:
      - { resource: config.yml }

    web.debug:
      exception: %kernel.debug%
      toolbar:   %kernel.debug%

    zend.logger:
      priority: info
      path:     %kernel.root_dir%/logs/%kernel.environment%.log

Nun weißt du, wie Bundles aktiviert und konfiguriert werden. Im nächsten
Schritt schauen wir uns an, was die mitgelieferten Bundles bereitstellen.

Der Benutzer
------------

Obwohl HTTP statuslos ist, stellt Symfony ein praktisches User-Objekt bereit,
welches den Client darstellt (ganz egal, ob der Client nun eine echte Person
mit Browser ist, ein Robot oder ein Webservice). Zwischen den Requests
speichert Symfony die Attribute in einem Cookie, indem die ganz normalen
PHP-Sessions genutzt werden.

Dieses Feature wird vom `WebBundle` bereitgestellt und kann durch die folgende
Zeile in der `config.yml` aktiviert werden:

    [yml]
    # hello/config/config.yml
    web.user: ~

Das Speichern und Abrufen von Benutzerinformationen kann einfach durch einen
beliebigen Controller erfolgen:

    [php]
    // store an attribute for reuse during a later user request
    $this->getUser()->setAttribute('foo', 'bar');

    // in another controller for another request
    $this->getUser()->getAttribute('foo');

    // get/set the user culture
    $this->getUser()->setCulture('fr');

Es lassen sich auch kleine Nachrichten speichern, die nur beim nächsten Request
verfügbar sind:

    [php]
    $this->getUser()->setFlash('notice', 'Congratulations, your action succeeded!')

Die Datenbank nutzen
--------------------

Wenn dein Projekt auf irgendeine Weise von einer Datenbank abhängt, so hast du
die freie Auswahl. Du kannst ein ORM wie Doctrine oder Propel nutzen, wenn du
die Datenbank abstrahieren möchtest. In diesem Abschnitt beschränken wir uns
jedoch auf die Doctrine DBAL, einer dünnen Schicht auf dem PDO.

Dazu muss das `DoctrineBundle` durch Hinzufügen einiger Zeilen in der
`config.xml` aktiviert und konfiguriert werden:

    # hello/config/config.yml
    doctrine.dbal:
      driver:   PDOMySql # can be any of OCI8, PDOMsSql, PDOMySql, PDOOracle, PDOPgSql, or PDOSqlite
      dbname:   your_db_name
      user:     root
      password: your_password # or null if there is none

Und schon sind wir fertig. Nun kannst du über ein Connection-Objekt in jeder
Action auf die Datenbank zugreifen:

    [php]
    public function showAction($id)
    {
      $stmt = $this->getDatabaseConnection()->execute('SELECT * FROM product WHERE id = ?', array($id));

      if (!$product = $stmt->fetch())
      {
        throw new NotFoundHttpException('The product does not exist.');
      }

      return $this->render(...);
    }

Der Ausdruck `$this->getDatabaseConnection()` gibt ein Objekt zurück, das sich,
basierend auf der Konfiguration der `config.yml`, wie ein PDO-Objekt verhält.

E-Mails senden
--------------

Mit Symfony E-Mails versenden ist ein Klacks. Zunächst aktiviert und
konfiguriert man das `SwiftmailerBundle` so, wie es senden soll:

    # hello/config/config.yml
    swift.mailer:
      transport: gmail # can be any of smtp, mail, sendmail, or gmail
      username:  your_gmail_username
      password:  your_gmail_password

Anschließend kann der Mailer in jeder beliebigen Action genutzt werden:

    [php]
    public function indexAction($name)
    {
      // get the mailer first (mandatory to initialize Swift Mailer)
      $mailer = $this->getMailer();

      $message = \Swift_Message::newInstance()
        ->setSubject('Hello Email')
        ->setFrom('send@example.com')
        ->setTo('recipient@example.com')
        ->setBody($this->renderView('HelloBundle:Hello:email', array('name' => $name)))
      ;
      $mailer->send($message);

      return $this->render(...);
    }

Der Mailtext wird in einem Template gespeichert, welches mit `renderView()`
gerendert wird.

Abschließende Gedanken
----------------------

In diesem Teil haben wir grundlegende Features von Symfony kennengelernt.
Spiele mit Symfony, entwickle kleine Anwendungen und sobald du dich wohlfühlst,
setze deine Symfony-Tour mit dem nächsten Teil fort. Dann werden wir darüber
sprechen, wie Symfony funktioniert und wie du es deinem Bedarf entsprechend
konfigurierst.

