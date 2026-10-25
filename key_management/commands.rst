Operating a KMS from the Command Line
=====================================

Four commands operate the configured clients.

.. include:: /key_management/_experimental.rst.inc

Encrypting and Decrypting a Value
---------------------------------

Both read standard input when their payload argument is omitted and write
their diagnostics to standard error, so they compose in a pipeline:

.. code-block:: terminal

    $ php bin/console key-management:encrypt app-key 'hello world'
    $ cat secrets.json | php bin/console key-management:encrypt app-key
    $ php bin/console key-management:decrypt < envelope.b64

    # re-encrypting under another key after a rotation
    $ php bin/console key-management:decrypt < old.b64 \
        | php bin/console key-management:encrypt new-key > new.b64

These two always work with payloads carrying their own data key. One
referring to a stored data key is reported as such rather than decrypted,
since the command line has no store to resolve it with; read those through
the application.

Creating a Data Key
-------------------

Prints JSON holding the key id, the length, and the plaintext and wrapped
forms in base64. This is how the key of a
:doc:`blind index </key_management/searching>` is minted:

.. code-block:: terminal

    $ php bin/console key-management:generate-data-key app-key

.. warning::

    The plaintext data key reaches standard output. Redirect it to a file
    the shell history does not keep, and keep only the wrapped form.

Re-wrapping the Stored Data Keys
--------------------------------

Needs a configured :doc:`store </key_management/store>`, and reports that
nothing can be re-wrapped without one. It unwraps each row with the client
that row records, wraps it again with ``--to``, and commits row by row, so
an interrupted run is resumed by running the same command again. No payload
is read or rewritten:

.. code-block:: terminal

    # see what would move, first
    $ php bin/console key-management:rewrap-data-keys --to=azure \
        --key-id=my-vault-key --dry-run

    # only the keys still wrapped by the old client
    $ php bin/console key-management:rewrap-data-keys --from=aws --to=azure \
        --key-id=my-vault-key

The store must trust ``--to`` and the clients of the rows. That means its
``client`` option, or a client listed in its ``allowed_clients`` option. The
command also signs every key again with the first ``secret`` of the store, which
is how an old secret is retired.

Common Options
--------------

The first three commands take ``--client`` to pick a client, which is
required as soon as several are registered and none is named ``default``,
and ``--aad`` to pass authenticated data.
``key-management:generate-data-key`` also takes ``--length``, 16 bytes
minimum. ``key-management:rewrap-data-keys`` takes ``--accept-unbound``,
needed once when turning
:ref:`bind_wrapping_context <key-management-store-binding>` on, since the
keys already stored carry no context yet.
