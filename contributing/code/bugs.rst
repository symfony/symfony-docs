Reporting a Bug
===============

Whenever you find a bug in Symfony, we kindly ask you to report it. It helps
us make a better Symfony.

.. caution::

    If you think you've found a security issue, please use the special
    :doc:`procedure <security>` instead.

Before submitting a bug:

* Double-check the official :doc:`documentation </index>` to see if you're not misusing the
  framework;

* Ask for assistance on `Stack Overflow`_ on the #symfony `IRC channel`_, or on
  the #support channel of `the Symfony Slack`_ if you're not sure if your issue
  is really a bug.

If your problem definitely looks like a bug, report it using the official bug
`tracker`_ and follow some basic rules:

* Use the title field to clearly describe the issue;

* Describe the steps needed to reproduce the bug with short code examples
  (providing a unit test that illustrates the bug is best);

* If the bug you experienced affects more than one layer, providing a simple
  failing unit test may not be sufficient. In this case, please fork the
  `Symfony Standard Edition`_ and reproduce your issue on a new branch;

* Give as much detail as possible about your environment (OS, PHP version,
  Symfony version, enabled extensions, ...);

* If you want to provide a stack trace you got on an html page, be sure to
  provide the plain text version, which should appear at the bottom of the
  page. *Do not* provide it as a screenshot, since search engines will not be
  able to index the text inside them. Same goes for errors encountered in a
  terminal, do not take a screenshot, but learn how to copy/paste from it. If
  the stack trace is long, consider enclosing it in a `<details> html tag`_.
  **Be wary that stack traces may contain sensitive information, and if it is
  the case, be sure to redact them prior to posting your stack trace.**

* *(optional)* Attach a :doc:`patch <patches>`.

.. _`Stack Overflow`: http://stackoverflow.com/questions/tagged/symfony2
.. _IRC channel: https://symfony.com/irc
.. _the Symfony Slack: https://symfony.com/slack-invite
.. _tracker: https://github.com/symfony/symfony/issues
.. _Symfony Standard Edition: https://github.com/symfony/symfony-standard/
.. _<details> html tag: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/details
