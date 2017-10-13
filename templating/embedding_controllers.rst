.. index::
    single: Templating; Embedding action

How to Embed Controllers in a Template
======================================

In some cases, you need to do more than include a simple template. Suppose
you have a sidebar in your layout that contains the three most recent articles.
Retrieving the three articles may include querying the database or performing
other heavy logic that can't be done from within a template.

The solution is to simply embed the result of an entire controller from your
template. First, create a controller that renders a certain number of recent
articles::

    // src/AppBundle/Controller/ArticleController.php
    namespace AppBundle\Controller;

    // ...

    class ArticleController extends Controller
    {
        public function recentArticlesAction($max = 3)
        {
            // make a database call or other logic
            // to get the "$max" most recent articles
            $articles = ...;

            return $this->render(
                'article/recent_list.html.twig',
                array('articles' => $articles)
            );
        }
    }

The ``recent_list`` template is perfectly straightforward:

.. code-block:: html+twig

    {# app/Resources/views/article/recent_list.html.twig #}
    {% for article in articles %}
        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}

.. note::

    Notice that the article URL is hardcoded in this example
    (e.g. ``/article/*slug*``). This is a bad practice. In the next section,
    you'll learn how to do this correctly.

To include the controller, you'll need to refer to it using the standard
string syntax for controllers (i.e. **bundle**:**controller**:**action**):

.. code-block:: html+twig

    {# app/Resources/views/base.html.twig #}

    {# ... #}
    <div id="sidebar">
        {{ render(controller(
            'AppBundle:Article:recentArticles',
            { 'max': 3 }
        )) }}
    </div>

Whenever you find that you need a variable or a piece of information that
you don't have access to in a template, consider rendering a controller.
Controllers are fast to execute and promote good code organization and reuse.
Of course, like all controllers, they should ideally be "skinny", meaning
that as much code as possible lives in reusable :doc:`services </service_container>`.
