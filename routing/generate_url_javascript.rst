How to Generate Routing URLs in JavaScript
==========================================

If you're in a Twig template, you can use the same ``path`` function to set JavaScript
variables. The ``escape`` function helps escape any non-JavaScript-safe values:

.. configuration-block::

    .. code-block:: html+twig

        <script>
        var route = "{{ path('blog_show', {'slug': 'my-blog-post'})|escape('js') }}";
        </script>

    .. code-block:: html+php

        <script>
        var route = "<?php echo $view->escape(
            $view['router']->generate('blog_show', array(
                'slug' => 'my-blog-post',
            )),
            'js'
        ) ?>";
        </script>

But if you *actually* need to generate routes in pure JavaScript, consider using
the `FOSJsRoutingBundle`_. It makes the following possible:

.. code-block:: javascript

    var url = Routing.generate('blog_show', {
        'slug': 'my-blog-post'
    });

.. _`FOSJsRoutingBundle`: https://github.com/FriendsOfSymfony/FOSJsRoutingBundle
