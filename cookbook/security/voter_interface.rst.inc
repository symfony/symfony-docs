.. code-block:: php

    interface VoterInterface
    {
        public function supportsAttribute($attribute);
        public function supportsClass($class);
        public function vote(TokenInterface $token, $object, array $attributes);
    }

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsAttribute`
method is used to check if the voter supports the given user attribute (i.e:
a role like ``ROLE_USER``, an ACL ``EDIT``, etc.).

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::supportsClass`
method is used to check if the voter supports the class of the object whose
access is being checked.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface::vote`
method must implement the business logic that verifies whether or not the
user has access. This method must return one of the following values:

* ``VoterInterface::ACCESS_GRANTED``: The authorization will be granted by this voter;
* ``VoterInterface::ACCESS_ABSTAIN``: The voter cannot decide if authorization should be granted;
* ``VoterInterface::ACCESS_DENIED``: The authorization will be denied by this voter.
