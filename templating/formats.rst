.. index::
    single: Templating; Formats

How to Work with Different Output Formats in Templates
======================================================

Templates are a generic way to render content in *any* format. And while in
most cases you'll use templates to render HTML content, a template can just
as easily generate JavaScript, CSS, XML or any other format you can dream of.

For example, the same "resource" is often rendered in several formats.
To render a contact index page in XML, simply include the format in the
template name:

* *XML template name*: ``contact/index.xml.twig``
* *XML template filename*: ``index.xml.twig``

In reality, this is nothing more than a naming convention and the template
isn't actually rendered differently based on its format.

In many cases, you may want to allow a single controller to render multiple
different formats based on the "request format". For that reason, a common
pattern is to do the following::

.. configuration-block::

  .. code-block:: php-annotations
  
      /**
       * @Route("/{_format}", name="contact_list", defaults={"_format": "html"}, requirements={"_format": "html|xml|pdf"})
       *
       * @param Request $request       
       *
       * @return Response
       */
      public function indexAction(Request $request)
      {
          $format = $request->getRequestFormat();

          return $this->render('contact/index.'.$format.'.twig');
      }
    

The ``getRequestFormat()`` on the ``Request`` object defaults to ``html``,
but can return any other format based on the format requested by the user.
The request format is most often managed by the routing, where a route can
be configured so that ``/contact`` sets the request format to ``html`` while
``/contact/xml`` sets the format to ``xml``. For more information, see the
:ref:`Advanced Example in the Routing chapter <advanced-routing-example>`.

To create links that include the format parameter, include a ``_format``
key in the parameter hash:

.. configuration-block::

    .. code-block:: html+twig

        <a href="{{ path('contact_list', {'_format': 'pdf'}) }}">
            PDF Version
        </a>

    .. code-block:: html+php

        <!-- The path() method was introduced in Symfony 2.8. Prior to 2.8, you
             had to use generate(). -->
        <a href="<?php echo $view['router']->path('contact_list', ['_format' => 'pdf']) ?>">
            PDF Version
        </a>
