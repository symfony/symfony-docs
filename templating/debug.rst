.. index::
    single: Templating; Debug
    single: Templating; Dump
    single: Twig; Debug
    single: Twig; Dump

How to Dump Debug Information in Twig Templates
===============================================

When using PHP templates, you can use the
:ref:`dump() function from the VarDumper component <components-var-dumper-dump>`
if you need to quickly find the value of a variable passed. First, make sure it
is installed:

.. code-block:: terminal

    $ composer require var-dumper

This is useful, for example, inside your controller::

    // src/Controller/ArticleController.php
    namespace App\Controller;

    // ...

    class ArticleController extends Controller
    {
        public function recentList()
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

By design, the ``dump()`` function is only available in the ``dev`` and ``test``
environments, to avoid leaking sensitive information in production. In fact,
trying to use the ``dump()`` function in the ``prod`` environment will result in
a PHP error.
