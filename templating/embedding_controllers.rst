.. index::
    single: Templating; Embedding action

How to Embed Controllers in a Template
======================================

In some cases, you need to do more than include a simple template. Suppose
you have a sidebar in your layout that contains the three most recent articles.
Retrieving the three articles may include querying the database or performing
other heavy logic that can't be done from within a template.

.. note::

    Rendering embedded controllers is "heavier" than including a template or calling
    a custom Twig function. Unless you're planning on :doc:`caching the fragment </http_cache/esi>`,
    avoid embedding many controllers.

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

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/article/recent_list.html.twig #}
        {% for article in articles %}
            <a href="/article/{{ article.slug }}">
                {{ article.title }}
            </a>
        {% endfor %}

    .. code-block:: html+php

        <!-- app/Resources/views/article/recent_list.html.php -->
        <?php foreach ($articles as $article): ?>
            <a href="/article/<?php echo $article->getSlug() ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach ?>

.. note::

    Notice that the article URL is hardcoded in this example
    (e.g. ``/article/*slug*``). This is a bad practice. In the next section,
    you'll learn how to do this correctly.

To include the controller, you'll need to refer to it using the standard
string syntax for controllers (i.e. **bundle**:**controller**:**action**):

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/base.html.twig #}

        {# ... #}
        <div id="sidebar">
            {{ render(controller(
                'AppBundle:Article:recentArticles',
                { 'max': 3 }
            )) }}
        </div>

    .. code-block:: html+php

        <!-- app/Resources/views/base.html.php -->

        <!-- ... -->
        <div id="sidebar">
            <?php echo $view['actions']->render(
                new \Symfony\Component\HttpKernel\Controller\ControllerReference(
                    'AppBundle:Article:recentArticles',
                    array('max' => 3)
                )
            ) ?>
        </div>

The result of an embedded controler can also be :doc:`cached </http_cache/esi>`
