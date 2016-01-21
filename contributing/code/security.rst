Security Issues
===============

This document explains how Symfony security issues are handled by the Symfony
core team (Symfony being the code hosted on the main ``symfony/symfony`` `Git
repository`_).

Reporting a Security Issue
--------------------------

If you think that you have found a security issue in Symfony, don't use the
bug tracker and don't publish it publicly. Instead, all security issues must
be sent to **security [at] symfony.com**. Emails sent to this address are
forwarded to the Symfony core-team private mailing-list.

Resolving Process
-----------------

For each report, we first try to confirm the vulnerability. When it is
confirmed, the core-team works on a solution following these steps:

#. Send an acknowledgement to the reporter;
#. Work on a patch;
#. Get a CVE identifier from mitre.org;
#. Write a security announcement for the official Symfony `blog`_ about the
   vulnerability. This post should contain the following information:

   * a title that always include the "Security release" string;
   * a description of the vulnerability;
   * the affected versions;
   * the possible exploits;
   * how to patch/upgrade/workaround affected applications;
   * the CVE identifier;
   * credits.
#. Send the patch and the announcement to the reporter for review;
#. Apply the patch to all maintained versions of Symfony;
#. Package new versions for all affected versions;
#. Publish the post on the official Symfony `blog`_ (it must also be added to
   the "`Security Advisories`_" category);
#. Update the security advisory list (see below).
#. Update the public `security advisories database`_ maintained by the
   FriendsOfPHP organization and which is used by the ``security:check`` command.

.. note::

    Releases that include security issues should not be done on Saturday or
    Sunday, except if the vulnerability has been publicly posted.

.. note::

    While we are working on a patch, please do not reveal the issue publicly.

.. note::

    The resolution takes anywhere between a couple of days to a month depending
    on its complexity and the coordination with the downstream projects (see
    next paragraph).

Collaborating with Downstream Open-Source Projects
--------------------------------------------------

As Symfony is used by many large Open-Source projects, we standardized the way
the Symfony security team collaborates on security issues with downstream
projects. The process works as follows:

#. After the Symfony security team has acknowledged a security issue, it
   immediately sends an email to the downstream project security teams to
   inform them of the issue;

#. The Symfony security team creates a private Git repository to ease the
   collaboration on the issue and access to this repository is given to the
   Symfony security team, to the Symfony contributors that are impacted by
   the issue, and to one representative of each downstream projects;

#. All people with access to the private repository work on a solution to
   solve the issue via pull requests, code reviews, and comments;

#. Once the fix is found, all involved projects collaborate to find the best
   date for a joint release (there is no guarantee that all releases will
   be at the same time but we will try hard to make them at about the same
   time). When the issue is not known to be exploited in the wild, a period
   of two weeks seems like a reasonable amount of time.

The list of downstream projects participating in this process is kept as small
as possible in order to better manage the flow of confidential information
prior to disclosure. As such, projects are included at the sole discretion of
the Symfony security team.

As of today, the following projects have validated this process and are part
of the downstream projects included in this process:

* Drupal (releases typically happen on Wednesdays)
* eZPublish

Security Advisories
-------------------

.. tip::

    You can check your Symfony application for known security vulnerabilities
    using the ``security:check`` command. See :ref:`book-security-checking-vulnerabilities`.

This section indexes security vulnerabilities that were fixed in Symfony
releases, starting from Symfony 1.0.0:

* November 23, 2015: `CVE-2015-8125: Potential Remote Timing Attack Vulnerability in Security Remember-Me Service <http://symfony.com/blog/cve-2015-8125-potential-remote-timing-attack-vulnerability-in-security-remember-me-service>`_ (2.3.35, 2.6.12 and 2.7.7)
* November 23, 2015: `CVE-2015-8124: Session Fixation in the "Remember Me" Login Feature <http://symfony.com/blog/cve-2015-8124-session-fixation-in-the-remember-me-login-feature>`_ (2.3.35, 2.6.12 and 2.7.7)
* May 26, 2015: `CVE-2015-4050: ESI unauthorized access <https://symfony.com/blog/cve-2015-4050-esi-unauthorized-access>`_ (Symfony 2.3.29, 2.5.12 and 2.6.8)
* April 1, 2015: `CVE-2015-2309: Unsafe methods in the Request class <https://symfony.com/blog/cve-2015-2309-unsafe-methods-in-the-request-class>`_ (Symfony 2.3.27, 2.5.11 and 2.6.6)
* April 1, 2015: `CVE-2015-2308: Esi Code Injection <https://symfony.com/blog/cve-2015-2308-esi-code-injection>`_ (Symfony 2.3.27, 2.5.11 and 2.6.6)
* September 3, 2014: `CVE-2014-6072: CSRF vulnerability in the Web Profiler <https://symfony.com/blog/cve-2014-6072-csrf-vulnerability-in-the-web-profiler>`_ (Symfony 2.3.19, 2.4.9 and 2.5.4)
* September 3, 2014: `CVE-2014-6061: Security issue when parsing the Authorization header <https://symfony.com/blog/cve-2014-6061-security-issue-when-parsing-the-authorization-header>`_ (Symfony 2.3.19, 2.4.9 and 2.5.4)
* September 3, 2014: `CVE-2014-5245: Direct access of ESI URLs behind a trusted proxy <https://symfony.com/blog/cve-2014-5245-direct-access-of-esi-urls-behind-a-trusted-proxy>`_ (Symfony 2.3.19, 2.4.9 and 2.5.4)
* September 3, 2014: `CVE-2014-5244: Denial of service with a malicious HTTP Host header <https://symfony.com/blog/cve-2014-5244-denial-of-service-with-a-malicious-http-host-header>`_ (Symfony 2.3.19, 2.4.9 and 2.5.4)
* July 15, 2014: `Security releases: Symfony 2.3.18, 2.4.8, and 2.5.2 released <https://symfony.com/blog/security-releases-cve-2014-4931-symfony-2-3-18-2-4-8-and-2-5-2-released>`_ (`CVE-2014-4931 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2014-4931>`_)
* October 10, 2013: `Security releases: Symfony 2.0.25, 2.1.13, 2.2.9, and 2.3.6 released <https://symfony.com/blog/security-releases-cve-2013-5958-symfony-2-0-25-2-1-13-2-2-9-and-2-3-6-released>`_ (`CVE-2013-5958 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2013-5958>`_)
* August 7, 2013: `Security releases: Symfony 2.0.24, 2.1.12, 2.2.5, and 2.3.3 released <https://symfony.com/blog/security-releases-symfony-2-0-24-2-1-12-2-2-5-and-2-3-3-released>`_ (`CVE-2013-4751 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2013-4751>`_ and `CVE-2013-4752 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2013-4752>`_)
* January 17, 2013: `Security release: Symfony 2.0.22 and 2.1.7 released <https://symfony.com/blog/security-release-symfony-2-0-22-and-2-1-7-released>`_ (`CVE-2013-1348 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2013-1348>`_ and `CVE-2013-1397 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2013-1397>`_)
* December 20, 2012: `Security release: Symfony 2.0.20 and 2.1.5 <https://symfony.com/blog/security-release-symfony-2-0-20-and-2-1-5-released>`_  (`CVE-2012-6431 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2012-6431>`_ and `CVE-2012-6432 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2012-6432>`_)
* November 29, 2012: `Security release: Symfony 2.0.19 and 2.1.4 <https://symfony.com/blog/security-release-symfony-2-0-19-and-2-1-4>`_
* November 25, 2012: `Security release: symfony 1.4.20 released  <https://symfony.com/blog/security-release-symfony-1-4-20-released>`_ (`CVE-2012-5574 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2012-5574>`_)
* August 28, 2012: `Security Release: Symfony 2.0.17 released <https://symfony.com/blog/security-release-symfony-2-0-17-released>`_
* May 30, 2012: `Security Release: symfony 1.4.18 released <https://symfony.com/blog/security-release-symfony-1-4-18-released>`_ (`CVE-2012-2667 <http://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2012-2667>`_)
* February 24, 2012: `Security Release: Symfony 2.0.11 released <https://symfony.com/blog/security-release-symfony-2-0-11-released>`_
* November 16, 2011: `Security Release: Symfony 2.0.6 <https://symfony.com/blog/security-release-symfony-2-0-6>`_
* March 21, 2011: `symfony 1.3.10 and 1.4.10: security releases <https://symfony.com/blog/symfony-1-3-10-and-1-4-10-security-releases>`_
* June 29, 2010: `Security Release: symfony 1.3.6 and 1.4.6 <https://symfony.com/blog/security-release-symfony-1-3-6-and-1-4-6>`_
* May 31, 2010: `symfony 1.3.5 and 1.4.5 <https://symfony.com/blog/symfony-1-3-5-and-1-4-5>`_
* February 25, 2010: `Security Release: 1.2.12, 1.3.3 and 1.4.3 <https://symfony.com/blog/security-release-1-2-12-1-3-3-and-1-4-3>`_
* February 13, 2010: `symfony 1.3.2 and 1.4.2 <https://symfony.com/blog/symfony-1-3-2-and-1-4-2>`_
* April 27, 2009: `symfony 1.2.6: Security fix <https://symfony.com/blog/symfony-1-2-6-security-fix>`_
* October 03, 2008: `symfony 1.1.4 released: Security fix <https://symfony.com/blog/symfony-1-1-4-released-security-fix>`_
* May 14, 2008: `symfony 1.0.16 is out  <https://symfony.com/blog/symfony-1-0-16-is-out>`_
* April 01, 2008: `symfony 1.0.13 is out  <https://symfony.com/blog/symfony-1-0-13-is-out>`_
* March 21, 2008: `symfony 1.0.12 is (finally) out ! <https://symfony.com/blog/symfony-1-0-12-is-finally-out>`_
* June 25, 2007: `symfony 1.0.5 released (security fix) <https://symfony.com/blog/symfony-1-0-5-released-security-fix>`_

.. _Git repository: https://github.com/symfony/symfony
.. _blog: https://symfony.com/blog/
.. _Security Advisories: https://symfony.com/blog/category/security-advisories
.. _`security advisories database`: https://github.com/FriendsOfPHP/security-advisories
