.. index::
   single: Templating; Global variables

How to Inject Variables into all Templates (i.e. Global Variables)
==================================================================

Sometimes you want a variable to be accessible to all the templates you use.
This is possible inside your ``app/config/config.yml`` file:

.. code-block:: yaml

    # app/config/config.yml
    twig:
        # ...
        globals:
            ga_tracking: UA-xxxxx-x

Now, the variable ``ga_tracking`` is available in all Twig templates:

.. code-block:: html+jinja

    <p>Our google tracking code is: {{ ga_tracking }} </p>

It's that easy! You can also take advantage of the built-in :ref:`book-service-container-parameters`
system, which lets you isolate or reuse the value:

.. code-block:: ini

    ; app/config/parameters.yml
    [parameters]
        ga_tracking: UA-xxxxx-x

.. code-block:: yaml

    # app/config/config.yml
    twig:
        globals:
            ga_tracking: %ga_tracking%

The same variable is available exactly as before.

More Complex Global Variables
-----------------------------

If the global variable you want to set is more complicated - say an object -
then you won't be able to use the above method. Instead, you'll need to create
a :ref:`Twig Extension<reference-dic-tags-twig-extension>` and return the
global variable as one of the entries in the ``getGlobals`` method.
