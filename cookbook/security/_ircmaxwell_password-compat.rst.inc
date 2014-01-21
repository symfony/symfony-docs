.. caution::

    If you're using PHP 5.4 or lower, you'll need to install the ``ircmaxell/password-compat``
    library via Composer in order to be able to use the ``bcrypt`` encoder:

    .. code-block:: json

        {
            "require": {
                ...
                "ircmaxell/password-compat": "~1.0.3"
            }
        }
