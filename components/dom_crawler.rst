.. index::
   single: DomCrawler
   single: Components; DomCrawler

The DomCrawler Component
========================

    The DomCrawler component eases DOM navigation for HTML and XML documents.

.. note::

    While possible, the DomCrawler component is not designed for manipulation
    of the DOM or re-dumping HTML/XML.

Installation
------------

.. code-block:: terminal

    $ composer require symfony/dom-crawler

.. include:: /components/require_autoload.rst.inc

Usage
-----

.. seealso::

    This article explains how to use the DomCrawler features as an independent
    component in any PHP application. Read the :ref:`Symfony Functional Tests <functional-tests>`
    article to learn about how to use it when creating Symfony tests.

The :class:`Symfony\\Component\\DomCrawler\\Crawler` class provides methods
to query and manipulate HTML and XML documents.

An instance of the Crawler represents a set of :phpclass:`DOMElement` objects,
which are nodes that can be traversed as follows::

    use Symfony\Component\DomCrawler\Crawler;

    $html = <<<'HTML'
    <!DOCTYPE html>
    <html>
        <body>
            <p class="message">Hello World!</p>
            <p>Hello Crawler!</p>
        </body>
    </html>
    HTML;

    $crawler = new Crawler($html);

    foreach ($crawler as $domElement) {
        var_dump($domElement->nodeName);
    }

Specialized :class:`Symfony\\Component\\DomCrawler\\Link`,
:class:`Symfony\\Component\\DomCrawler\\Image` and
:class:`Symfony\\Component\\DomCrawler\\Form` classes are useful for
interacting with html links, images and forms as you traverse through the HTML
tree.

.. note::

    The DomCrawler will attempt to automatically fix your HTML to match the
    official specification. For example, if you nest a ``<p>`` tag inside
    another ``<p>`` tag, it will be moved to be a sibling of the parent tag.
    This is expected and is part of the HTML5 spec. But if you're getting
    unexpected behavior, this could be a cause. And while the DomCrawler
    isn't meant to dump content, you can see the "fixed" version of your HTML
    by :ref:`dumping it <component-dom-crawler-dumping>`.

.. note::

    If you need better support for HTML5 contents or want to get rid of the
    inconsistencies of PHP's DOM extension, install the `html5-php library`_.
    The DomCrawler component will use it automatically when the content has
    an HTML5 doctype.

Node Filtering
~~~~~~~~~~~~~~

Using XPath expressions, you can select specific nodes within the document::

    $crawler = $crawler->filterXPath('descendant-or-self::body/p');

.. tip::

    ``DOMXPath::query`` is used internally to actually perform an XPath query.

If you prefer CSS selectors over XPath, install :doc:`/components/css_selector`.
It allows you to use jQuery-like selectors::

    $crawler = $crawler->filter('body > p');

An anonymous function can be used to filter with more complex criteria::

    use Symfony\Component\DomCrawler\Crawler;
    // ...

    $crawler = $crawler
        ->filter('body > p')
        ->reduce(function (Crawler $node, $i) {
            // filters every other node
            return ($i % 2) == 0;
        });

To remove a node, the anonymous function must return ``false``.

.. note::

    All filter methods return a new :class:`Symfony\\Component\\DomCrawler\\Crawler`
    instance with the filtered content. To check if the filter actually
    found something, use ``$crawler->count() > 0`` on this new crawler.

Both the :method:`Symfony\\Component\\DomCrawler\\Crawler::filterXPath` and
:method:`Symfony\\Component\\DomCrawler\\Crawler::filter` methods work with
XML namespaces, which can be either automatically discovered or registered
explicitly.

Consider the XML below:

.. code-block:: xml

    <?xml version="1.0" encoding="UTF-8" ?>
    <entry
        xmlns="http://www.w3.org/2005/Atom"
        xmlns:media="http://search.yahoo.com/mrss/"
        xmlns:yt="http://gdata.youtube.com/schemas/2007"
    >
        <id>tag:youtube.com,2008:video:kgZRZmEc9j4</id>
        <yt:accessControl action="comment" permission="allowed"/>
        <yt:accessControl action="videoRespond" permission="moderated"/>
        <media:group>
            <media:title type="plain">Chordates - CrashCourse Biology #24</media:title>
            <yt:aspectRatio>widescreen</yt:aspectRatio>
        </media:group>
    </entry>

This can be filtered with the  ``Crawler`` without needing to register namespace
aliases both with :method:`Symfony\\Component\\DomCrawler\\Crawler::filterXPath`::

    $crawler = $crawler->filterXPath('//default:entry/media:group//yt:aspectRatio');

and :method:`Symfony\\Component\\DomCrawler\\Crawler::filter`::

    $crawler = $crawler->filter('default|entry media|group yt|aspectRatio');

.. note::

    The default namespace is registered with a prefix "default". It can be
    changed with the
    :method:`Symfony\\Component\\DomCrawler\\Crawler::setDefaultNamespacePrefix`
    method.

    The default namespace is removed when loading the content if it's the only
    namespace in the document. It's done to simplify the XPath queries.

Namespaces can be explicitly registered with the
:method:`Symfony\\Component\\DomCrawler\\Crawler::registerNamespace` method::

    $crawler->registerNamespace('m', 'http://search.yahoo.com/mrss/');
    $crawler = $crawler->filterXPath('//m:group//yt:aspectRatio');

Verify if the current node matches a selector::

    $crawler->matches('p.lorem');

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

Get all the child or ancestor nodes::

    $crawler->filter('body')->children();
    $crawler->filter('body > p')->ancestors();

.. versionadded:: 5.3

    The ``ancestors()`` method was introduced in Symfony 5.3.

Get all the direct child nodes matching a CSS selector::

    $crawler->filter('body')->children('p.lorem');

Get the first parent (heading toward the document root) of the element that matches the provided selector::

    $crawler->closest('p.lorem');

.. note::

    All the traversal methods return a new :class:`Symfony\\Component\\DomCrawler\\Crawler`
    instance.

Accessing Node Values
~~~~~~~~~~~~~~~~~~~~~

Access the node name (HTML tag name) of the first node of the current selection (e.g. "p" or "div")::

    // returns the node name (HTML tag name) of the first child element under <body>
    $tag = $crawler->filterXPath('//body/*')->nodeName();

Access the value of the first node of the current selection::

    // if the node does not exist, calling to text() will result in an exception
    $message = $crawler->filterXPath('//body/p')->text();

    // avoid the exception passing an argument that text() returns when node does not exist
    $message = $crawler->filterXPath('//body/p')->text('Default text content');

    // by default, text() trims white spaces, including the internal ones
    // (e.g. "  foo\n  bar    baz \n " is returned as "foo bar baz")
    // pass FALSE as the second argument to return the original text unchanged
    $crawler->filterXPath('//body/p')->text('Default text content', false);

Access the attribute value of the first node of the current selection::

    $class = $crawler->filterXPath('//body/p')->attr('class');

Extract attribute and/or node values from the list of nodes::

    $attributes = $crawler
        ->filterXpath('//body/p')
        ->extract(['_name', '_text', 'class'])
    ;

.. note::

    Special attribute ``_text`` represents a node value, while ``_name``
    represents the element name (the HTML tag name).

Call an anonymous function on each node of the list::

    use Symfony\Component\DomCrawler\Crawler;
    // ...

    $nodeValues = $crawler->filter('p')->each(function (Crawler $node, $i) {
        return $node->text();
    });

The anonymous function receives the node (as a Crawler) and the position as arguments.
The result is an array of values returned by the anonymous function calls.

When using nested crawler, beware that ``filterXPath()`` is evaluated in the
context of the crawler::

    $crawler->filterXPath('parent')->each(function (Crawler $parentCrawler, $i) {
        // DON'T DO THIS: direct child can not be found
        $subCrawler = $parentCrawler->filterXPath('sub-tag/sub-child-tag');

        // DO THIS: specify the parent tag too
        $subCrawler = $parentCrawler->filterXPath('parent/sub-tag/sub-child-tag');
        $subCrawler = $parentCrawler->filterXPath('node()/sub-tag/sub-child-tag');
    });

Adding the Content
~~~~~~~~~~~~~~~~~~

The crawler supports multiple ways of adding the content, but they are mutually
exclusive, so you can only use one of them to add content (e.g. if you pass the
content to the ``Crawler`` constructor, you can't call ``addContent()`` later)::

    $crawler = new Crawler('<html><body/></html>');

    $crawler->addHtmlContent('<html><body/></html>');
    $crawler->addXmlContent('<root><node/></root>');

    $crawler->addContent('<html><body/></html>');
    $crawler->addContent('<root><node/></root>', 'text/xml');

    $crawler->add('<html><body/></html>');
    $crawler->add('<root><node/></root>');

.. note::

    The :method:`Symfony\\Component\\DomCrawler\\Crawler::addHtmlContent` and
    :method:`Symfony\\Component\\DomCrawler\\Crawler::addXmlContent` methods
    default to UTF-8 encoding but you can change this behavior with their second
    optional argument.

    The :method:`Symfony\\Component\\DomCrawler\\Crawler::addContent` method
    guesses the best charset according to the given contents and defaults to
    ``ISO-8859-1`` in case no charset can be guessed.

As the Crawler's implementation is based on the DOM extension, it is also able
to interact with native :phpclass:`DOMDocument`, :phpclass:`DOMNodeList`
and :phpclass:`DOMNode` objects::

    $domDocument = new \DOMDocument();
    $domDocument->loadXml('<root><node/><node/></root>');
    $nodeList = $domDocument->getElementsByTagName('node');
    $node = $domDocument->getElementsByTagName('node')->item(0);

    $crawler->addDocument($domDocument);
    $crawler->addNodeList($nodeList);
    $crawler->addNodes([$node]);
    $crawler->addNode($node);
    $crawler->add($domDocument);

.. _component-dom-crawler-dumping:

.. sidebar:: Manipulating and Dumping a ``Crawler``

    These methods on the ``Crawler`` are intended to initially populate your
    ``Crawler`` and aren't intended to be used to further manipulate a DOM
    (though this is possible). However, since the ``Crawler`` is a set of
    :phpclass:`DOMElement` objects, you can use any method or property available
    on :phpclass:`DOMElement`, :phpclass:`DOMNode` or :phpclass:`DOMDocument`.
    For example, you could get the HTML of a ``Crawler`` with something like
    this::

        $html = '';

        foreach ($crawler as $domElement) {
            $html .= $domElement->ownerDocument->saveHTML($domElement);
        }

    Or you can get the HTML of the first node using
    :method:`Symfony\\Component\\DomCrawler\\Crawler::html`::

        // if the node does not exist, calling to html() will result in an exception
        $html = $crawler->html();

        // avoid the exception passing an argument that html() returns when node does not exist
        $html = $crawler->html('Default <strong>HTML</strong> content');

    Or you can get the outer HTML of the first node using
    :method:`Symfony\\Component\\DomCrawler\\Crawler::outerHtml`::

        $html = $crawler->outerHtml();

Expression Evaluation
~~~~~~~~~~~~~~~~~~~~~

The ``evaluate()`` method evaluates the given XPath expression. The return
value depends on the XPath expression. If the expression evaluates to a scalar
value (e.g. HTML attributes), an array of results will be returned. If the
expression evaluates to a DOM document, a new ``Crawler`` instance will be
returned.

This behavior is best illustrated with examples::

    use Symfony\Component\DomCrawler\Crawler;

    $html = '<html>
    <body>
        <span id="article-100" class="article">Article 1</span>
        <span id="article-101" class="article">Article 2</span>
        <span id="article-102" class="article">Article 3</span>
    </body>
    </html>';

    $crawler = new Crawler();
    $crawler->addHtmlContent($html);

    $crawler->filterXPath('//span[contains(@id, "article-")]')->evaluate('substring-after(@id, "-")');
    /* Result:
    [
        0 => '100',
        1 => '101',
        2 => '102',
    ];
    */

    $crawler->evaluate('substring-after(//span[contains(@id, "article-")]/@id, "-")');
    /* Result:
    [
        0 => '100',
    ]
    */

    $crawler->filterXPath('//span[@class="article"]')->evaluate('count(@id)');
    /* Result:
    [
        0 => 1.0,
        1 => 1.0,
        2 => 1.0,
    ]
    */

    $crawler->evaluate('count(//span[@class="article"])');
    /* Result:
    [
        0 => 3.0,
    ]
    */

    $crawler->evaluate('//span[1]');
    // A Symfony\Component\DomCrawler\Crawler instance

Links
~~~~~

Use the ``filter()`` method to find links by their ``id`` or ``class``
attributes and use the ``selectLink()`` method to find links by their content
(it also finds clickable images with that content in its ``alt`` attribute).

Both methods return a ``Crawler`` instance with just the selected link. Use the
``link()`` method to get the :class:`Symfony\\Component\\DomCrawler\\Link` object
that represents the link::

    // first, select the link by id, class or content...
    $linkCrawler = $crawler->filter('#sign-up');
    $linkCrawler = $crawler->filter('.user-profile');
    $linkCrawler = $crawler->selectLink('Log in');

    // ...then, get the Link object:
    $link = $linkCrawler->link();

    // or do all this at once:
    $link = $crawler->filter('#sign-up')->link();
    $link = $crawler->filter('.user-profile')->link();
    $link = $crawler->selectLink('Log in')->link();

The :class:`Symfony\\Component\\DomCrawler\\Link` object has several useful
methods to get more information about the selected link itself::

    // returns the proper URI that can be used to make another request
    $uri = $link->getUri();

.. note::

    The ``getUri()`` is especially useful as it cleans the ``href`` value and
    transforms it into how it should really be processed. For example, for a
    link with ``href="#foo"``, this would return the full URI of the current
    page suffixed with ``#foo``. The return from ``getUri()`` is always a full
    URI that you can act on.

Images
~~~~~~

To find an image by its ``alt`` attribute, use the ``selectImage`` method on an
existing crawler. This returns a ``Crawler`` instance with just the selected
image(s). Calling ``image()`` gives you a special
:class:`Symfony\\Component\\DomCrawler\\Image` object::

    $imagesCrawler = $crawler->selectImage('Kitten');
    $image = $imagesCrawler->image();

    // or do this all at once
    $image = $crawler->selectImage('Kitten')->image();

The :class:`Symfony\\Component\\DomCrawler\\Image` object has the same
``getUri()`` method as :class:`Symfony\\Component\\DomCrawler\\Link`.

Forms
~~~~~

Special treatment is also given to forms. A ``selectButton()`` method is
available on the Crawler which returns another Crawler that matches ``<button>``
or ``<input type="submit">`` or ``<input type="button">`` elements (or an
``<img>`` element inside them). The string given as argument is looked for in
the ``id``, ``alt``, ``name``, and ``value`` attributes and the text content of
those elements.

This method is especially useful because you can use it to return
a :class:`Symfony\\Component\\DomCrawler\\Form` object that represents the
form that the button lives in::

    // button example: <button id="my-super-button" type="submit">My super button</button>

    // you can get button by its label
    $form = $crawler->selectButton('My super button')->form();

    // or by button id (#my-super-button) if the button doesn't have a label
    $form = $crawler->selectButton('my-super-button')->form();

    // or you can filter the whole form, for example a form has a class attribute: <form class="form-vertical" method="POST">
    $crawler->filter('.form-vertical')->form();

    // or "fill" the form fields with data
    $form = $crawler->selectButton('my-super-button')->form([
        'name' => 'Ryan',
    ]);

The :class:`Symfony\\Component\\DomCrawler\\Form` object has lots of very
useful methods for working with forms::

    $uri = $form->getUri();
    $method = $form->getMethod();
    $name = $form->getName();

The :method:`Symfony\\Component\\DomCrawler\\Form::getUri` method does more
than just return the ``action`` attribute of the form. If the form method
is GET, then it mimics the browser's behavior and returns the ``action``
attribute followed by a query string of all of the form's values.

.. note::

    The optional ``formaction`` and ``formmethod`` button attributes are
    supported. The ``getUri()`` and ``getMethod()`` methods take into account
    those attributes to always return the right action and method depending on
    the button used to get the form.

You can virtually set and get values on the form::

    // sets values on the form internally
    $form->setValues([
        'registration[username]' => 'symfonyfan',
        'registration[terms]'    => 1,
    ]);

    // gets back an array of values - in the "flat" array like above
    $values = $form->getValues();

    // returns the values like PHP would see them,
    // where "registration" is its own array
    $values = $form->getPhpValues();

To work with multi-dimensional fields:

.. code-block:: html

    <form>
        <input name="multi[]"/>
        <input name="multi[]"/>
        <input name="multi[dimensional]"/>
        <input name="multi[dimensional][]" value="1"/>
        <input name="multi[dimensional][]" value="2"/>
        <input name="multi[dimensional][]" value="3"/>
    </form>

Pass an array of values::

    // sets a single field
    $form->setValues(['multi' => ['value']]);

    // sets multiple fields at once
    $form->setValues(['multi' => [
        1             => 'value',
        'dimensional' => 'an other value',
    ]]);

    // tick multiple checkboxes at once
    $form->setValues(['multi' => [
        'dimensional' => [1, 3] // it uses the input value to determine which checkbox to tick
    ]]);

This is great, but it gets better! The ``Form`` object allows you to interact
with your form like a browser, selecting radio values, ticking checkboxes,
and uploading files::

    $form['registration[username]']->setValue('symfonyfan');

    // checks or unchecks a checkbox
    $form['registration[terms]']->tick();
    $form['registration[terms]']->untick();

    // selects an option
    $form['registration[birthday][year]']->select(1984);

    // selects many options from a "multiple" select
    $form['registration[interests]']->select(['symfony', 'cookies']);

    // fakes a file upload
    $form['registration[photo]']->upload('/path/to/lucas.jpg');

Using the Form Data
...................

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

One great example of an integrated system that uses all of this is
the :class:`Symfony\\Component\\BrowserKit\\HttpBrowser` provided by
the :doc:`BrowserKit component </components/browser_kit>`.
It understands the Symfony Crawler object and can use it to submit forms
directly::

    use Symfony\Component\BrowserKit\HttpBrowser;
    use Symfony\Component\HttpClient\HttpClient;

    // makes a real request to an external site
    $browser = new HttpBrowser(HttpClient::create());
    $crawler = $browser->request('GET', 'https://github.com/login');

    // select the form and fill in some values
    $form = $crawler->selectButton('Sign in')->form();
    $form['login'] = 'symfonyfan';
    $form['password'] = 'anypass';

    // submits the given form
    $crawler = $browser->submit($form);

.. _components-dom-crawler-invalid:

Selecting Invalid Choice Values
...............................

By default, choice fields (select, radio) have internal validation activated
to prevent you from setting invalid values. If you want to be able to set
invalid values, you can use the  ``disableValidation()`` method on either
the whole form or specific field(s)::

    // disables validation for a specific field
    $form['country']->disableValidation()->select('Invalid value');

    // disables validation for the whole form
    $form->disableValidation();
    $form['country']->select('Invalid value');

Resolving a URI
~~~~~~~~~~~~~~~

.. versionadded:: 5.1

    The :class:`Symfony\\Component\\DomCrawler\\UriResolver` helper class was added in Symfony 5.1.

The :class:`Symfony\\Component\\DomCrawler\\UriResolver` class takes an URI
(relative, absolute, fragment, etc.) and turns it into an absolute URI against
another given base URI::

    use Symfony\Component\DomCrawler\UriResolver;

    UriResolver::resolve('/foo', 'http://localhost/bar/foo/'); // http://localhost/foo
    UriResolver::resolve('?a=b', 'http://localhost/bar#foo'); // http://localhost/bar?a=b
    UriResolver::resolve('../../', 'http://localhost/'); // http://localhost/

Learn more
----------

* :doc:`/testing`
* :doc:`/components/css_selector`

.. _`html5-php library`: https://github.com/Masterminds/html5-php
