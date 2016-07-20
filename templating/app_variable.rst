.. index::
    single: Templating; app Variable

How to Access the User, Request, Session & more in Twig via the ``app`` Variable
================================================================================

During each request, Symfony will set a global template variable ``app``
in both Twig and PHP template engines by default. The ``app`` variable
is a :class:`Symfony\\Bundle\\FrameworkBundle\\Templating\\GlobalVariables`
instance which will give you access to some application specific variables
automatically:

``app.security`` (deprecated as of 2.6)
    The :class:`Symfony\\Component\\Security\\Core\\SecurityContext` object or
    ``null`` if there is none.
``app.user``
    The representation of the current user or ``null`` if there is none. The
    value stored in this variable can be a :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`
    object, any other object which implements a ``__toString()`` method or even
    a regular string.
``app.request``
    The :class:`Symfony\\Component\\HttpFoundation\\Request` object that represents
    the current request (depending on your application, this can be a sub-request
    or a regular request, as explained later).
``app.session``
    The :class:`Symfony\\Component\\HttpFoundation\\Session\\Session` object that
    represents the current user's session or ``null`` if there is none.
``app.environment``
    The name of the current environment (``dev``, ``prod``, etc).
``app.debug``
    True if in debug mode. False otherwise.

.. configuration-block::

    .. code-block:: html+twig

        <p>Username: {{ app.user.username }}</p>
        {% if app.debug %}
            <p>Request method: {{ app.request.method }}</p>
            <p>Application Environment: {{ app.environment }}</p>
        {% endif %}

    .. code-block:: html+php

        <p>Username: <?php echo $app->getUser()->getUsername() ?></p>
        <?php if ($app->getDebug()): ?>
            <p>Request method: <?php echo $app->getRequest()->getMethod() ?></p>
            <p>Application Environment: <?php echo $app->getEnvironment() ?></p>
        <?php endif ?>

.. tip::

    You can add your own global template variables, see
    :doc:`/templating/global_variables`.
