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
forwarded to the Symfony core team private mailing-list.

Resolving Process
-----------------

For each report, we first try to confirm the vulnerability. When it is
confirmed, the core team works on a solution following these steps:

#. Send an acknowledgement to the reporter;
#. Work on a patch;
#. Get a CVE identifier from `mitre.org`_;
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
    using the ``security:check`` command (see :doc:`/security/security_checker`).

Check the `Security Advisories`_ blog category for a list of all security
vulnerabilities that were fixed in Symfony releases, starting from Symfony
1.0.0.

.. _Git repository: https://github.com/symfony/symfony
.. _blog: https://symfony.com/blog/
.. _Security Advisories: https://symfony.com/blog/category/security-advisories
.. _`security advisories database`: https://github.com/FriendsOfPHP/security-advisories
.. _`mitre.org`: https://cveform.mitre.org/
.. _`Security Advisories`: https://symfony.com/blog/category/security-advisories
