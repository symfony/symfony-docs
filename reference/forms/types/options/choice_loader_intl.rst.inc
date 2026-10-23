``choice_loader``
~~~~~~~~~~~~~~~~~

**type**: :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\ChoiceLoaderInterface` **default**: a loader based on :class:`Symfony\\Component\\Form\\ChoiceList\\Loader\\IntlCallbackChoiceLoader`

The default loader gets the choice names from the Intl component. The field's
options control which names to load and how to translate them. To provide a
custom loader, see :ref:`reference-form-choice-loader`.
