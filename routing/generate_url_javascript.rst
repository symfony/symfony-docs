How to Generate Routing URLs in JavaScript
==========================================

If you're in a Twig template, you can use the same ``path()`` function to set
JavaScript variables. The ``escape()`` function helps escape any
non-JavaScript-safe values:

.. code-block:: html+twig

    <script>
        let route = "{{ path('blog_show', {'slug': 'my-blog-post'})|escape('js') }}";
    </script>

But if you *actually* need to generate routes in pure JavaScript, consider using
the `FOSJsRoutingBundle`_. It makes the following possible:

.. code-block:: html+twig

    <script>
        let url = Routing.generate('blog_show', {
            'slug': 'my-blog-post'
        });
    </script>

.. _`FOSJsRoutingBundle`: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
