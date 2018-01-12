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

    // src/AppBundle/Controller/ArticleController.php
    namespace AppBundle\Controller;

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

In a Twig template, two constructs are available for dumping a variable.
Choosing between both is mostly a matter of personal taste, still:

* ``{% dump foo.bar %}`` is the way to go when the original template output
  shall not be modified: variables are not dumped inline, but in the web
  debug toolbar;
* on the contrary, ``{{ dump(foo.bar) }}`` dumps inline and thus may or not
  be suited to your use case (e.g. you shouldn't use it in an HTML
  attribute or a ``<script>`` tag).

.. code-block:: html+twig

    {# app/Resources/views/article/recent_list.html.twig #}
    {% dump articles %}

    {% for article in articles %}
        <a href="/article/{{ article.slug }}">
            {{ article.title }}
        </a>
    {% endfor %}
    
or

.. code-block:: html+twig
    
    {# app/Resources/views/article/recent_list.html.twig #}
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
