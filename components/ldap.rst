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

``useSsl``
    Whether or not to secure the connection using SSL

``useStartTls``
    Whether or not to secure the connection using StartTLS

``optReferrals``
    Specifies whether to automatically follow referrals
    returned by the LDAP server

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
:method:`Symfony\\Component\\Ldap\\Ldap::find` method::

    // ...

    $ldap->find('dc=symfony,dc=com', '(&(objectclass=person)(ou=Maintainers))');

.. _Packagist: https://packagist.org/packages/symfony/ldap
