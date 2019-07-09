.. _running-symfony2-tests:

Running Symfony Tests
=====================

The Symfony project uses a third-party service which automatically runs tests
for any submitted :doc:`patch <pull_requests>`. If the new code breaks any test,
the pull request will show an error message with a link to the full error details.

In any case, it's a good practice to run tests locally before submitting a
:doc:`patch <pull_requests>` for inclusion, to check that you have not broken anything.

.. _phpunit:
.. _dependencies_optional:

Before Running the Tests
------------------------

To run the Symfony test suite, install the external dependencies used during the
tests, such as Doctrine, Twig and Monolog. To do so,
`install Composer`_ and execute the following:

.. code-block:: terminal

    $ composer update

.. _running:

Running the Tests
-----------------

Then, run the test suite from the Symfony root directory with the following
command:

.. code-block:: terminal

    $ php ./phpunit symfony

The output should display ``OK``. If not, read the reported errors to figure out
what's going on and if the tests are broken because of the new code.

.. tip::

    The entire Symfony suite can take up to several minutes to complete. If you
    want to test a single component, type its path after the ``phpunit`` command,
    e.g.:

    .. code-block:: terminal

        $ php ./phpunit src/Symfony/Component/Finder/Tests

.. tip::

    On Windows, install the `Cmder`_, `ConEmu`_, `ANSICON`_ or `Mintty`_ free applications
    to see colored test results.

.. _`install Composer`: https://getcomposer.org/download/
.. _Cmder: http://cmder.net/
.. _ConEmu: https://conemu.github.io/
.. _ANSICON: https://github.com/adoxa/ansicon/releases
.. _Mintty: https://mintty.github.io/
