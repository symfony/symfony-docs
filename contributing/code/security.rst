Security Issues
===============

This document explains how Symfony security issues are handled by the Symfony
core team (Symfony being the code hosted on the main ``symfony/symfony`` `Git
repository`_).

Reporting a Security Issue
--------------------------

If you think that you have found a security issue in Symfony, don't use the
mailing-list or the bug tracker and don't publish it publicly. Instead, all
security issues must be sent to **security [at] symfony.com**. Emails sent to
this address are forwarded to the Symfony core-team private mailing-list.

Resolving Process
-----------------

For each report, we first try to confirm the vulnerability. When it is
confirmed, the core-team works on a solution following these steps:

1. Send an acknowledgement to the reporter;
2. Work on a patch;
3. Get a CVE identifier from mitre.org;
3. Write a security announcement for the official Symfony `blog`_ about the
   vulnerability. This post should contain the following information:

   * a title that always include the "Security release" string;
   * a description of the vulnerability;
   * the affected versions;
   * the possible exploits;
   * how to patch/upgrade/workaround affected applications;
   * the CVE identifier;
   * credits.
4. Send the patch and the announcement to the reporter for review;
5. Apply the patch to all maintained versions of Symfony;
6. Package new versions for all affected versions;
7. Publish the post on the official Symfony `blog`_ (it must also be added to
   the "`Security Advisories`_" category);
8. Update the security advisory list (see below).

.. note::

    Releases that include security issues should not be done on Saturday or
    Sunday, except if the vulnerability has been publicly posted.

.. note::

    While we are working on a patch, please do not reveal the issue publicly.

Security Advisories
-------------------

This section indexes security vulnerabilities that were fixed in Symfony
releases, starting from Symfony 1.0.0:

* November 29, 2012: `Security release: Symfony 2.0.19 and 2.1.4 <http://symfony.com/blog/security-release-symfony-2-0-19-and-2-1-4>`_
* November 25, 2012: `Security release: symfony 1.4.20 released  <http://symfony.com/blog/security-release-symfony-1-4-20-released>`_ (`CVE-2012-5574 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2012-5574>`_)
* August 28, 2012: `Security Release: Symfony 2.0.17 released <http://symfony.com/blog/security-release-symfony-2-0-17-released>`_
* May 30, 2012: `Security Release: symfony 1.4.18 released <http://symfony.com/blog/security-release-symfony-1-4-18-released>`_ (`CVE-2012-2667 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2012-2667>`_)
* February 24, 2012: `Security Release: Symfony 2.0.11 released <http://symfony.com/blog/security-release-symfony-2-0-11-released>`_
* November 16, 2011: `Security Release: Symfony 2.0.6 <http://symfony.com/blog/security-release-symfony-2-0-6>`_
* March 21, 2011: `symfony 1.3.10 and 1.4.10: security releases <http://symfony.com/blog/symfony-1-3-10-and-1-4-10-security-releases>`_
* June 29, 2010: `Security Release: symfony 1.3.6 and 1.4.6 <http://symfony.com/blog/security-release-symfony-1-3-6-and-1-4-6>`_
* May 31, 2010: `symfony 1.3.5 and 1.4.5 <http://symfony.com/blog/symfony-1-3-5-and-1-4-5>`_
* February 25, 2010: `Security Release: 1.2.12, 1.3.3 and 1.4.3 <http://symfony.com/blog/security-release-1-2-12-1-3-3-and-1-4-3>`_
* February 13, 2010: `symfony 1.3.2 and 1.4.2 <http://symfony.com/blog/symfony-1-3-2-and-1-4-2>`_
* April 27, 2009: `symfony 1.2.6: Security fix <http://symfony.com/blog/symfony-1-2-6-security-fix>`_
* October 03, 2008: `symfony 1.1.4 released: Security fix <http://symfony.com/blog/symfony-1-1-4-released-security-fix>`_
* May 14, 2008: `symfony 1.0.16 is out  <http://symfony.com/blog/symfony-1-0-16-is-out>`_
* April 01, 2008: `symfony 1.0.13 is out  <http://symfony.com/blog/symfony-1-0-13-is-out>`_
* March 21, 2008: `symfony 1.0.12 is (finally) out ! <http://symfony.com/blog/symfony-1-0-12-is-finally-out>`_
* June 25, 2007: `symfony 1.0.5 released (security fix) <http://symfony.com/blog/symfony-1-0-5-released-security-fix>`_

.. _Git repository:      https://github.com/symfony/symfony
.. _blog:                https://symfony.com/blog/
.. _Security Advisories: http://symfony.com/blog/category/security-advisories
