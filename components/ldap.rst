.. index::
   single: Ldap
   single: Components; Ldap

The Ldap Component
==================

    The Ldap component provides a means to connect to an LDAP server (OpenLDAP or Active Directory).

Installation
------------

.. code-block:: terminal

    $ composer require symfony/ldap

.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Ldap\\Ldap` class provides methods to authenticate
and query against an LDAP server.

The ``Ldap`` class uses an :class:`Symfony\\Component\\Ldap\\Adapter\\AdapterInterface`
to communicate with an LDAP server. The :class:`adapter <Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\Adapter>`
for PHP's built-in LDAP extension, for example, can be configured using the
following options:

``host``
    IP or hostname of the LDAP server

``port``
    Port used to access the LDAP server

``version``
    The version of the LDAP protocol to use

``encryption``
    The encryption protocol: ``ssl``, ``tls`` or ``none`` (default)

``connection_string``
    You may use this option instead of ``host`` and ``port`` to connect to the
    LDAP server

``optReferrals``
    Specifies whether to automatically follow referrals returned by the LDAP server

``options``
    LDAP server's options as defined in
    :class:`ConnectionOptions <Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\ConnectionOptions>`

For example, to connect to a start-TLS secured LDAP server::

    use Symfony\Component\Ldap\Ldap;

    $ldap = Ldap::create('ext_ldap', [
        'host' => 'my-server',
        'encryption' => 'ssl',
    ]);

Or you could directly specify a connection string::

    use Symfony\Component\Ldap\Ldap;

    $ldap = Ldap::create('ext_ldap', ['connection_string' => 'ldaps://my-server:636']);

The :method:`Symfony\\Component\\Ldap\\Ldap::bind` method
authenticates a previously configured connection using both the
distinguished name (DN) and the password of a user::

    use Symfony\Component\Ldap\Ldap;
    // ...

    $ldap->bind($dn, $password);

.. caution::

    When the LDAP server allows unauthenticated binds, a blank password will always be valid.

Once bound (or if you enabled anonymous authentication on your
LDAP server), you may query the LDAP server using the
:method:`Symfony\\Component\\Ldap\\Ldap::query` method::

    use Symfony\Component\Ldap\Ldap;
    // ...

    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $results = $query->execute();

    foreach ($results as $entry) {
        // Do something with the results
    }

By default, LDAP entries are lazy-loaded. If you wish to fetch
all entries in a single call and do something with the results'
array, you may use the
:method:`Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\Collection::toArray` method::

    use Symfony\Component\Ldap\Ldap;
    // ...

    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $results = $query->execute()->toArray();

    // Do something with the results array

By default, LDAP queries use the ``Symfony\Component\Ldap\Adapter\QueryInterface::SCOPE_SUB``
scope, which corresponds to the ``LDAP_SCOPE_SUBTREE`` scope of the
:phpfunction:`ldap_search` function. You can also use ``SCOPE_BASE`` (related
to the ``LDAP_SCOPE_BASE`` scope of :phpfunction:`ldap_read`) and ``SCOPE_ONE``
(related to the ``LDAP_SCOPE_ONELEVEL`` scope of :phpfunction:`ldap_list`)::

    use Symfony\Component\Ldap\Adapter\QueryInterface;

    $query = $ldap->query('dc=symfony,dc=com', '...', ['scope' => QueryInterface::SCOPE_ONE]);

Use the ``filter`` option to only retrieve some specific attributes:

    $query = $ldap->query('dc=symfony,dc=com', '...', ['filter' => ['cn, mail']);

Creating or Updating Entries
----------------------------

The Ldap component provides means to create new LDAP entries, update or even
delete existing ones::

    use Symfony\Component\Ldap\Entry;
    use Symfony\Component\Ldap\Ldap;
    // ...

    $entry = new Entry('cn=Fabien Potencier,dc=symfony,dc=com', [
        'sn' => ['fabpot'],
        'objectClass' => ['inetOrgPerson'],
    ]);

    $entryManager = $ldap->getEntryManager();

    // Creating a new entry
    $entryManager->add($entry);

    // Finding and updating an existing entry
    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $result = $query->execute();
    $entry = $result[0];

    $phoneNumber = $entry->getAttribute('phoneNumber');
    $isContractor = $entry->hasAttribute('contractorCompany');
    // attribute names in getAttribute() and hasAttribute() methods are case-sensitive
    // pass FALSE as the second method argument to make them case-insensitive
    $isContractor = $entry->hasAttribute('contractorCompany', false);

    $entry->setAttribute('email', ['fabpot@symfony.com']);
    $entryManager->update($entry);

    // Adding or removing values to a multi-valued attribute is more efficient than using update()
    $entryManager->addAttributeValues($entry, 'telephoneNumber', ['+1.111.222.3333', '+1.222.333.4444']);
    $entryManager->removeAttributeValues($entry, 'telephoneNumber', ['+1.111.222.3333', '+1.222.333.4444']);

    // Removing an existing entry
    $entryManager->remove(new Entry('cn=Test User,dc=symfony,dc=com'));

.. versionadded:: 5.3

    The option to make attribute names case-insensitive in ``getAttribute()``
    and ``hasAttribute()`` was introduce in Symfony 5.3.

Batch Updating
______________

Use the entry manager's :method:`Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\EntryManager::applyOperations`
method to update multiple attributes at once::

    use Symfony\Component\Ldap\Entry;
    use Symfony\Component\Ldap\Ldap;
    // ...

    $entry = new Entry('cn=Fabien Potencier,dc=symfony,dc=com', [
        'sn' => ['fabpot'],
        'objectClass' => ['inetOrgPerson'],
    ]);

    $entryManager = $ldap->getEntryManager();

    // Adding multiple email addresses at once
    $entryManager->applyOperations($entry->getDn(), [
        new UpdateOperation(LDAP_MODIFY_BATCH_ADD, 'mail', 'new1@example.com'),
        new UpdateOperation(LDAP_MODIFY_BATCH_ADD, 'mail', 'new2@example.com'),
    ]);

Possible operation types are ``LDAP_MODIFY_BATCH_ADD``, ``LDAP_MODIFY_BATCH_REMOVE``,
``LDAP_MODIFY_BATCH_REMOVE_ALL``, ``LDAP_MODIFY_BATCH_REPLACE``. Parameter
``$values`` must be ``NULL`` when using ``LDAP_MODIFY_BATCH_REMOVE_ALL``
operation type.
