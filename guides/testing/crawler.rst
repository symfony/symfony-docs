.. index::
   single: Tests; Crawler

The Crawler
===========

A Crawler instance is returned each time you make a request with the Client.
It allows you to traverse HTML documents, select nodes, find links and forms.

Creating a Crawler Instance
---------------------------

A Crawler instance is automatically created for you when you make a request
with a Client. But you can create your own easily::

    use Symfony\Components\DomCrawler\Crawler;

    $crawler = new Crawler($html, $url);

The constructor takes two arguments: the second one is the URL that is used to
generate absolute URLs for links and forms; the first one can be any of the
following:

* An HTML document;
* An XML document;
* A ``DOMDocument`` instance;
* A ``DOMNodeList`` instance;
* A ``DOMNode`` instance;
* An array of the above elements.

After creation, you can add more nodes:

===================== ================================
Method                Description                     
===================== ================================
``addHTMLDocument()`` An HTML document                
``addXMLDocument()``  An XML document                 
``addDOMDocument()``  A ``DOMDocument`` instance      
``addDOMNodeList()``  A ``DOMNodeList`` instance      
``addDOMNode()``      A ``DOMNode`` instance          
``addNodes()``        An array of the above elements  
``add()``             Accept any of the above elements
===================== ================================

Traversing
----------

Like JQuery, the Crawler has methods to traverse the DOM of an HTML/XML
document:

===================== =========================================
Method                Description
===================== =========================================
``filter('h1')``      Nodes that match the CSS selector
``filterXpath('h1')`` Nodes that match the XPath expression
``eq(1)``             Node for the specified index
``first()``           First node
``last()``            Last node
``siblings()``        Siblings
``nextAll()``         All following siblings
``previousAll()``     All preceding siblings
``parents()``         Parent nodes
``children()``        Children
``reduce($lambda)``   Nodes for which the callable returns true
===================== =========================================

You can iteratively narrow your node selection by chaining method calls as
each method returns a new Crawler instance for the matching nodes::

    $crawler
        ->filter('h1')
        ->reduce(function ($node, $i)
        {
            if (!$node->getAttribute('class')) {
                return false;
            }
        })
        ->first();

.. tip::
   Use the ``count()`` function to get the number of nodes stored in a Crawler:
   ``count($crawler)``

Extracting Information
----------------------

The Crawler can extract information from the nodes::

    // Returns the attribute value for the first node
    $crawler->attr('class');

    // Returns the node value for the first node
    $crawler->text();

    // Extracts an array of attributes for all nodes (_text returns the node value)
    $crawler->extract(array('_text', 'href'));

    // Executes a lambda for each node and return an array of results
    $data = $crawler->each(function ($node, $i)
    {
        return $node->getAttribute('href');
    });

Links
-----

You can select links with the traversing methods, but the ``selectLink()``
shortcut is often more convenient::

    $crawler->selectLink('Click here');

It selects links that contain the given text, or clickable images for which
the ``alt`` attribute contains the given text.

The Client ``click()`` method takes a ``Link`` instance as returned by the
``link()`` method::

    $link = $crawler->link();

    $client->click($link);

.. tip::
   The ``links()`` method returns an array of ``Link``s for all nodes.

Forms
-----

As for links, you select forms with the ``selectButton()`` method::

    $crawler->selectButton('submit');

Notice that we select form buttons and not forms as a form can have several
buttons; if you use the traversing API, keep in mind that you must look for a
button.

The ``selectButton()`` method can select ``button`` tags and submit ``input`` tags;
it has several heuristics to find them:

* The ``value`` attribute value;

* The ``id`` or ``alt`` attribute value for images;

* The ``id`` or ``name`` attribute value for ``button`` tags.

When you have a node representing a button, call the ``form()`` method to get a
``Form`` instance for the form wrapping the button node::

    $form = $crawler->form();

When calling the ``form()`` method, you can also pass an array of field values
that overrides the default ones::

    $form = $crawler->form(array(
        'name'         => 'Fabien',
        'like_symfony' => true,
    ));

And if you want to simulate a specific HTTP method for the form, pass it as a
second argument::

    $form = $crawler->form(array(), 'DELETE');

The Client can submit ``Form`` instances::

    $client->submit($form);

The field values can also be passed as a second argument of the ``submit()``
method::

    $client->submit($form, array(
        'name'         => 'Fabien',
        'like_symfony' => true,
    ));

For more complex situations, use the ``Form`` instance as an array to set the
value of each field individually::

    // Change the value of a field
    $form['name'] = 'Fabien';

There is also a nice API to manipulate the values of the fields according to
their type::

    // Select an option or a radio
    $form['country']->select('France');

    // Tick a checkbox
    $form['like_symfony']->tick();

    // Upload a file
    $form['photo']->upload('/path/to/lucas.jpg');

.. tip::
   You can get the values that will be submitted by calling the ``getValues()``
   method. The uploaded files are available in a separate array returned by
   ``getFiles()``. The ``getPhpValues()`` and ``getPhpFiles()`` also return the
   submitted values, but in the PHP format (it converts the keys with square
   brackets notation to PHP arrays).
