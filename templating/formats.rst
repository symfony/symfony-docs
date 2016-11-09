.. index::
    single: Templating; Formats

How to Work with Different Output Formats in Templates
======================================================

Templates are a generic way to render content in *any* format. And while in
most cases you'll use templates to render HTML content, a template can just
as easily generate JavaScript, CSS, XML or any other format you can dream of.

For example, the same "resource" is often rendered in several formats.
To render an article index page in XML, simply include the format in the
template name:

* *XML template name*: ``article/index.xml.twig``
* *XML template filename*: ``index.xml.twig``

In reality, this is nothing more than a naming convention and the template
isn't actually rendered differently based on its format.

In many cases, you may want to allow a single controller to render multiple
different formats based on the "request format". For that reason, a common
pattern is to do the following::

    public function showAction(Request $request, Article $entity)
    {
        $format = $request->getRequestFormat();

        return $this->render('article/index.'.$format.'.twig', [
          'entity' => $entity
        ]);
    }

The ``getRequestFormat()`` on the ``Request`` object defaults to ``html``,
but can return any other format based on the format requested by the user.
The request format is most often managed by the routing, where a route can
be configured so that ``/contact`` sets the request format to ``html`` while
``/contact.xml`` sets the format to ``xml``. For more information, see this
:ref:`Advanced Routing Example <advanced-routing-example>`.

To create links that include the format parameter, include a ``_format``
key in the parameter hash:

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ path('article_show', {'id': 123, '_format': 'pdf'}) }}">
            PDF Version
        </a>

    .. code-block:: html+php

        <a href="<?php echo $view['router']->generate('article_show', array(
            'id' => 123,
            '_format' => 'pdf',
        )) ?>">
            PDF Version
        </a>
