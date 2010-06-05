A Quick Tour of Symfony 2.0: The Bundles
========================================

You are my hero! Who would have thought that you would still be here after the
first three parts? Your efforts will be well rewarded soon. This part starts
to scratch the surface of one of the greatest and more powerful features of
Symfony, its bundle system.

The Bundle System
-----------------

A bundle is kind of like a plugin in other software. But why is it called
bundle and not plugin then? Because everything is a bundle in Symfony, from
the core framework features to the code you write for your application.
Bundles are first-class citizens in Symfony. This gives you the flexibility to
use pre-built features packaged in third-party bundles or to distribute your
own bundles. It makes it so easy to pick and choose which features to enable
in your application and optimize them the way you want.

An application is made up of bundles as defined in the `registerBundles()`
method of the `HelloKernel` class:

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

Along side the `HelloBundle` we have already talked about, notice that the
kernel also enables `KernelBundle`, `WebBundle`, `DoctrineBundle`,
`SwiftmailerBundle`, and `ZendBundle`. They are all part of the core
framework.

Each bundle can be customized via configuration files written in YAML or XML.
Have a look at the default configuration:

    [yml]
    # hello/config/config.yml
    kernel.config: ~
    web.web: ~
    web.templating: ~

Each entry like `kernel.config` defines the configuration of a bundle. Some
bundles can have several entries if they provide many features like
`WebBundle`, which has two entries: `web.web` and `web.templating`.

Each environment can override the default configuration by providing a
specific configuration file:

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

Now that you know how to enable bundles and how to configure them, let's see
what the built-in bundles can do for you.

The User
--------

Even if the HTTP protocol is stateless, Symfony provides a nice user object
that represents the client (be it a real person using a browser, a bot, or a
web service). Between two requests, Symfony stores the attributes in a cookie
by using the native PHP sessions.

This feature is provided by `WebBundle` and it can be enabled by adding the
following line to `config.yml`:

    [yml]
    # hello/config/config.yml
    web.user: ~

Storing and retrieving information from the user can be easily achieved from
any controller:

    [php]
    // store an attribute for reuse during a later user request
    $this->getUser()->setAttribute('foo', 'bar');

    // in another controller for another request
    $this->getUser()->getAttribute('foo');

    // get/set the user culture
    $this->getUser()->setCulture('fr');

You can also store small messages that will only be available for the very
next request:

    [php]
    $this->getUser()->setFlash('notice', 'Congratulations, your action succeeded!')

Accessing the Database
----------------------

If your project relies on a database in one way or another, feel free to
choose any tool you want. You can even use an ORM like Doctrine or Propel if
you want to abstract the database. But in this section, we will keep things
simple and use the Doctrine DBAL, a thin layer on top of PDO, to connect to
the database.

Enable the `DoctrineBundle` and configure your connection in `config.yml` by
adding the following lines:

    # hello/config/config.yml
    doctrine.dbal:
      driver:   PDOMySql # can be any of OCI8, PDOMsSql, PDOMySql, PDOOracle, PDOPgSql, or PDOSqlite
      dbname:   your_db_name
      user:     root
      password: your_password # or null if there is none

That's all there is to it. You can now use a connection object to interact
with the database from any action:

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

The `$this->getDatabaseConnection()` expression returns an object that works
like the PDO one, based on the configuration of `config.yml`.

Sending Emails
--------------

Sending emails with Symfony is a snap. First, enable the `SwiftmailerBundle`
and configure how you want them to be sent:

    # hello/config/config.yml
    swift.mailer:
      transport: gmail # can be any of smtp, mail, sendmail, or gmail
      username:  your_gmail_username
      password:  your_gmail_password

Then, use the mailer from any action:

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

The email body is stored in a template, rendered with the `renderView()`
method.

Final Thoughts
--------------

This part wraps up discovering the basic features of Symfony. Play with
Symfony, develop small applications with it, and when you feel comfortable
enough, resume your tour of Symfony with the next part, where we talk more
about how Symfony works, and how to configure it to fit your needs.
