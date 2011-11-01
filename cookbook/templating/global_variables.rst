Injecting variables into all templates
======================================

Sometimes you want a variable to be accessable to all the templates you use, here's how.

If you set a parameter in parameters.ini then you will have to make it accessable by 
defining it as a global in the twig section of config.yml.

.. code-block: ini
    [parameters]
        ga_tracking: UA-xxxxx-x

.. code-block: yaml
    twig:
        debug:            %kernel.debug%
        strict_variables: %kernel.debug%
        globals:
            ga_tracking: %ga_tracking%

.. code-block: twig
    <p>Our google tracking code is: {{ga_tracking}} </p>

Of course, you can also define the parameter directly in twig:


.. code-block: yaml
    twig:
        debug:            %kernel.debug%
        strict_variables: %kernel.debug%
        globals:
            ga_tracking: UA-xxxxx-x





