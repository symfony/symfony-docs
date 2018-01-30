.. index::
   single: Expressions in the Framework

Security: Complex Access Controls with Expressions
==================================================

.. seealso::

    The best solution for handling complex authorization rules is to use
    the :doc:`Voter System </security/voters>`.

In addition to a role like ``ROLE_ADMIN``, the ``isGranted()`` method also
accepts an :class:`Symfony\\Component\\ExpressionLanguage\\Expression` object::

    use Symfony\Component\ExpressionLanguage\Expression;
    // ...

    public function index()
    {
        $this->denyAccessUnlessGranted(new Expression(
            '"ROLE_ADMIN" in roles or (user and user.isSuperAdmin())'
        ));

        // ...
    }

In this example, if the current user has ``ROLE_ADMIN`` or if the current
user object's ``isSuperAdmin()`` method returns ``true``, then access will
be granted (note: your User object may not have an ``isSuperAdmin()`` method,
that method is invented for this example).

This uses an expression and you can learn more about the expression language
syntax, see :doc:`/components/expression_language/syntax`.

.. _security-expression-variables:

Inside the expression, you have access to a number of variables:

``user``
    The user object (or the string ``anon`` if you're not authenticated).
``roles``
    The array of roles the user has, including from the
    :ref:`role hierarchy <security-role-hierarchy>` but not including the
    ``IS_AUTHENTICATED_*`` attributes (see the functions below).
``object``
     The object (if any) that's passed as the second argument to ``isGranted()``.
``token``
    The token object.
``trust_resolver``
    The :class:`Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationTrustResolverInterface`,
    object: you'll probably use the ``is_*()`` functions below instead.

Additionally, you have access to a number of functions inside the expression:

``is_authenticated``
    Returns ``true`` if the user is authenticated via "remember-me" or authenticated
    "fully" - i.e. returns true if the user is "logged in".
``is_anonymous``
    Equal to using ``IS_AUTHENTICATED_ANONYMOUSLY`` with the ``isGranted()`` function.
``is_remember_me``
    Similar, but not equal to ``IS_AUTHENTICATED_REMEMBERED``, see below.
``is_fully_authenticated``
    Similar, but not equal to ``IS_AUTHENTICATED_FULLY``, see below.
``has_role``
    Checks to see if the user has the given role - equivalent to an expression like
    ``'ROLE_ADMIN' in roles``.

.. sidebar:: ``is_remember_me`` is different than checking ``IS_AUTHENTICATED_REMEMBERED``

    The ``is_remember_me()`` and ``is_authenticated_fully()`` functions are *similar*
    to using ``IS_AUTHENTICATED_REMEMBERED`` and ``IS_AUTHENTICATED_FULLY``
    with the ``isGranted()`` function - but they are **not** the same. The
    following controller snippet shows the difference::

        use Symfony\Component\ExpressionLanguage\Expression;
        use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
        // ...

        public function index(AuthorizationCheckerInterface $auth)
        {
            $access1 = $auth->isGranted('IS_AUTHENTICATED_REMEMBERED');

            $access2 = $auth->isGranted(new Expression(
                'is_remember_me() or is_fully_authenticated()'
            ));
        }

    Here, ``$access1`` and ``$access2`` will be the same value. Unlike the
    behavior of ``IS_AUTHENTICATED_REMEMBERED`` and ``IS_AUTHENTICATED_FULLY``,
    the ``is_remember_me()`` function *only* returns true if the user is authenticated
    via a remember-me cookie and ``is_fully_authenticated`` *only* returns
    true if the user has actually logged in during this session (i.e. is
    full-fledged).

Learn more
----------

* :doc:`/service_container/expression_language`
* :doc:`/reference/constraints/Expression`
