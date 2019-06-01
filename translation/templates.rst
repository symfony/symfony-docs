Using Translation in Templates
==============================

Twig Templates
--------------

.. _translation-tags:

Using Twig Tags
~~~~~~~~~~~~~~~

Symfony provides specialized Twig tags (``trans`` and ``transchoice``) to
help with message translation of *static blocks of text*:

.. code-block:: twig

    {% trans %}Hello %name%{% endtrans %}

    {% transchoice count %}
        {0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples
    {% endtranschoice %}

The ``transchoice`` tag automatically gets the ``%count%`` variable from
the current context and passes it to the translator. This mechanism only
works when you use a placeholder following the ``%var%`` pattern.

.. deprecated:: 4.2

    The ``transchoice`` tag is deprecated since Symfony 4.2 and will be
    removed in 5.0. Use the :doc:`ICU MessageFormat </translation/message_format>` with
    the ``trans`` tag instead.

.. caution::

    The ``%var%`` notation of placeholders is required when translating in
    Twig templates using the tag.

.. tip::

    If you need to use the percent character (``%``) in a string, escape it by
    doubling it: ``{% trans %}Percent: %percent%%%{% endtrans %}``

You can also specify the message domain and pass some additional variables:

.. code-block:: twig

    {% trans with {'%name%': 'Fabien'} from 'app' %}Hello %name%{% endtrans %}

    {% trans with {'%name%': 'Fabien'} from 'app' into 'fr' %}Hello %name%{% endtrans %}

    {% transchoice count with {'%name%': 'Fabien'} from 'app' %}
        {0} %name%, there are no apples|{1} %name%, there is one apple|]1,Inf[ %name%, there are %count% apples
    {% endtranschoice %}

.. _translation-filters:

Using Twig Filters
~~~~~~~~~~~~~~~~~~

The ``trans`` and ``transchoice`` filters can be used to translate *variable
texts* and complex expressions:

.. code-block:: twig

    {{ message|trans }}

    {{ message|transchoice(5) }}

    {{ message|trans({'%name%': 'Fabien'}, 'app') }}

    {{ message|transchoice(5, {'%name%': 'Fabien'}, 'app') }}

.. deprecated:: 4.2

    The ``transchoice`` filter is deprecated since Symfony 4.2 and will be
    removed in 5.0. Use the :doc:`ICU MessageFormat </translation/message_format>` with
    the ``trans`` filter instead.

.. tip::

    Using the translation tags or filters have the same effect, but with
    one subtle difference: automatic output escaping is only applied to
    translations using a filter. In other words, if you need to be sure
    that your translated message is *not* output escaped, you must apply
    the ``raw`` filter after the translation filter:

    .. code-block:: html+twig

        {# text translated between tags is never escaped #}
        {% trans %}
            <h3>foo</h3>
        {% endtrans %}

        {% set message = '<h3>foo</h3>' %}

        {# strings and variables translated via a filter are escaped by default #}
        {{ message|trans|raw }}
        {{ '<h3>bar</h3>'|trans|raw }}

.. tip::

    You can set the translation domain for an entire Twig template with a single tag:

    .. code-block:: twig

       {% trans_default_domain 'app' %}

    Note that this only influences the current template, not any "included"
    template (in order to avoid side effects).

PHP Templates
-------------

The translator service is accessible in PHP templates through the
``translator`` helper::

    <?= $view['translator']->trans('Symfony is great') ?>

    <?= $view['translator']->transChoice(
        '{0} There are no apples|{1} There is one apple|]1,Inf[ There are %count% apples',
        10,
        ['%count%' => 10]
    ) ?>
