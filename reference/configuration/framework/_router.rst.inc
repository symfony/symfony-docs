router
~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``false``

cache_dir
.........

**type**: ``string`` **default**: ``%kernel.cache_dir%``

The directory where routing information will be cached. Can be set to
``~`` (``null``) to disable route caching.

.. deprecated:: 7.1

    Setting the ``cache_dir`` option is deprecated since Symfony 7.1. The routes
    are now always cached in the ``%kernel.build_dir%`` directory.

default_uri
...........

**type**: ``string``

The default URI used to generate URLs in a non-HTTP context (see
:ref:`Generating URLs in Commands <router-generate-urls-commands>`).

http_port
.........

**type**: ``integer`` **default**: ``80``

The port for normal http requests (this is used when matching the scheme).

https_port
..........

**type**: ``integer`` **default**: ``443``

The port for https requests (this is used when matching the scheme).

resource
........

**type**: ``string`` **required**

The path to the main routing resource (e.g. a YAML file) that contains the
routes and imports the router should load.

strict_requirements
...................

**type**: ``mixed`` **default**: ``true``

Determines the routing generator behavior. When generating a route that
has specific :ref:`parameter requirements <routing-requirements>`, the generator
can behave differently in case the used parameters do not meet these requirements.

The value can be one of:

``true``
    Throw an exception when the requirements are not met;
``false``
    Disable exceptions when the requirements are not met and return ``''``
    instead;
``null``
    Disable checking the requirements (thus, match the route even when the
    requirements don't match).

``true`` is recommended in the development environment, while ``false``
or ``null`` might be preferred in production.

.. _reference-router-type:

type
....

**type**: ``string``

The type of the resource to hint the loaders about the format. This isn't
needed when you use the default routers with the expected file extensions
(``.xml``, ``.yaml``, ``.php``).

utf8
....

**type**: ``boolean`` **default**: ``true``

When this option is set to ``true``, the regular expressions used in the
:ref:`requirements of route parameters <routing-requirements>` will be run
using the `utf-8 modifier`_. This will for example match any UTF-8 character
when using ``.``, instead of matching only a single byte.

If the charset of your application is UTF-8 (as defined in the
:ref:`getCharset() method <configuration-kernel-charset>` of your kernel) it's
recommended setting it to ``true``. This will make non-UTF8 URLs generate 404
errors.

.. _`utf-8 modifier`: https://www.php.net/reference.pcre.pattern.modifiers
