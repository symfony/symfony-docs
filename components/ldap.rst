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

Alternatively, you can clone the `<https://github.com/symfony/ldap>`_ repository.

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

    $ldap = Ldap::create('ext_ldap', array(
        'host' => 'my-server',
        'encryption' => 'ssl',
    ));

Or you could directly specify a connection string::

    use Symfony\Component\Ldap\Ldap;

    $ldap = Ldap::create('ext_ldap', array('connection_string' => 'ldaps://my-server:636'));

The :method:`Symfony\\Component\\Ldap\\Ldap::bind` method
authenticates a previously configured connection using both the
distinguished name (DN) and the password of a user::

    use Symfony\Component\Ldap\Ldap;
    // ...

    $ldap->bind($dn, $password);

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

Creating or Updating Entries
----------------------------

The Ldap component provides means to create new LDAP entries, update or even
delete existing ones::

    use Symfony\Component\Ldap\Ldap;
    use Symfony\Component\Ldap\Entry;
    // ...

    $entry = new Entry('cn=Fabien Potencier,dc=symfony,dc=com', array(
        'sn' => array('fabpot'),
        'objectClass' => array('inetOrgPerson'),
    ));

    $entityManager = $ldap->getEntryManager();

    // Creating a new entry
    $entityManager->add($entry);

    // Finding and updating an existing entry
    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $result = $query->execute();
    $entry = $result[0];
    $entry->setAttribute('email', array('fabpot@symfony.com'));
    $entityManager->update($entry);

    // Removing an existing entry
    $entityManager->remove(new Entry('cn=Test User,dc=symfony,dc=com'));

.. _Packagist: https://packagist.org/packages/symfony/ldap
