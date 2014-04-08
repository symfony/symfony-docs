Symfony Core Team
=================

This document states the rules that govern the Symfony Core group. These rules
are effective upon publication of this document and all Symfony Core members
must adhere to said rules and protocol.

Core Organization
-----------------

Symfony Core members are divided into three groups. Each member can only belong
to one group at a time. The privileges granted to a group are automatically
granted to all higher priority groups.

The Symfony Core groups, in descending order of priority, are as follows:

1. **Project Leader**

* Elects members in any other group;
* Merges pull requests in all Symfony repositories.

2. **Mergers**

* Merge pull requests for the component or components on which they have been
  granted privileges.

3. **Deciders**

* Decide to merge or reject a pull request.

Active Core Members
~~~~~~~~~~~~~~~~~~~

.. role:: leader
.. role:: merger
.. role:: decider

* **Project Leader**:

  * **Fabien Potencier** (:leader:`fabpot`).

* **Mergers**:

  * **Bernhard Schussek** (:merger:`webmozart`) can merge into the Form_,
    Validator_, Icu_, Intl_, Locale_, OptionsResolver_ and PropertyAccess_
    components;

  * **Tobias Schultze** (:merger:`Tobion`) can merge into the Routing_
    component;

  * **Romain Neutron** (:merger:`romainneutron`) can merge into the
    Process_ component;

  * **Nicolas Grekas** (:merger:`nicolas-grekas`) can merge into the Debug_
    component.

* **Deciders**:

  * **Christophe Coevoet** (:decider:`stof`);
  * **Jakub Zalas** (:decider:`jakzal`);
  * **Jordi Boggiano** (:decider:`seldaek`);
  * **Lukas Kahwe Smith** (:decider:`lsmith77`).

Core Membership Application
~~~~~~~~~~~~~~~~~~~~~~~~~~~

At present, new Symfony Core membership applications are not accepted.

Core Membership Revocation
~~~~~~~~~~~~~~~~~~~~~~~~~~

A Symfony Core membership can be revoked for any of the following reasons:

* Refusal to follow the rules and policies stated in this document;
* Lack of activity for the past six months;
* Willful negligence or intent to harm the Symfony project;
* Upon decision of the **Project Leader**.

Should new Symfony Core memberships be accepted in the future, revoked
members must wait at least 12 months before re-applying.

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

* Enough time was given for peer reviews (a few minutes for typos or minor
  changes, at least 2 days for "regular" pull requests, and 4 days for pull
  requests with "a significant impact");

* It is a minor change [1]_, regardless of the number of votes;

* At least the component's **Merger** or two other Core members voted ``+1``
  and no Core member voted ``-1``.

Pull Request Merging Process
~~~~~~~~~~~~~~~~~~~~~~~~~~~~

All code must be committed to the repository through pull requests, except for
minor changes [1]_ which can be committed directly to the repository.

**Mergers** must always use the command-line ``gh`` tool provided by the
**Project Leader** to merge the pull requests.

Release Policy
~~~~~~~~~~~~~~

The **Project Leader** is also the release manager for every Symfony version.

Symfony Core Rules and Protocol Amendments
------------------------------------------

The rules described in this document may be amended at anytime at the
discretion of the **Project Leader**.


.. [1] Minor changes comprise typos, DocBlock fixes, code standards
       violations, and minor CSS, JavaScript and HTML modifications.

.. _Form: https://github.com/symfony/Form
.. _Validator: https://github.com/symfony/Validator
.. _Icu: https://github.com/symfony/Icu
.. _Intl: https://github.com/symfony/Intl
.. _Locale: https://github.com/symfony/Locale
.. _OptionsResolver: https://github.com/symfony/OptionsResolver
.. _PropertyAccess: https://github.com/symfony/PropertyAccess
.. _Routing: https://github.com/symfony/Routing
.. _Process: https://github.com/symfony/Process
.. _Debug: https://github.com/symfony/Debug
