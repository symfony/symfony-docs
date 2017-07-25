.. index::
    single: Templating; Formats

How to Work with Different Output Formats in Templates
======================================================

Templates are a generic way to render content in *any* format. While in
most cases you'll use templates to render HTML content, a template can just
as easily generate JavaScript, CSS, XML or any other format you can dream of.

For example, the same "resource" is often rendered in several formats.
To render an article index page in XML, simply include the format in the
template name:

* *XML template name*: ``article/show.xml.twig``
* *XML template filename*: ``show.xml.twig``

In reality, this is nothing more than a naming convention and the template
isn't actually rendered differently based on its format.

In many cases, you may want to allow a single controller to render multiple
different formats based on the "request format". For that reason, a common
pattern is to do the following::

    // ...
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

    class ArticleController extends Controller
    {
        /**
         * @Route("/{slug}")
         */
        public function showAction(Request $request, $slug)
        {
            // retrieve the article based on $slug
            $article = ...;

            $format = $request->getRequestFormat();

            return $this->render('article/show.'.$format.'.twig', array(
                'article' => $article,
            ));
        }
    }

The ``getRequestFormat()`` on the ``Request`` object defaults to ``html``,
but can return any other format based on the format requested by the user.
The request format is most often managed by the routing, where a route can
be configured so that ``/about-us`` sets the request format to ``html`` while
``/about-us.xml`` sets the format to ``xml``. This can be achieved by using the
special ``_format`` placeholder in your route definition::

    /**
     * @Route("/{slug}.{_format}", defaults={"_format": "html"})
     */
    public function showAction(Request $request, $slug)
    {
        // ...
    }

Now, include the ``_format`` placeholder when generating a route for another
format:

.. code-block:: html+twig

    <a href="{{ path('article_show', {'slug': 'about-us', '_format': 'xml'}) }}">
        View as XML
    </a>

.. seealso::

    For more information, see this :ref:`Advanced Routing Example <advanced-routing-example>`.

.. tip::

    When building APIs, using file name extensions often isn't the best
    solution. The FOSRestBundle provides a request listener that uses content
    negotiation. For more information, check out the bundle's `Request Format Listener`_
    documentation.

.. _Request Format Listener: http://symfony.com/doc/current/bundles/FOSRestBundle/3-listener-support.html#format-listener
