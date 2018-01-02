.. index::
   single: Ldap
   single: Components; Ldap

The Ldap Component
==================

    The Ldap component provides a means to connect to an LDAP server (OpenLDAP or Active Directory).

Installation
------------

You can install the component in 2 different ways:

* :doc:`Install it via Composer </components/using_components>` (``symfony/ldap`` on `Packagist`_);
* Use the official Git repository (https://github.com/symfony/ldap).

.. include:: /components/require_autoload.rst.inc

Usage
-----

The :class:`Symfony\\Component\\Ldap\\Ldap` class provides methods to authenticate
and query against an LDAP server.

The ``Ldap`` class uses an :class:`Symfony\\Component\\Ldap\\Adapter\\AdapterInterface`
to communicate with an LDAP server. The :class:`adapter <Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\Adapter>`
for PHP's built-in LDAP extension, for example, can be configured
using the following options:

``host``
    IP or hostname of the LDAP server

``port``
    Port used to access the LDAP server

``version``
    The version of the LDAP protocol to use

``encryption``
    The encryption protocol : ``ssl``, ``tls`` or ``none`` (default)

``options``
    LDAP server's options as defined in :class:`ConnectionOptions <Symfony\\Component\\Ldap\\Adapter\\ExtLdap\\ConnectionOptions>`

For example, to connect to a start-TLS secured LDAP server::

    use Symfony\Component\Ldap\Adapter\ExtLdap\Adapter;
    use Symfony\Component\Ldap\Ldap;

    $adapter = new Adapter(array(
        'host' => 'my-server',
        'port' => 389,
        'encryption' => 'tls',
        'options' => array(
            'protocol_version' => 3,
            'referrals' => false,
        ),
    ));
    $ldap = new Ldap($adapter);

The :method:`Symfony\\Component\\Ldap\\Ldap::bind` method
authenticates a previously configured connection using both the
distinguished name (DN) and the password of a user::

    // ...

    $ldap->bind($dn, $password);

Once bound (or if you enabled anonymous authentication on your
LDAP server), you may query the LDAP server using the
:method:`Symfony\\Component\\Ldap\\Ldap::query` method::

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

    // ...

    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $results = $query->execute()->toArray();

    // Do something with the results array

Creating or updating entries
----------------------------

Since version 3.1, The Ldap component provides means to create
new LDAP entries, update or even delete existing ones::

    use Symfony\Component\Ldap\Entry;
    // ...

    $entry = new Entry('cn=Fabien Potencier,dc=symfony,dc=com', array(
        'sn' => array('fabpot'),
        'objectClass' => array('inetOrgPerson'),
    ));

    $em = $ldap->getEntryManager();

    // Creating a new entry
    $em->add($entry);

    // Finding and updating an existing entry
    $query = $ldap->query('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');
    $result = $query->execute();
    $entry = $result[0];
    $entry->addAttribute('email', array('fabpot@symfony.com'));
    $em->update($entry);

    // Removing an existing entry
    $em->remove(new Entry('cn=Test User,dc=symfony,dc=com'));

.. _Packagist: https://packagist.org/packages/symfony/ldap
