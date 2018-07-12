.. index::
    single: Templating; Embedding action

How to Embed Controllers in a Template
======================================

.. note::

    Rendering embedded controllers is "heavier" than including a template or calling
    a custom Twig function. Unless you're planning on :doc:`caching the fragment </http_cache/esi>`,
    avoid embedding many controllers.

:ref:`Including template fragments <including-other-templates>` is useful to
reuse the same content on several pages. However, this technique is not the best
solution in some cases.

Consider a website that displays on its sidebar the most recently published
articles. This list of articles is dynamic and it's probably the result of a
database query. In other words, the controller of any page that displays that
sidebar must make the same database query and pass the list of articles to the
included template fragment.

The alternative solution proposed by Symfony is to create a controller that only
displays the list of recent articles and then call to that controller from any
template that needs to display that content.

First, create a controller that renders a certain number of recent articles::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...

    class ArticleController extends Controller
    {
        public function recentArticles($max = 3)
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

Then, create a ``recent_list`` template fragment to list the articles given by
the controller:

.. code-block:: html+twig

    {# templates/article/recent_list.html.twig #}
    {% for article in articles %}
        <a href="{{ path('article_show', {slug: article.slug}) }}">
            {{ article.title }}
        </a>
    {% endfor %}

Finally, call the controller from any template using the ``render()`` function
and the standard string syntax for controllers (i.e. **controllerNamespace**::**action**):

.. code-block:: html+twig

    {# templates/base.html.twig #}

    {# ... #}
    <div id="sidebar">
        {{ render(controller(
            'App\\Controller\\ArticleController::recentArticles',
            { 'max': 3 }
        )) }}
    </div>
