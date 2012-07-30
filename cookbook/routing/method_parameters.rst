.. index::
   single: Routing; _method

Using HTTP Methods beyond GET and POST in Routes
================================================

The HTTP method of a request is one of the requirements that can be checked
when seeing if it matches a route. This is introduced in the routing chapter
of the book ":doc:`/book/routing`" with examples using GET and POST. You
can also use other HTTP verbs in this way. For example, if you have a blog
post entry then you could use the same URL pattern to show it, make changes
to it and delete it by matching on GET, PUT and DELETE.

.. configuration-block::

    .. code-block:: yaml

        blog_show:
            pattern:  /blog/{slug}
            defaults: { _controller: AcmeDemoBundle:Blog:show }
            requirements:
                _method:  GET

        blog_update:
            pattern:  /blog/{slug}
            defaults: { _controller: AcmeDemoBundle:Blog:update }
            requirements:
                _method:  PUT

        blog_delete:
            pattern:  /blog/{slug}
            defaults: { _controller: AcmeDemoBundle:Blog:delete }
            requirements:
                _method:  DELETE

    .. code-block:: xml

        <?xml version="1.0" encoding="UTF-8" ?>

        <routes xmlns="http://symfony.com/schema/routing"
            xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xsi:schemaLocation="http://symfony.com/schema/routing http://symfony.com/schema/routing/routing-1.0.xsd">

            <route id="blog_show" pattern="/blog/{slug}">
                <default key="_controller">AcmeDemoBundle:Blog:show</default>
                <requirement key="_method">GET</requirement>
            </route>

            <route id="blog_update" pattern="/blog/{slug}">
                <default key="_controller">AcmeDemoBundle:Blog:update</default>
                <requirement key="_method">PUT</requirement>
            </route>

            <route id="blog_delete" pattern="/blog/{slug}">
                <default key="_controller">AcmeDemoBundle:Blog:delete</default>
                <requirement key="_method">DELETE</requirement>
            </route>
        </routes>

    .. code-block:: php

        use Symfony\Component\Routing\RouteCollection;
        use Symfony\Component\Routing\Route;

        $collection = new RouteCollection();
        $collection->add('blog_show', new Route('/blog/{slug}', array(
            '_controller' => 'AcmeDemoBundle:Blog:show',
        ), array(
            '_method' => 'GET',
        )));

        $collection->add('blog_update', new Route('/blog/{slug}', array(
            '_controller' => 'AcmeDemoBundle:Blog:update',
        ), array(
            '_method' => 'PUT',
        )));

        $collection->add('blog_delete', new Route('/blog/{slug}', array(
            '_controller' => 'AcmeDemoBundle:Blog:delete',
        ), array(
            '_method' => 'DELETE',
        )));

        return $collection;

Unfortunately, life isn't quite this simple, since most browsers do not
support sending PUT and DELETE requests. Fortunately Symfony2 provides you
with a simple way of working around this limitation. By including a ``_method``
parameter in the query string or parameters of an HTTP request, Symfony2 will
use this as the method when matching routes. This can be done easily in forms
with a hidden field. Suppose you have a form for editing a blog post:

.. code-block:: html+jinja

    <form action="{{ path('blog_update', {'slug': blog.slug}) }}" method="post">
        <input type="hidden" name="_method" value="PUT" />
        {{ form_widget(form) }}
        <input type="submit" value="Update" />
    </form>

The submitted request will now match the ``blog_update`` route and the ``updateAction``
will be used to process the form.

Likewise the delete form could be changed to look like this:

.. code-block:: html+jinja

    <form action="{{ path('blog_delete', {'slug': blog.slug}) }}" method="post">
        <input type="hidden" name="_method" value="DELETE" />
        {{ form_widget(delete_form) }}
        <input type="submit" value="Delete" />
    </form>

It will then match the ``blog_delete`` route.