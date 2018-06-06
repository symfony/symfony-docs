.. index::
    single: Templating; Embedding action

How to Embed Controllers in a Template
======================================

Including template fragments is a simple way to reuse common contents among
templates. However, the contents of the included templates are static, so you
can't use them to implement features like displaying in a sidebar the most
recent articles (which require making a database query).

The solution is to call a controller from the template and output its result.
First, create a controller that renders a certain number of recent articles::

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

Then, create a ``recent_list`` template fragment to list the articles given by
the controller:

.. configuration-block::

    .. code-block:: html+twig

        {# app/Resources/views/article/recent_list.html.twig #}
        {% for article in articles %}
            <a href="{{ path('article_show', {slug: article.slug}) }}">
                {{ article.title }}
            </a>
        {% endfor %}

    .. code-block:: html+php

        <!-- app/Resources/views/article/recent_list.html.php -->
        <?php foreach ($articles as $article): ?>
            <a href="<?php echo $view['router']->path('article_show', array(
            'slug' => $article->getSlug(),
            )) ?>">
                <?php echo $article->getTitle() ?>
            </a>
        <?php endforeach ?>

Finally, call the controller from any template using the ``render()`` function
and the common syntax for controllers (i.e. **bundle**:**controller**:**action**):

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

Whenever you find that you need a variable or a piece of information that
you don't have access to in a template, consider rendering a controller.
Controllers are fast to execute and promote good code organization and reuse.
Of course, like all controllers, they should ideally be "skinny", meaning
that as much code as possible lives in reusable :doc:`services </service_container>`.
