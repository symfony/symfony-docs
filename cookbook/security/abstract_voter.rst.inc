.. code-block:: php

    abstract class AbstractVoter implements VoterInterface
    {
        public function supportsAttribute($attribute);
        public function supportsClass($class);
        public function vote(TokenInterface $token, $object, array $attributes);

        abstract protected function getSupportedClasses();
        abstract protected function getSupportedAttributes();
        abstract protected function isGranted($attribute, $object, $user = null);
    }

Behind the scenes this class implements the
:class:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface`,
which has this structure:

.. include:: /cookbook/security/voter_interface.rst.inc

The basic functionality covering common use cases is provided
and end developer is expected to implement the abstract methods.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::getSupportedClasses`
method is used to provide an array of supported classes, i.e. ['\Acme\DemoBundle\Model\Product']

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::getSupportedAttributes`
method is used to provide an array of supported attributes, i.e. ['CREATE', 'READ']

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::isGranted`
method must implement the business logic that verifies whether or not a given
user is allowed a given attribute on a given object. This method must return a boolean.