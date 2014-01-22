.. code-block:: php

    interface VoterInterface
    {
        public function supportsAttribute($attribute);
        public function supportsClass($class);
        public function vote(TokenInterface $token, object, array $attributes);
    }

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsAttribute`
method is used to check if the voter supports the given user attribute (i.e: a role, an acl, etc.).

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsClass`
method is used to check if the voter supports the current user token class.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::vote`
method must implement the business logic that verifies whether or not the
user is granted access. This method must return one of the following values:

* ``VoterInterface::ACCESS_GRANTED``: The user is allowed to access the
application
* ``VoterInterface::ACCESS_ABSTAIN``: The voter cannot decide if the user
is granted or not
* ``VoterInterface::ACCESS_DENIED``: The user is not allowed to access
the application