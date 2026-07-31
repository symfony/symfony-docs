rate_limiter
~~~~~~~~~~~~

enabled
.......

**type**: ``boolean`` **default**: ``true`` or ``false`` depending on your installation

.. _reference-rate-limiter-name:

limiters
........

**type**: ``prototype``

A list of rate limiters to create, keyed by the limiter name.

cache_pool
""""""""""

**type**: ``string`` **default**: ``cache.rate_limiter``

The cache pool to use for storing the current limiter state.

interval
""""""""

**type**: ``string``

Configures the fixed interval if ``policy`` is set to ``fixed_window`` or
``sliding_window``. The value must be a number followed by ``second``, ``minute``,
``hour``, ``day``, ``week`` or ``month`` (or their plural equivalent).

limit
"""""

**type**: ``integer``

The maximum allowed hits in a fixed interval or burst. Required when the
``policy`` is not ``compound`` or ``no_limit``.

limiters
""""""""

**type**: ``array``

The limiter names to use when using the ``compound`` policy.

lock_factory
""""""""""""

**type**: ``string`` **default**: ``auto``

The service ID of the lock factory used by this limiter (or ``null`` to disable
locking).

policy
""""""

**type**: ``string`` **required**

The name of the rate limiting algorithm to use. Allowed values are ``fixed_window``,
``token_bucket``, ``sliding_window``, ``compound`` and ``no_limit``.
See :ref:`Rate Limiter Policies <rate-limiter-policies>`)
for more information.

rate
""""

**type**: ``array``

Configures the fill rate if ``policy`` is set to ``token_bucket``.

* ``interval`` (**type**: ``string``) the rate interval. The value must be a
  number followed by ``second``, ``minute``, ``hour``, ``day``, ``week`` or
  ``month`` (or their plural equivalent).
* ``amount`` (**type**: ``integer`` **default**: ``1``) the amount of tokens to
  add each interval.

storage_service
"""""""""""""""

**type**: ``string`` **default**: ``null``

The service ID of a custom storage implementation. This takes precedence over
any configured ``cache_pool``.
