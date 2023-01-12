.. index::
   single: Expressions in the Framework

Using Expressions in Security Access Controls
=============================================

.. seealso::

    The best solution for handling complex authorization rules is to use
    the :doc:`Voter System </security/voters>`.

In addition to a role like ``ROLE_ADMIN``, the ``isGranted()`` method also
accepts an :class:`Symfony\\Component\\ExpressionLanguage\\Expression` object::

    // src/Controller/MyController.php
    namespace App\Controller;

    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\ExpressionLanguage\Expression;
    use Symfony\Component\HttpFoundation\Response;

    class MyController extends AbstractController
    {
        public function index(): Response
        {
            $this->denyAccessUnlessGranted(new Expression(
                '"ROLE_ADMIN" in role_names or (is_authenticated() and user.isSuperAdmin())'
            ));

            // ...
        }
    }

In this example, if the current user has ``ROLE_ADMIN`` or if the current
user object's ``isSuperAdmin()`` method returns ``true``, then access will
be granted (note: your User object may not have an ``isSuperAdmin()`` method,
that method is invented for this example).

This uses an expression and you can learn more about the expression language
syntax, see :ref:`expression-language-syntax`.

.. _security-expression-variables:

Inside the expression, you have access to a number of variables:

``user``
    The user object (or the string ``anon`` if you're not authenticated).
``role_names``
    An array with the string representation of the roles the user has. This array
    includes any roles granted indirectly via the :ref:`role hierarchy <security-role-hierarchy>` but it
    does not include the ``IS_AUTHENTICATED_*`` attributes (see the functions below).
``object``
    The object (if any) that's passed as the second argument to ``isGranted()``.
``subject``
    It stores the same value as ``object``, so they are equivalent.
``token``
    The token object.
``trust_resolver``
    The :class:`Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationTrustResolverInterface`,
    object: you'll probably use the ``is_*()`` functions below instead.

Additionally, you have access to a number of functions inside the expression:

``is_authenticated()``
    Returns ``true`` if the user is authenticated via "remember-me" or authenticated
    "fully" - i.e. returns true if the user is "logged in".
``is_anonymous()``
    Returns ``true`` if the user is anonymous. That is, the firewall confirms that it
    does not know this user's identity. This is different from ``IS_AUTHENTICATED_ANONYMOUSLY``,
    which is granted to *all* users, including authenticated ones.
``is_remember_me()``
    Similar, but not equal to ``IS_AUTHENTICATED_REMEMBERED``, see below.
``is_fully_authenticated()``
    Equal to checking if the user has the ``IS_AUTHENTICATED_FULLY`` role.
``is_granted()``
    Checks if the user has the given permission. Optionally accepts a
    second argument with the object where permission is checked on. It's
    equivalent to using the :ref:`isGranted() method <security-isgranted>`
    from the security service.

.. deprecated:: 5.4

   The ``is_anonymous()`` function is deprecated since Symfony 5.4.

.. sidebar:: ``is_remember_me()`` is different than checking ``IS_AUTHENTICATED_REMEMBERED``

    The ``is_remember_me()`` and ``is_fully_authenticated()`` functions are *similar*
    to using ``IS_AUTHENTICATED_REMEMBERED`` and ``IS_AUTHENTICATED_FULLY``
    with the ``isGranted()`` function - but they are **not** the same. The
    following controller snippet shows the difference::

        use Symfony\Component\ExpressionLanguage\Expression;
        use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
        // ...

        public function index(AuthorizationCheckerInterface $authorizationChecker): Response
        {
            $access1 = $authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED');

            $access2 = $authorizationChecker->isGranted(new Expression(
                'is_remember_me() or is_fully_authenticated()'
            ));
        }

    Here, ``$access1`` and ``$access2`` will be the same value. Unlike the
    behavior of ``IS_AUTHENTICATED_REMEMBERED`` and ``IS_AUTHENTICATED_FULLY``,
    the ``is_remember_me()`` function *only* returns true if the user is authenticated
    via a remember-me cookie and ``is_fully_authenticated()`` *only* returns
    true if the user has actually logged in during this session (i.e. is
    full-fledged).

Learn more
----------

* :doc:`/service_container/expression_language`
* :doc:`/reference/constraints/Expression`
