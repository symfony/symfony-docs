.. index::
    single: Controller; Validating CSRF Tokens

How to Manually Validate a CSRF Token in a Controller
=====================================================

Sometimes, you want to use CSRF protection in an action where you do not
want to use the Symfony Form component. If, for example, you are implementing
a DELETE action, you can use the :method:`Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller::isCsrfTokenValid`
method to check the validity of a CSRF token::

    public function deleteAction()
    {
        if ($this->isCsrfTokenValid('token_id', $submittedToken)) {
            // ... do something, like deleting an object
        }
    }

.. versionadded:: 2.6
    The ``isCsrfTokenValid()`` shortcut method was introduced in Symfony 2.6.
    It is equivalent to executing the following code:

    .. code-block:: php

        use Symfony\Component\Security\Csrf\CsrfToken;

        $this->get('security.csrf.token_manager')
            ->isTokenValid(new CsrfToken('token_id', 'TOKEN'));
