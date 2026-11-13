The DOM Crawler
===============

A Crawler instance is returned each time you make a request with the Client.
It allows you to traverse HTML or XML documents: select nodes, find
:ref:`links <testing-links>` and :ref:`forms <testing-forms>`, and retrieve
attributes or contents.

Traversing
----------

Like jQuery, the Crawler has methods to traverse the DOM of an HTML/XML
document. For example, the following finds all ``input[type=submit]`` elements,
selects the last one on the page, and then selects its immediate ancestor element::

    $newCrawler = $crawler->filter('input[type=submit]')
        ->last()
        ->ancestors()
        ->first()
    ;

Many other methods are also available:

``filter('h1.title')``
    Finds nodes that match the given CSS selector (which must be supported by
    Symfony's :doc:`CSS Selector component </components/css_selector>`).
``filterXpath('h1')``
    Finds nodes matching the given `XPath expression`_.
``eq(1)``
    Returns the node at the given index (``0`` is the first node).
``first()``
    Returns the first node (equivalent to ``eq(0)``).
``last()``
    Returns the last node.
``siblings()``
    Returns all sibling nodes (nodes with the same parent, excluding the current node).
``nextAll()``
    Returns all following siblings (same parent, after the current node).
``previousAll()``
    Returns all preceding siblings (same parent, before the current node).
``ancestors()``
    Returns all ancestor nodes (parents, grandparents, etc., up to the ``<html>``
    element).
``children()``
    Returns all direct child nodes of the current node.
``reduce($lambda)``
    Filters the nodes using a callback; keeps only those for which it returns ``true``.

Since each of these methods returns a new ``Crawler`` instance, you can
narrow down your node selection by chaining the method calls::

    $crawler
        ->filter('h1')
        ->reduce(function ($node, int $i): bool {
            if (!$node->attr('class')) {
                return false;
            }

            return true;
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
    // returns an array for each element in the crawler,
    // each with the value and href
    $info = $crawler->extract(['_text', 'href']);

    // executes a lambda for each node and returns an array of results
    $data = $crawler->each(function ($node, int $i): string {
        return $node->attr('href');
    });

If you only need to check the contents of some nodes in your tests, use the
:ref:`crawler assertions <testing-crawler-assertions>` instead.

.. _`XPath expression`: https://developer.mozilla.org/en-US/docs/Web/XML/XPath
