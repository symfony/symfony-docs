.. index::
   single: Routing; methods

How to Use HTTP Methods beyond GET and POST in Routes
=====================================================

The HTTP method of a request is one of the requirements that can be checked
when seeing if it matches a route. This is introduced in the routing chapter
of the book ":doc:`/book/routing`" with examples using GET and POST. You can
also use other HTTP verbs in this way. For example, if you have a blog post
entry then you could use the same URL path to show it, make changes to it and
delete it by matching on GET, PUT and DELETE.

.. configuration-block::

    .. code-block:: yaml

        blog_show:
            path:     /blog/{slug}
            defaults: { _controller: AppBundle:Blog:show }
            methods:  [GET]

        blog_update:
            path:     /blog/{slug}
            defaults: { _controller: AppBundle:Blog:update }
            methods:  [PUT]

        blog_delete:
            path:     /blog/{slug}
            defaults: { _controller: AppBundle:Blog:delete }
            methods:  [DELETE]

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_show" path="/blog/{slug}" methods="GET">
                <default key="_controller">AppBundle:Blog:show</default>
            </route>

            <route id="blog_update" path="/blog/{slug}" methods="PUT">
                <default key="_controller">AppBundle:Blog:update</default>
            </route>

            <route id="blog_delete" path="/blog/{slug}" methods="DELETE">
                <default key="_controller">AppBundle:Blog:delete</default>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => 'AppBundle:Blog:show',
        ), array(), array(), '', array(), array('GET')));

        $collection->add('blog_update', new Route('/blog/{slug}', array(
            '_controller' => 'AppBundle:Blog:update',
        ), array(), array(), '', array(), array('PUT')));

        $collection->add('blog_delete', new Route('/blog/{slug}', array(
            '_controller' => 'AppBundle:Blog:delete',
        ), array(), array(), '', array('DELETE')));

        return $collection;

Faking the Method with ``_method``
----------------------------------

.. note::

    The ``_method`` functionality shown here is disabled by default in Symfony 2.2
    and enabled by default in Symfony 2.3. To control it in Symfony 2.2, you
    must call :method:`Request::enableHttpMethodParameterOverride <Symfony\\Component\\HttpFoundation\\Request::enableHttpMethodParameterOverride>`
    before you handle the request (e.g. in your front controller). In Symfony
    2.3, use the :ref:`configuration-framework-http_method_override` option.

Unfortunately, life isn't quite this simple, since most browsers do not support
sending PUT and DELETE requests via the `method` attribute in an HTML form. Fortunately,
Symfony provides you with a simple way of working around this limitation. By including
a ``_method`` parameter in the query string or parameters of an HTTP request, Symfony
will use this as the method when matching routes. Forms automatically include a
hidden field for this parameter if their submission method is not GET or POST.
See :ref:`the related chapter in the forms documentation<book-forms-changing-action-and-method>`
for more information.
