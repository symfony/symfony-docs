.. index::
    single: Templating; Debug
    single: Templating; Dump
    single: Twig; Debug
    single: Twig; Dump

How to Dump Debug Information in Twig Templates
===============================================

When using PHP, you can use the
:ref:`dump() function from the VarDumper component <components-var-dumper-dump>`
if you need to quickly find the value of a variable passed. This is useful,
for example, inside your controller::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...

    class ArticleController extends Controller
    {
        public function recentListAction()
        {
            $articles = ...;
            dump($articles);

            // ...
        }
    }

.. note::

    The output of the ``dump()`` function is then rendered in the web developer
    toolbar.

The same mechanism can be used in Twig templates thanks to ``dump()`` function:

.. code-block:: html+twig

    {# templates/article/recent_list.html.twig #}
    {{ dump(articles) }}

    {% for article in articles %}
        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}

By design, the ``dump()`` function is only available if the ``kernel.debug``
setting (in ``config.yml``) is ``true``, to avoid leaking sensitive information
in production. In fact, trying to use the ``dump()`` function when ``kernel.debug``
is ``false`` (for example in the ``prod`` environment) will result in an
application error.
