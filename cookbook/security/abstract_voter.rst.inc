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
and developer is expected to implement the abstract methods.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::getSupportedClasses`
method tells Symfony that your voter should be called whenever an object of one of the given classes
is passed to `isGranted`  For example, if you return ['\Acme\DemoBundle\Model\Product'],
Symfony will call your voter when a `Product` object is passed to `isGranted`.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::getSupportedAttributes`
method tells Symfony that your voter should be called whenever one of these strings is passes as the
first argument to `isGranted`. For example, if you return `array('CREATE', 'READ')`, then
Symfony will call your voter when one of these is passed to `isGranted`.

The :method:`Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AbstractVoter::isGranted`
method must implement the business logic that verifies whether or not a given
user is allowed access to a given attribute on a given object. This method must return a boolean.
