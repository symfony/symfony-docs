.. index::
   single: DomCrawler

The DomCrawler Component
========================

    DomCrawler Component eases DOM navigation for HTML and XML documents.

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

Instance of the Crawler represents a set (:phpclass:`SplObjectStorage`)
of :phpclass:`DOMElement` objects:

.. code-block:: php

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

More specialized :class:`Symfony\\Component\\DomCrawler\\Link` and
:class:`Symfony\\Component\\DomCrawler\\Form` classes are useful for
interacting with html links and forms.

Node Filtering
~~~~~~~~~~~~~~

Using XPath expressions is really simplified:

.. code-block:: php

    $crawler = $crawler->filterXPath('descendant-or-self::body/p');

.. tip::

    :phpmethod:`DOMXPath::query` is used internally to actually perform
    an XPath query.

Filtering is even easier if you have CssSelector Component installed:

.. code-block:: php

    $crawler = $crawler->filter('body > p');

Anonymous function can be used to filter with more complex criteria:

.. code-block:: php

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

Access node by its position on the list:

.. code-block:: php

    $crawler->filter('body > p')->eq(0);

Get the first or last node of the current selection:

.. code-block:: php

    $crawler->filter('body > p')->first();
    $crawler->filter('body > p')->last();

Get the nodes of the same level as the current selection:

.. code-block:: php

    $crawler->filter('body > p')->siblings();

Get the same level nodes after or before the current selection:

.. code-block:: php

    $crawler->filter('body > p')->nextAll();
    $crawler->filter('body > p')->previousAll();

Get all the child or parent nodes:

.. code-block:: php

    $crawler->filter('body')->children();
    $crawler->filter('body > p')->parents();

.. note::

    All the traversal methods return a new :class:`Symfony\\Component\\DomCrawler\\Crawler`
    instance.

Accessing Node Values
~~~~~~~~~~~~~~~~~~~~~

Access the value of the first node of the current selection:

.. code-block:: php

    $message = $crawler->filterXPath('//body/p')->text();

Access the attribute value of the first node of the current selection:

.. code-block:: php

    $class = $crawler->filterXPath('//body/p')->attr('class');

Extract attribute and/or node values from the list of nodes:

.. code-block:: php

    $attributes = $crawler->filterXpath('//body/p')->extract(array('_text', 'class'));

.. note:: Special attribute ``_text`` represents a node value.

Call an anonymous function on each node of the list:

.. code-block:: php

    $nodeValues = $crawler->filter('p')->each(function ($node, $i) {
        return $node->nodeValue;
    });

The anonymous function receives the position and the node as arguments.
Result is an array of values returned by anonymous function calls.

Adding the Content
~~~~~~~~~~~~~~~~~~

Crawler supports multiple ways of adding the content:

.. code-block:: php

    $crawler = new Crawler('<html><body /></html>');

    $crawler->addHtmlContent('<html><body /></html>');
    $crawler->addXmlContent('<root><node /></root>');

    $crawler->addContent('<html><body /></html>');
    $crawler->addContent('<root><node /></root>', 'text/xml');

    $crawler->add('<html><body /></html>');
    $crawler->add('<root><node /></root>');

As Crawler's implementation is based on the DOM extension it is also able
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

todo:

* selectLink()
* selectButton()
* link()
* links()
* form()
