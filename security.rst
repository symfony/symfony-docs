.. index::
   single: Security

Security
========

Security is commonly the most tricky part to implement in any web application.
In addition to learning and implementing a lot of different features, you must
always be aware of the latest best practices defined in the security industry.

Symfony abstracts all these details and provides out-of-the-box support for the
most common security features (such as login forms and storing users in the
database) and provides extension points for your custom needs.

This article lists the main articles related to security, but you don't need to
read them all. In fact, for the typical web application, you just need to
:doc:`create users in the database <...>`, :doc:`display a login form <...>` and
:doc:`check permissions with firewalls <...>`.

Managing Users
--------------

* :doc:`Creating Users in a Config File <...>`
* :doc:`Creating Users in a Database <...>`
* :doc:`Custom User Creation <...>`
* :doc:`Assigning Permissions to Users <...>`
* :doc:`Hashing and Checking Passwords <...>`
* :doc:`Impersonating Users <...>`

Authenticating Users
--------------------

**Authentication** is the process followed by the users to log in the application.

* :doc:`Authenticating Users with a Login Form <...>`
* :doc:`Authenticating Users with HTTP Requests <...>`
* :doc:`Authenticating Users with LDAP <...>`
* :doc:`Authenticating Users with JSON <...>`
* :doc:`Custom Authentication Mechanisms (WSEE, SAML) <...>`
* :doc:`Logging Out <...>`

Restricting Access and Checking Permissions
-------------------------------------------

**Authorization** is the process followed by Symfony to decide if the user can
perform a certain action.

* :doc:`Checking Permissions with Firewalls (access_control) <...>`
* :doc:`Checking Permissions with Custom Business Logic (voters) <...>`
* :doc:`Checking Permissions in Services, Controllers and Templates <...>`
* :doc:`Custom Authorization Mechanisms <...>`

Other Security Articles
-----------------------

* :doc:`Checking Permissions with ACLs (Access Control Lists) <security/acl>`
* :doc:`Forcing HTTPS or HTTP for Different URLs <security/force_https>`
* :doc:`Check your Application for Known Security Vulnerabilities <security/security_checker>`
