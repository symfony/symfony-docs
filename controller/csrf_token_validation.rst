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
