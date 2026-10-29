Operating a KMS from the Command Line
=====================================

Four commands operate the configured clients.

.. include:: /key_management/_experimental.rst.inc

Encrypting and Decrypting a Value
---------------------------------

The ``key-management:encrypt`` and ``key-management:decrypt`` commands read
standard input when their payload argument is omitted and write their
diagnostics to standard error, so you can combine them in a pipeline:

.. code-block:: terminal

    # a value given as an argument shows up in the shell history and in the
    # process list, so pass a secret through the standard input instead
    $ php bin/console key-management:encrypt app-key 'hello world'
    $ php bin/console key-management:encrypt app-key < secrets.json
    $ php bin/console key-management:decrypt < envelope.b64

    # re-encrypting under another key after a rotation
    $ php bin/console key-management:decrypt < old.b64 \
        | php bin/console key-management:encrypt new-key > new.b64

These two commands only work with payloads carrying their own data key.
The command line has no store to resolve a stored data key with, so read
those payloads through the application. Once the application has a
:doc:`store </key_management/store>`, the envelope interfaces read what
``key-management:encrypt`` writes only when ``read_self_contained`` is on. The
envelope encrypter of each client always reads it.

Creating a Data Key
-------------------

The ``key-management:generate-data-key`` command prints JSON holding the
key id, the length, and the plaintext and wrapped forms in base64. This is
how you create the key of a :doc:`blind index </key_management/searching>`:

.. code-block:: terminal

    $ php bin/console key-management:generate-data-key app-key

.. warning::

    The plaintext data key is printed to standard output. Redirect it to a
    file instead of displaying it in the terminal, and keep only the
    wrapped form.

Re-wrapping the Stored Data Keys
--------------------------------

The ``key-management:rewrap-data-keys`` command needs a configured
:doc:`store </key_management/store>`. It unwraps each row with the client
that row records, wraps it again with ``--to``, and commits row by row, so
you can resume an interrupted run by running the same command again. No
payload is read or rewritten:

.. code-block:: terminal

    # list the keys that would move first; a dry run contacts no KMS, so it
    # doesn't check that the keys can be unwrapped
    $ php bin/console key-management:rewrap-data-keys --to=azure \
        --key-id=my-vault-key --dry-run -v

    # only the keys still wrapped by the old client
    $ php bin/console key-management:rewrap-data-keys --from=aws --to=azure \
        --key-id=my-vault-key

``--to`` defaults to the ``client`` of the store, and ``--key-id`` to its
``key_id`` when ``--to`` is that client. Without options, the command re-wraps
every key under the configuration of the store:

.. code-block:: terminal

    $ php bin/console key-management:rewrap-data-keys

Both ``--to`` and the clients recorded in the rows must be trusted by the
store: they must be its ``client`` or be listed in its ``allowed_clients``
option. The command also signs every key again with the first ``secret`` of
the store, which is how an old secret is retired.

Before re-wrapping a row, the command checks its
:ref:`binding <key-management-store-binding>`, as the store does when reading
it. The command reports each row that fails this check, or that its client
cannot unwrap, and leaves it unchanged. When any row is left unchanged, the
command fails: fix the reported causes and run it again.

When :ref:`bind_wrapping_context <key-management-store-binding>` is on, the
command wraps every key with the context of its row. The keys stored before
the option was turned on have no context yet, so pass ``--accept-unbound``
once to re-wrap them:

.. code-block:: terminal

    $ php bin/console key-management:rewrap-data-keys --accept-unbound

Common Options
--------------

The encrypt, decrypt and data key commands take ``--client`` to pick a
client, which is required as soon as several are registered and none is
named ``default``, and ``--aad`` to pass authenticated data.
``key-management:generate-data-key`` also takes ``--length``, 16 bytes
minimum.
