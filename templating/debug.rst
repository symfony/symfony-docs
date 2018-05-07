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

    $ composer require symfony/var-dumper

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

In a Twig template, you can use the ``dump`` utility as a function or a tag:

* ``{% dump foo.bar %}`` is the way to go when the original template output
  shall not be modified: variables are not dumped inline, but in the web
  debug toolbar;
* on the contrary, ``{{ dump(foo.bar) }}`` dumps inline and thus may or not
  be suited to your use case (e.g. you shouldn't use it in an HTML
  attribute or a ``<script>`` tag).

.. code-block:: html+twig

    {# templates/article/recent_list.html.twig #}
    {# the contents of this variable are sent to the Web Debug Toolbar #}
    {% dump articles %}

    {% for article in articles %}
        {# the contents of this variable are displayed on the web page #}
        {{ dump(article) }}

        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}

By design, the ``dump()`` function is only available in the ``dev`` and ``test``
environments, to avoid leaking sensitive information in production. In fact,
trying to use the ``dump()`` function in the ``prod`` environment will result in
a PHP error.
