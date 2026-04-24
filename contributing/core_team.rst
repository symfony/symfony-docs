Symfony Core Team
=================

The **Symfony Core** team is the group of developers that determine the
direction and evolution of the Symfony project. Their votes rule if the
features and patches proposed by the community are approved or rejected.

All the Symfony Core members are long-time contributors with solid technical
expertise and they have demonstrated a strong commitment to drive the project
forward.

This document states the rules that govern the Symfony core team. These rules
are effective upon publication of this document and all Symfony Core members
must adhere to said rules and protocol.

Core Team Member Role
---------------------

In addition to being a regular contributor, core team members are expected to:

* Review, approve, and merge pull requests;
* Help enforce, improve, and implement Symfony :doc:`processes and policies </contributing/index>`;
* Participate in the Symfony Core Team discussions (on Slack and GitHub).

Core Team Member Responsibilities
---------------------------------

Core Team members are unpaid volunteers and as such, they are not expected to
dedicate any specific amount of time on Symfony. They are expected to help the
project in any way they can. From reviewing pull requests and writing documentation,
to participating in discussions and helping the community in general. However,
their involvement is completely voluntary and can be as much or as little as
they want.

Core Team Communication
~~~~~~~~~~~~~~~~~~~~~~~

As an open source project, public discussions and documentation is favored
over private ones. All communication in the Symfony community conforms to
the :doc:`/contributing/code_of_conduct/code_of_conduct`. Request
assistance from other Core and CARE team members when getting in situations
not following the Code of Conduct.

Core Team members are invited in a private Slack channel, for quick
interactions and private processes (e.g. security issues). Each member
should feel free to ask for assistance for anything they may encounter.
Expect no judgement from other team members.

Core Organization
-----------------

Symfony Core members are divided into groups. Each member can only belong to one
group at a time. The privileges granted to a group are automatically granted to
all higher priority groups.

The Symfony Core groups, in descending order of priority, are as follows:

1. **Project Leader**

   * Elects members in any other group;
   * Merges pull requests in all Symfony repositories.

2. **Mergers Team**

   * Merge pull requests on the main Symfony repository.

In addition, there are other groups created to manage specific topics:

* **Security Team**: manages the whole security process (triaging reported vulnerabilities,
  fixing the reported issues, coordinating the release of security fixes, etc.);
* **Symfony UX Team**: manages the `UX repositories`_;
* **Symfony CLI Team**: manages the `CLI repositories`_;
* **Documentation Team**: manages the whole `symfony-docs repository`_.

Active Core Members
~~~~~~~~~~~~~~~~~~~

* **Project Leader**:

  * **Fabien Potencier** (`fabpot`_).

* **Mergers Team** (``@symfony/mergers`` on GitHub):

  * **Nicolas Grekas** (`nicolas-grekas`_);
  * **Christophe Coevoet** (`stof`_);
  * **Christian Flothmann** (`xabbuh`_);
  * **Kévin Dunglas** (`dunglas`_);
  * **Javier Eguiluz** (`javiereguiluz`_);
  * **Grégoire Pineau** (`lyrixx`_);
  * **Robin Chalas** (`chalasr`_);
  * **Yonel Ceruto** (`yceruto`_);
  * **Tobias Nyholm** (`Nyholm`_);
  * **Wouter De Jong** (`wouterj`_);
  * **Alexander M. Turek** (`derrabus`_);
  * **Jérémy Derussé** (`jderusse`_);
  * **Tugdual Saunier** (`tucksaun`_);
  * **Oskar Stark** (`OskarStark`_);
  * **Mathieu Santostefano** (`welcomattic`_);
  * **Kevin Bond** (`kbond`_);
  * **Jérôme Tamarelle** (`gromnan`_);
  * **Berislav Balogović** (`hypemc`_);
  * **Mathias Arlaud** (`mtarld`_);
  * **Florent Morselli** (`spomky`_);
  * **Alexandre Daubois** (`alexandre-daubois`_);
  * **Christopher Hertel** (`chr-hertel`_).

* **Security Team** (``@symfony/security`` on GitHub):

  * **Fabien Potencier** (`fabpot`_);
  * **Jérémy Derussé** (`jderusse`_).

* **Symfony UX Team** (``@symfony/ux`` on GitHub):

  * **Kevin Bond** (`kbond`_);
  * **Simon André** (`smnandre`_);
  * **Hugo Alliaume** (`kocal`_);
  * **Matheo Daninos** (`webmamba`_).

* **Symfony CLI Team** (``@symfony-cli/core`` on GitHub):

  * **Fabien Potencier** (`fabpot`_);
  * **Tugdual Saunier** (`tucksaun`_).

* **Documentation Team** (``@symfony/team-symfony-docs`` on GitHub):

  * **Fabien Potencier** (`fabpot`_);
  * **Christian Flothmann** (`xabbuh`_);
  * **Wouter De Jong** (`wouterj`_);
  * **Javier Eguiluz** (`javiereguiluz`_);
  * **Oskar Stark** (`OskarStark`_).

Former Core Members
~~~~~~~~~~~~~~~~~~~

They are no longer part of the core team, but we are very grateful for all their
Symfony contributions:

* **Bernhard Schussek** (`webmozart`_);
* **Abdellatif AitBoudad** (`aitboudad`_);
* **Romain Neutron** (`romainneutron`_);
* **Jordi Boggiano** (`Seldaek`_);
* **Lukas Kahwe Smith** (`lsmith77`_);
* **Jules Pietri** (`HeahDude`_);
* **Jakub Zalas** (`jakzal`_);
* **Samuel Rozé** (`sroze`_);
* **Tobias Schultze** (`Tobion`_);
* **Maxime Steinhausser** (`ogizanagi`_);
* **Titouan Galopin** (`tgalopin`_);
* **Michael Cullum** (`michaelcullum`_);
* **Thomas Calvet** (`fancyweb`_);
* **Ryan Weaver** (`weaverryan`_).

Core Membership Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~

About once a year, the core team discusses the opportunity to invite new members.

Core Membership Revocation
~~~~~~~~~~~~~~~~~~~~~~~~~~

A Symfony Core membership can be revoked for any of the following reasons:

* Refusal to follow the rules and policies stated in this document;
* Lack of activity for the past six months;
* Willful negligence or intent to harm the Symfony project;
* Upon decision of the **Project Leader**.

Core Membership Compensation
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Core Team members work on Symfony on a purely voluntary basis. In return
for their work for the Symfony project, members can get free access to
Symfony conferences. Personal vouchers for Symfony conferences are handed out
on request by the **Project Leader**.

Code Development Rules
----------------------

Symfony project development is based on pull requests proposed by any member
of the Symfony community. Pull request acceptance or rejection is decided based
on the votes cast by the Symfony Core members.

Pull Request Voting Policy
~~~~~~~~~~~~~~~~~~~~~~~~~~

* ``-1`` votes must always be justified by technical and objective reasons;

* ``+1`` votes do not require justification, unless there is at least one
  ``-1`` vote;

* Core members can change their votes as many times as they desire
  during the course of a pull request discussion;
* Core members are not allowed to vote on their own pull requests.

Pull Request Merging Policy
~~~~~~~~~~~~~~~~~~~~~~~~~~~

A pull request **can be merged** if:

* It is an :ref:`unsubstantial change <core-team_unsubstantial-changes>`;
* Enough time was given for peer reviews;
* It is a bug fix and at least two **Mergers Team** members voted ``+1``
  (only one if the submitter is part of the Mergers team) and no Core
  member voted ``-1`` (via GitHub reviews or as comments).
* It is a new feature and at least two **Mergers Team** members voted
  ``+1`` (if the submitter is part of the Mergers team, two *other* members)
  and no Core member voted ``-1`` (via GitHub reviews or as comments).

.. _core-team_unsubstantial-changes:

.. note::

    Unsubstantial changes comprise typos, DocBlock fixes, code standards
    fixes, comment, exception message tweaks, and minor CSS, JavaScript and
    HTML modifications.

Pull Request Merging Process
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All code must be committed to the repository through pull requests, except
for :ref:`unsubstantial change <core-team_unsubstantial-changes>` which can be
committed directly to the repository.

**Mergers** must always use the command-line ``gh`` tool provided by the
**Project Leader** to merge pull requests.

When merging a pull request, the tool asks for a category that should be chosen
following these rules:

* **Feature**: For new features and deprecations; Pull requests must be merged
  in the development branch.
* **Bug**: Only for bug fixes; We are very conservative when it comes to
  merging older, but still maintained, branches. Read the :doc:`/contributing/code/maintenance`
  document for more information.
* **Data**: For updates of data files such as translations, ICU/intl data and
  mime types.
* **Minor**: For everything that does not change the behavior or when they don't
  need to be listed in the CHANGELOG files: typos in variable names or comments,
  Markdown files, test files, code style fixes, etc. A component with only
  **minor** changes since its last release will not be tagged in the next patch
  release.
* **Security**: It's the category used for security fixes and should never be
  used except by the security team.

Getting the right category is important as it is used by automated tools to
generate the CHANGELOG files when releasing new versions.

.. tip::

    Core team members are part of the ``mergers`` group on the ``symfony``
    Github organization. This gives them write-access to many repositories,
    including the main ``symfony/symfony`` mono-repository.

    To avoid unintentional pushes to the main project (which in turn creates
    new versions on Packagist), Core team members are encouraged to have
    two clones of the project locally:

    #. A clone for their own contributions, which they use to push to their
       fork on GitHub. Clear out the push URL for the Symfony repository using
       ``git remote set-url --push origin dev://null`` (change ``origin``
       to the Git remote pointing to the Symfony repository);
    #. A clone for merging, which they use in combination with ``gh`` and
       allows them to push to the main repository.

Upmerging Version Branches
~~~~~~~~~~~~~~~~~~~~~~~~~~

To synchronize changes in all versions, version branches are regularly
merged from oldest to latest, called "upmerging". This is a manual process.
There is no strict policy on when this occurs, but usually not more than
once a day and at least once before monthly releases.

Before starting the upmerge, Git must be configured to provide a merge
summary by running:

.. code-block:: terminal

    # Run command in the "symfony" repository
    $ git config merge.stat true

The upmerge should always be done on all maintained versions at the same
time. Refer to `the releases page`_ to find all actively maintained
versions (indicated by a green color).

The process follows these steps:

#. Start on the oldest version and make sure it's up to date with the
   upstream repository;
#. Check-out the second oldest version, update from upstream and merge the
   previous version from the local branch;
#. Continue this process until you reached the latest version;
#. Push the branches to the repository and monitor the test suite. Failure
   might indicate hidden/missed merge conflicts.

.. code-block:: terminal

    # 'origin' is referred to as the main upstream project
    $ git fetch origin

    # update the local branches
    $ git checkout 6.4
    $ git reset --hard origin/6.4
    $ git checkout 7.2
    $ git reset --hard origin/7.2
    $ git checkout 7.3
    $ git reset --hard origin/7.3

    # upmerge 6.4 into 7.2
    $ git checkout 7.2
    $ git merge --no-ff 6.4
    # ... resolve conflicts
    $ git commit

    # upmerge 7.2 into 7.3
    $ git checkout 7.3
    $ git merge --no-ff 7.2
    # ... resolve conflicts
    $ git commit

    $ git push origin 7.3 7.2 6.4

.. warning::

    Upmerges must be explicit, i.e. no fast-forward merges.

.. tip::

    Solving merge conflicts can be challenging. You can always ping other
    Core team members to help you in the process (e.g. members that merged
    a specific conflicting change).

Release Policy
~~~~~~~~~~~~~~

The **Project Leader** is also the release manager for every Symfony version.

Bugfix releases are published every month for each actively maintained version.
However, a package is not released if there are no new commits since its last
release, or if all new commits belong to the **minor** category (which doesn't
warrant a new release).

Symfony Core Rules and Protocol Amendments
------------------------------------------

The rules described in this document may be amended at any time at the
discretion of the **Project Leader**.

.. _`symfony-docs repository`: https://github.com/symfony/symfony-docs
.. _`UX repositories`: https://github.com/symfony/ux
.. _`CLI repositories`: https://github.com/symfony-cli
.. _`fabpot`: https://github.com/fabpot/
.. _`webmozart`: https://github.com/webmozart/
.. _`Tobion`: https://github.com/Tobion/
.. _`nicolas-grekas`: https://github.com/nicolas-grekas/
.. _`stof`: https://github.com/stof/
.. _`dunglas`: https://github.com/dunglas/
.. _`jakzal`: https://github.com/jakzal/
.. _`Seldaek`: https://github.com/Seldaek/
.. _`weaverryan`: https://github.com/weaverryan/
.. _`aitboudad`: https://github.com/aitboudad/
.. _`xabbuh`: https://github.com/xabbuh/
.. _`javiereguiluz`: https://github.com/javiereguiluz/
.. _`lyrixx`: https://github.com/lyrixx/
.. _`chalasr`: https://github.com/chalasr/
.. _`ogizanagi`: https://github.com/ogizanagi/
.. _`Nyholm`: https://github.com/Nyholm
.. _`sroze`: https://github.com/sroze
.. _`yceruto`: https://github.com/yceruto
.. _`michaelcullum`: https://github.com/michaelcullum
.. _`wouterj`: https://github.com/wouterj
.. _`HeahDude`: https://github.com/HeahDude
.. _`OskarStark`: https://github.com/OskarStark
.. _`romainneutron`: https://github.com/romainneutron
.. _`lsmith77`: https://github.com/lsmith77/
.. _`derrabus`: https://github.com/derrabus/
.. _`jderusse`: https://github.com/jderusse/
.. _`tgalopin`: https://github.com/tgalopin/
.. _`fancyweb`: https://github.com/fancyweb/
.. _`welcomattic`: https://github.com/welcomattic/
.. _`kbond`: https://github.com/kbond/
.. _`gromnan`: https://github.com/gromnan/
.. _`smnandre`: https://github.com/smnandre/
.. _`kocal`: https://github.com/kocal/
.. _`webmamba`: https://github.com/webmamba/
.. _`hypemc`: https://github.com/hypemc/
.. _`mtarld`: https://github.com/mtarld/
.. _`spomky`: https://github.com/spomky/
.. _`alexandre-daubois`: https://github.com/alexandre-daubois/
.. _`tucksaun`: https://github.com/tucksaun/
.. _`chr-hertel`: https://github.com/chr-hertel/
.. _`the releases page`: https://symfony.com/releases
