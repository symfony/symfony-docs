translation_count
~~~~~~~~~~~~~~~~~

.. versionadded:: 2.7
    The ``translation_count`` option was introduced in Symfony 2.7.

**type**: ``integer`` **default**: 1

An integer that will be passed to ``transchoice`` to allow the pluralization of
any labels that are rendered for this button.

``translation_count`` will also be passed as a variable named ``count`` so you can use
translation strings like this:

    {0} No items{1} One item|]1,Inf] %count% items
