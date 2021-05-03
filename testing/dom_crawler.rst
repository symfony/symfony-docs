.. index::
   single: Tests; Crawler

The DOM Crawler
===============

A Crawler instance is returned each time you make a request with the Client.
It allows you to traverse HTML or XML documents: select nodes, find links
and forms, and retrieve attributes or contents.

Traversing
----------

Like jQuery, the Crawler has methods to traverse the DOM of an HTML/XML
document. For example, the following finds all ``input[type=submit]`` elements,
selects the last one on the page, and then selects its immediate parent element::

    $newCrawler = $crawler->filter('input[type=submit]')
        ->last()
        ->parents()
        ->first()
    ;

Many other methods are also available:

``filter('h1.title')``
    Nodes that match the CSS selector.
``filterXpath('h1')``
    Nodes that match the XPath expression.
``eq(1)``
    Node for the specified index.
``first()``
    First node.
``last()``
    Last node.
``siblings()``
    Siblings.
``nextAll()``
    All following siblings.
``previousAll()``
    All preceding siblings.
``parents()``
    Returns the parent nodes.
``children()``
    Returns children nodes.
``reduce($lambda)``
    Nodes for which the callable does not return false.

Since each of these methods returns a new ``Crawler`` instance, you can
narrow down your node selection by chaining the method calls::

    $crawler
        ->filter('h1')
        ->reduce(function ($node, $i) {
            if (!$node->attr('class')) {
                return false;
            }
        })
        ->first()
    ;

.. tip::

    Use the ``count()`` function to get the number of nodes stored in a Crawler:
    ``count($crawler)``

Extracting Information
----------------------

The Crawler can extract information from the nodes::

    // returns the attribute value for the first node
    $crawler->attr('class');

    // returns the node value for the first node
    $crawler->text();

    // returns the default text if the node does not exist
    $crawler->text('Default text content');

    // pass TRUE as the second argument of text() to remove all extra white spaces, including
    // the internal ones (e.g. "  foo\n  bar    baz \n " is returned as "foo bar baz")
    $crawler->text(null, true);

    // extracts an array of attributes for all nodes
    // (_text returns the node value)
    // returns an array for each element in crawler,
    // each with the value and href
    $info = $crawler->extract(['_text', 'href']);

    // executes a lambda for each node and return an array of results
    $data = $crawler->each(function ($node, $i) {
        return $node->attr('href');
    });
