Injecting variables into all templates
======================================

Sometimes you want a variable to be accessable to all the templates you use, here's how.


Using parameters.ini
--------------------

If you set a parameter in parameters.ini then it is available in your templates.

.. code-block: ini
    [parameters]
        ga_tracking: UA-xxxxx-x

.. code-block: twig
    <p>Our google tracking code is: {{ga_tracking}} </p>

Defining a global in config.yml
-------------------------------

There is also a section in the twig configuration where you can define a global variable.

.. code-block: yaml

    twig:
        debug:            %kernel.debug%
        strict_variables: %kernel.debug%
        globals:
            ga_tracking: UA-xxxxx-x

Defining global functions or objects
------------------------------------

If you need to define a global function or object, then what you need is a service. See :doc `/book/service_container` 
for more information on how.



