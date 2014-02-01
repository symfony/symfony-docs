.. index::
   single: Security; Advanced ACL concepts

How to use Advanced ACL Concepts
================================

The aim of this chapter is to give a more in-depth view of the ACL system, and
also explain some of the design decisions behind it.

Design Concepts
---------------

Symfony2's object instance security capabilities are based on the concept of
an Access Control List. Every domain object **instance** has its own ACL. The
ACL instance holds a detailed list of Access Control Entries (ACEs) which are
used to make access decisions. Symfony2's ACL system focuses on two main
objectives:

- providing a way to efficiently retrieve a large amount of ACLs/ACEs for your
  domain objects, and to modify them;
- providing a way to easily make decisions of whether a person is allowed to
  perform an action on a domain object or not.

As indicated by the first point, one of the main capabilities of Symfony2's
ACL system is a high-performance way of retrieving ACLs/ACEs. This is
extremely important since each ACL might have several ACEs, and inherit from
another ACL in a tree-like fashion. Therefore, no ORM is leveraged, instead
the default implementation interacts with your connection directly using Doctrine's
DBAL.

Object Identities
~~~~~~~~~~~~~~~~~

The ACL system is completely decoupled from your domain objects. They don't
even have to be stored in the same database, or on the same server. In order
to achieve this decoupling, in the ACL system your objects are represented
through object identity objects. Every time you want to retrieve the ACL for a
domain object, the ACL system will first create an object identity from your
domain object, and then pass this object identity to the ACL provider for
further processing.

Security Identities
~~~~~~~~~~~~~~~~~~~

This is analog to the object identity, but represents a user, or a role in
your application. Each role, or user has its own security identity.

.. versionadded:: 2.5
    For users, the security identity is based on the username. This means that,
    if for any reason, a user's username was to change, you must ensure its
    security identity is updated too. The
    :method:`MutableAclProvider::updateUserSecurityIdentity() <Symfony\\Component\\Security\\Acl\\Dbal\\MutableAclProvider::updateUserSecurityIdentity>`
    method is there to handle the update, it was introduced in Symfony 2.5.

Database Table Structure
------------------------

The default implementation uses five database tables as listed below. The
tables are ordered from least rows to most rows in a typical application:

- *acl_security_identities*: This table records all security identities (SID)
  which hold ACEs. The default implementation ships with two security
  identities:
  :class:`Symfony\\Component\\Security\\Acl\\Domain\\RoleSecurityIdentity` and
  :class:`Symfony\\Component\\Security\\Acl\\Domain\\UserSecurityIdentity`.
- *acl_classes*: This table maps class names to a unique ID which can be
  referenced from other tables.
- *acl_object_identities*: Each row in this table represents a single domain
  object instance.
- *acl_object_identity_ancestors*: This table allows all the ancestors of
  an ACL to be determined in a very efficient way.
- *acl_entries*: This table contains all ACEs. This is typically the table
  with the most rows. It can contain tens of millions without significantly
  impacting performance.

Scope of Access Control Entries
-------------------------------

Access control entries can have different scopes in which they apply. In
Symfony2, there are basically two different scopes:

- Class-Scope: These entries apply to all objects with the same class.
- Object-Scope: This was the scope solely used in the previous chapter, and
  it only applies to one specific object.

Sometimes, you will find the need to apply an ACE only to a specific field of
the object. Suppose you want the ID only to be viewable by an administrator,
but not by your customer service. To solve this common problem, two more sub-scopes
have been added:

- Class-Field-Scope: These entries apply to all objects with the same class,
  but only to a specific field of the objects.
- Object-Field-Scope: These entries apply to a specific object, and only to a
  specific field of that object.

Pre-Authorization Decisions
---------------------------

For pre-authorization decisions, that is decisions made before any secure method (or
secure action) is invoked, the proven AccessDecisionManager service is used.
The AccessDecisionManager is also used for reaching authorization decisions based
on roles. Just like roles, the ACL system adds several new attributes which may be
used to check for different permissions.

Built-in Permission Map
~~~~~~~~~~~~~~~~~~~~~~~

+------------------+----------------------------+-----------------------------+
| Attribute        | Intended Meaning           | Integer Bitmasks            |
+==================+============================+=============================+
| VIEW             | Whether someone is allowed | VIEW, EDIT, OPERATOR,       |
|                  | to view the domain object. | MASTER, or OWNER            |
+------------------+----------------------------+-----------------------------+
| EDIT             | Whether someone is allowed | EDIT, OPERATOR, MASTER,     |
|                  | to make changes to the     | or OWNER                    |
|                  | domain object.             |                             |
+------------------+----------------------------+-----------------------------+
| CREATE           | Whether someone is allowed | CREATE, OPERATOR, MASTER,   |
|                  | to create the domain       | or OWNER                    |
|                  | object.                    |                             |
+------------------+----------------------------+-----------------------------+
| DELETE           | Whether someone is allowed | DELETE, OPERATOR, MASTER,   |
|                  | to delete the domain       | or OWNER                    |
|                  | object.                    |                             |
+------------------+----------------------------+-----------------------------+
| UNDELETE         | Whether someone is allowed | UNDELETE, OPERATOR, MASTER, |
|                  | to restore a previously    | or OWNER                    |
|                  | deleted domain object.     |                             |
+------------------+----------------------------+-----------------------------+
| OPERATOR         | Whether someone is allowed | OPERATOR, MASTER, or OWNER  |
|                  | to perform all of the above|                             |
|                  | actions.                   |                             |
+------------------+----------------------------+-----------------------------+
| MASTER           | Whether someone is allowed | MASTER, or OWNER            |
|                  | to perform all of the above|                             |
|                  | actions, and in addition is|                             |
|                  | allowed to grant           |                             |
|                  | any of the above           |                             |
|                  | permissions to others.     |                             |
+------------------+----------------------------+-----------------------------+
| OWNER            | Whether someone owns the   | OWNER                       |
|                  | domain object. An owner can|                             |
|                  | perform any of the above   |                             |
|                  | actions *and* grant master |                             |
|                  | and owner permissions.     |                             |
+------------------+----------------------------+-----------------------------+

Permission Attributes vs. Permission Bitmasks
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Attributes are used by the AccessDecisionManager, just like roles. Often, these
attributes represent in fact an aggregate of integer bitmasks. Integer bitmasks on
the other hand, are used by the ACL system internally to efficiently store your
users' permissions in the database, and perform access checks using extremely
fast bitmask operations.

Extensibility
~~~~~~~~~~~~~

The above permission map is by no means static, and theoretically could be
completely replaced at will. However, it should cover most problems you
encounter, and for interoperability with other bundles, you are encouraged to
stick to the meaning envisaged for them.

Post Authorization Decisions
----------------------------

Post authorization decisions are made after a secure method has been invoked,
and typically involve the domain object which is returned by such a method.
After invocation providers also allow to modify, or filter the domain object
before it is returned.

Due to current limitations of the PHP language, there are no
post-authorization capabilities build into the core Security component.
However, there is an experimental JMSSecurityExtraBundle_ which adds these
capabilities. See its documentation for further information on how this is
accomplished.

Process for Reaching Authorization Decisions
--------------------------------------------

The ACL class provides two methods for determining whether a security identity
has the required bitmasks, ``isGranted`` and ``isFieldGranted``. When the ACL
receives an authorization request through one of these methods, it delegates
this request to an implementation of
:class:`Symfony\\Component\\Security\\Acl\\Domain\\PermissionGrantingStrategy`.
This allows you to replace the way access decisions are reached without actually
modifying the ACL class itself.

The ``PermissionGrantingStrategy`` first checks all your object-scope ACEs. If none
is applicable, the class-scope ACEs will be checked. If none is applicable,
then the process will be repeated with the ACEs of the parent ACL. If no
parent ACL exists, an exception will be thrown.

.. _JMSSecurityExtraBundle: https://github.com/schmittjoh/JMSSecurityExtraBundle
