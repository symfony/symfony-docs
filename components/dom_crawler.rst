.. index::
   single: DomCrawler

The DomCrawler Component
========================

    The DomCrawler Component eases DOM navigation for HTML and XML documents.

Installation
------------

You can install the component in many different ways:

* Use the official Git repository (https://github.com/symfony/DomCrawler);
* Install it via PEAR ( `pear.symfony.com/DomCrawler`);
* Install it via Composer (`symfony/dom-crawler` on Packagist).

Usage
-----

The :class:`Symfony\\Component\\DomCrawler\\Crawler` class provides methods
to query and manipulate HTML and XML documents.

An instance of the Crawler represents a set (:phpclass:`SplObjectStorage`)
of :phpclass:`DOMElement` objects, which are basically nodes that you can
traverse easily::

    use Symfony\Component\DomCrawler\Crawler;

    $html = <<<'HTML'
    <html>
        <body>
            <p class="message">Hello World!</p>
            <p>Hello Crawler!</p>
        </body>
    </html>
    HTML;

    $crawler = new Crawler($html);

    foreach ($crawler as $domElement) {
        print $domElement->nodeName;
    }

Specialized :class:`Symfony\\Component\\DomCrawler\\Link` and
:class:`Symfony\\Component\\DomCrawler\\Form` classes are useful for
interacting with html links and forms as you traverse through the HTML tree.

Node Filtering
~~~~~~~~~~~~~~

Using XPath expressions is really easy::

    $crawler = $crawler->filterXPath('descendant-or-self::body/p');

.. tip::

    ``DOMXPath::query`` is used internally to actually perform an XPath query.

Filtering is even easier if you have the ``CssSelector`` Component installed.
This allows you to use jQuery-like selectors to traverse::

    $crawler = $crawler->filter('body > p');

Anonymous function can be used to filter with more complex criteria::

    $crawler = $crawler->filter('body > p')->reduce(function ($node, $i) {
        // filter even nodes
        return ($i % 2) == 0;
    });

To remove a node the anonymous function must return false.

.. note::

    All filter methods return a new :class:`Symfony\\Component\\DomCrawler\\Crawler`
    instance with filtered content.

Node Traversing
~~~~~~~~~~~~~~~

Access node by its position on the list::

    $crawler->filter('body > p')->eq(0);

Get the first or last node of the current selection::

    $crawler->filter('body > p')->first();
    $crawler->filter('body > p')->last();

Get the nodes of the same level as the current selection::

    $crawler->filter('body > p')->siblings();

Get the same level nodes after or before the current selection::

    $crawler->filter('body > p')->nextAll();
    $crawler->filter('body > p')->previousAll();

Get all the child or parent nodes::

    $crawler->filter('body')->children();
    $crawler->filter('body > p')->parents();

.. note::

    All the traversal methods return a new :class:`Symfony\\Component\\DomCrawler\\Crawler`
    instance.

Accessing Node Values
~~~~~~~~~~~~~~~~~~~~~

Access the value of the first node of the current selection::

    $message = $crawler->filterXPath('//body/p')->text();

Access the attribute value of the first node of the current selection::

    $class = $crawler->filterXPath('//body/p')->attr('class');

Extract attribute and/or node values from the list of nodes::

    $attributes = $crawler->filterXpath('//body/p')->extract(array('_text', 'class'));

.. note::

    Special attribute ``_text`` represents a node value.

Call an anonymous function on each node of the list::

    $nodeValues = $crawler->filter('p')->each(function ($node, $i) {
        return $node->nodeValue;
    });

The anonymous function receives the position and the node as arguments.
The result is an array of values returned by the anonymous function calls.

Adding the Content
~~~~~~~~~~~~~~~~~~

The crawler supports multiple ways of adding the content::

    $crawler = new Crawler('<html><body /></html>');

    $crawler->addHtmlContent('<html><body /></html>');
    $crawler->addXmlContent('<root><node /></root>');

    $crawler->addContent('<html><body /></html>');
    $crawler->addContent('<root><node /></root>', 'text/xml');

    $crawler->add('<html><body /></html>');
    $crawler->add('<root><node /></root>');

As the Crawler's implementation is based on the DOM extension, it is also able
to interact with native :phpclass:`DOMDocument`, :phpclass:`DOMNodeList`
and :phpclass:`DOMNode` objects:

.. code-block:: php

    $document = new \DOMDocument();
    $document->loadXml('<root><node /><node /></root>');
    $nodeList = $document->getElementsByTagName('node');
    $node = $document->getElementsByTagName('node')->item(0);

    $crawler->addDocument($document);
    $crawler->addNodeList($nodeList);
    $crawler->addNodes(array($node));
    $crawler->addNode($node);
    $crawler->add($document);

Form and Link support
~~~~~~~~~~~~~~~~~~~~~

Special treatment is given to links and forms inside the DOM tree.

Links
.....

To find a link by name (or a clickable image by its ``alt`` attribute), use
the ``selectLink`` method on an existing crawler. This returns a Crawler
instance with just the selected link(s). Calling ``link()`` gives us a special
:class:`Symfony\\Component\\DomCrawler\\Link` object::

    $linksCrawler = $crawler->selectLink('Go elsewhere...');
    $link = $linksCrawler->link();

    // or do this all at once
    $link = $crawler->selectLink('Go elsewhere...')->link();

The :class:`Symfony\\Component\\DomCrawler\\Link` object has several useful
methods to get more information about the selected link itself::

    // return the raw href value
    $href = $link->getRawUri();

    // return the proper URI that can be used to make another request
    $uri = $link->getUri();

The ``getUri()`` is especially useful as it cleans the ``href`` value and
transforms it into how it should really be processed. For example, for a
link with ``href="#foo"``, this would return the full URI of the current
page suffixed with ``#foo``. The return from ``getUri()`` is always a full
URI that you can act on.

Forms
.....

Special treatment is also given to forms. A ``selectButton()`` method is
available on the Crawler which returns another Crawler that matches a button
(``input[type=submit]``, ``input[type=image]``, or a ``button``) with the
given text. This method is especially useful because you can use it to return
a :class:`Symfony\\Component\\DomCrawler\\Form` object that represents the
form that the button lives in::

    $form = $crawler->selectButton('validate')->form();

    // or "fill" the form fields with data
    $form = $crawler->selectButton('validate')->form(array(
        'name' => 'Ryan',
    ));

The :class:`Symfony\\Component\\DomCrawler\\Form` object has lots of very
useful methods for working with forms:

    $uri = $form->getUri();

    $method = $form->getMethod();

The :method:`Symfony\\Component\\DomCrawler\\Form::getUri` method does more
than just return the ``action`` attribute of the form. If the form method
is GET, then it mimics the browser's behavior and returns the ``action``
attribute followed by a query string of all of the form's values.

You can virtually set and get values on the form::

    // set values on the form internally
    $form->setValues(array(
        'registration[username]' => 'symfonyfan',
        'registration[terms]'    => 1,
    ));

    // get back an array of values - in the "flat" array like above
    $values = $form->getValues();

    // returns the values like PHP would see them, where "registration" is its own array
    $values = $form->getPhpValues();

To work with multi-dimensional fields::

    <form>
        <input name="multi[]" />
        <input name="multi[]" />
        <input name="multi[dimensional]" />
    </form>

You must specify the fully qualified name of the field::

    // Set a single field
    $form->setValue('multi[0]', 'value');

    // Set multiple fields at once
    $form->setValue('multi', array(
        1             => 'value',
        'dimensional' => 'an other value'
    ));

This is great, but it gets better! The ``Form`` object allows you to interact
with your form like a browser, selecting radio values, ticking checkboxes,
and uploading files::

    $form['registration[username]']->setValue('symfonyfan');

    // check or uncheck a checkbox
    $form['registration[terms]']->tick();
    $form['registration[terms]']->untick();

    // select an option
    $form['registration[birthday][year]']->select(1984);

    // select many options from a "multiple" select or checkboxes
    $form['registration[interests]']->select(array('symfony', 'cookies'));

    // even fake a file upload
    $form['registration[photo]']->upload('/path/to/lucas.jpg');

What's the point of doing all of this? If you're testing internally, you
can grab the information off of your form as if it had just been submitted
by using the PHP values::

    $values = $form->getPhpValues();
    $files = $form->getPhpFiles();

If you're using an external HTTP client, you can use the form to grab all
of the information you need to create a POST request for the form::

    $uri = $form->getUri();
    $method = $form->getMethod();
    $values = $form->getValues();
    $files = $form->getFiles();

    // now use some HTTP client and post using this information

One great example of an integrated system that uses all of this is `Goutte`_.
Goutte understands the Symfony Crawler object and can use it to submit forms
directly::

    use Goutte\Client;

    // make a real reqeust to an external site
    $client = new Client();
    $crawler = $client->request('GET', 'https://github.com/login');

    // select the form and fill in some values
    $form = $crawler->selectButton('Log in')->form();
    $form['login'] = 'symfonyfan';
    $form['password'] = 'anypass';

    // submit that form
    $crawler = $client->submit($form);

.. _`Goutte`: https://github.com/fabpot/goutte
