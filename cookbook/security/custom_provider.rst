How to create a custom User Provider
====================================

Symfony firewalls depend for their authentication on user providers. 
These providers are requested by the authentication layer to provide a 
user object for a given username. Symfony will check whether the 
password of this user is correct and will then generate a security token, 
so the user may stay authenticated during the current session. Out of 
the box, Symfony has an "in_memory" and an "entity" user provider. 
In this entry we'll see how you can create your own user provider.
This may be a custom type of database, file or, in this example,
a webservice.  

Create a user class
-------------------

First create the user class for your specific type of user. The class should 
implement :class:`Symfony\\Component\\Security\\Core\\User\\UserInterface`.
The methods in this interface should therefore be defined in the custom user 
class:

``getRoles()``
  Return an array with the role(s) for this user, e.g. ``array('ROLE_USER')``.

``getPassword()``
  Return the password of the user (this would better be an encrypted password, 
  see below).
   
``getSalt()``
  Return the "salt" that should be used for encrypting the password. Return 
  nothing (i.e. ``null``) when the password needs no salt.
  
``getUsername()``
  Return the username of this user, i.e. the name by which it can authenticate.
  
``eraseCredentials()``
  Return nothing, but erase sensitive data from the user object. Don't erase
  credentials.
  
``equals(UserInterface $user)``
  The first argument of this method is an object which implements ``UserInterface``. 
  When called upon a user, this method returns ``true`` when the given user is the same
  as itself, or ``false`` if it is not. This means you should compare several 
  crucial properties like username, password and salt. Symfony uses this method to 
  find out if the user should reauthenticate.

.. code-block:: php    

    namespace Acme\WebserviceUserBundle\Security\User;
    
    use Symfony\Component\Security\Core\User\UserInterface;
     
    class WebserviceUser implements UserInterface
    {
        private $username;
        private $password;
        private $salt;
        private $roles;
     
        public function __construct($username, $password, $salt, array $roles)
        {
            $this->username = $username;
            $this->password = $password;
            $this->salt = $salt;
            $this->roles = $roles;
        }
     
        public function getRoles()
        {
            return $this->roles;
        }
     
        public function getPassword()
        {
            return $this->password;
        }
     
        public function getSalt()
        {
            return $this->salt;
        }
     
        public function getUsername()
        {
            return $this->username;
        }   
     
        public function eraseCredentials()
        {
        }
     
        public function equals(UserInterface $user)
        {
            if (!$user instanceof WebserviceUser) {
                return false;
            }
     
            if ($this->password !== $user->getPassword()) {
                return false;
            }
     
            if ($this->getSalt() !== $user->getSalt()) {
                return false;
            }
     
            if ($this->username !== $user->getUsername()) {
                return false;
            }
     
            return true;
        }
    }

Create a user provider
----------------------

Next we will create a user provider, in this case a ``WebserviceUserProvider``. 
It provides the firewall with instances of ``WebserviceUser``. The user provider
 has to implement the 
 :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`, 
which requires three methods to be defined: 

``loadUserByUsername($username)``
  Does the actual loading of the user: it looks for a user with the given username 
  in any way that seems appropriate to it and returns a user object (in our example 
  a ``WebserviceUser``). If the user was not found, this method must throw a 
  ``UsernameNotFoundException``.

``refreshUser(UserInterface $user)``
  Refreshes the information of the given user. It must check if the given user object
  is an instance of the user class that is supported by this specific user provider. 
  If not, an ``UnsupportedUserException`` should be thrown.

``supportsClass($class)``
  Should return ``true`` if this user provider can handle users of the given class, 
  ``false`` if not.
    
.. code-block:: php    

    namespace Acme\WebserviceUserBundle\Security\User;
     
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
     
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
     
    class WebserviceUserProvider implements UserProviderInterface
    {
        public function loadUserByUsername($username)
        {
            try {
                // make a call to your webservice here:
                // throw a WebserviceUserNotFoundException (or something alike) if you did not find the requested user
                // $user = ...;
                
                return new WebserviceUser($user->getUsername(), $user->getPassword(), $user->getSalt(), $user->getRoles())
            }
            catch(WebserviceUserNotFoundException $e) {
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }
        }
     
        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof WebserviceUser) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
            }
     
            return $this->loadUserByUsername($user->getUsername());
        }
     
        public function supportsClass($class)
        {
            return $class === 'Acme\WebserviceUserBundle\Security\User\WebserviceUser';
        }
    }

Create a service for the user provider
--------------------------------------

Now we make the user provider available as service.

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/MailerBundle/Resources/config/services.yml
        parameters:
            webservice_user_provider.class: Acme\WebserviceUserBundle\Security\User\WebserviceUserProvider
            
        services:
            webservice_user_provider:
                class: %webservice_user_provider.class%
    
    .. code-block:: xml

        <!-- src/Acme/WebserviceUserBundle/Resources/config/services.xml -->
        <parameters>
            <parameter key="webservice_user_provider.class">Acme\WebserviceUserBundle\Security\User\WebserviceUserProvider</parameter>
        </parameters>
 
        <services>
            <service id="webservice_user_provider" class="%webservice_user_provider.class%"></service>
        </services>
        
    .. code-block:: php
    
        // src/Acme/WebserviceUserBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        
        $container->setParameter('webservice_user_provider.class', 'Acme\WebserviceUserBundle\Security\User\WebserviceUserProvider');
        
        $container->setDefinition('webservice_user_provider', new Definition('%webservice_user_provider.class%');

.. note::

    The real implementation of the user provider will probably have some
    dependencies or configuration options. Add these as arguments in the 
    service definition.

Modify `security.yml`
---------------------

In ``app/config/security.yml`` everything comes together. Add the user provider
to the list of providers in the "security" section. Choose a name for the user provider 
(e.g. "webservice") and mention the id of the service you just defined.

.. code-block:: yaml

    security:
        providers:
            webservice:
                id: webservice_user_provider

Symfony also needs to know how to encode passwords that are supplied by website
users, e.g. by filling in a login form. You can do this by adding a line to the 
"encoders" section in ``/app/config/security.yml``. 

.. code-block:: yaml

    security:
        encoders:
            Acme\WebserviceUserBundle\Security\User\WebserviceUser: sha512
