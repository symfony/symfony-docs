How to create a custom User Provider
====================================

Symfony2 firewalls depend for their authentication on user providers. 
These providers are requested by the authentication layer to provide a 
user object for a given username. Symfony will check whether the 
password of this user is correct and will then generate a security token, 
so the user may stay authenticated during the current session. Out of 
the box, Symfony has a "in_memory" user provider and an "entity" user 
provider. In this entry we'll see how you can create your own user 
provider. The user provider in this example tries to load a Yaml file 
containing information about users in the following format:

.. code-block:: yaml

    username:
        password: secret
        roles: [ROLE_USER]

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

    namespace Acme\YamlUserBundle\Security\User;
    
    use Symfony\Component\Security\Core\User\UserInterface;
     
    class YamlUser implements UserInterface
    {
        protected $username;
        protected $password;
     
        public function __construct($username, $password, array $roles)
        {
            $this->username = $username;
            $this->password = $password;
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
            if (!$user instanceof YamlUser) {
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

Next we will create a user provider, in this case a ``YamlUserProvider``. 
This provides the firewall with instances of ``YamlUser``. It has to implement 
the :class:`Symfony\\Component\\Security\\Core\\User\\UserProviderInterface`, 
which requires three methods: 

``loadUserByUsername($username)``
  Does the actual loading of the user: it looks for a user with the given username 
  in any way that seems appropriate to it and returns a user object (in our example 
  a ``YamlUser``). If the user was not found, this method must throw a 
  ``UsernameNotFoundException``.

``refreshUser(UserInterface $user)``
  Refreshes the information of the given user. It must check if the given user object
  is an instance of the user class that is supported by this specific user provider. 
  If not, an ``UnsupportedUserException`` should be thrown.

``supportsClass($class)``
  Should return ``true`` if this user provider can handle users of the given class, 
  ``false`` if not.

The implementation for ``YamlUserProvider`` would be something like this:
    
.. code-block:: php    

    namespace Acme\YamlUserBundle\Security\User;
     
    use Symfony\Component\Security\Core\User\UserProviderInterface;
    use Symfony\Component\Security\Core\User\UserInterface;
     
    use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
    use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
     
    use Symfony\Component\Yaml\Yaml;
     
    class YamlUserProvider implements UserProviderInterface
    {
        protected $users;
     
        public function __construct($yml_path)
        {
            $userDefinitions = Yaml::parse($yml_path);
     
            $this->users = array();
     
            // load all user data from the given file
            foreach ($userDefinitions as $username => $attributes) {
                $password = isset($attributes['password']) ? $attributes['password'] : null;
                $roles = isset($attributes['roles']) ? $attributes['roles'] : array();
     
                $this->users[$username] = new YamlUser($username, $password, $roles);
            }
        }
     
        public function loadUserByUsername($username)
        {
            if (!isset($this->users[$username])) {
                throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            }
     
            $user = $this->users[$username];
     
            return new YamlUser($user->getUsername(), $user->getPassword(), $user->getRoles());
        }
     
        public function refreshUser(UserInterface $user)
        {
            if (!$user instanceof YamlUser) {
                throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
            }
     
            return $this->loadUserByUsername($user->getUsername());
        }
     
        public function supportsClass($class)
        {
            return $class === 'Acme\YamlUserBundle\Security\User\YamlUser';
        }
    }

As you can see, the constructor depends on one argument, the path to the Yaml file that 
contains the information about the users. We will add this argument in the next step
when we create a service for the ``YamlUserProvider``.

Create a service for the user provider
--------------------------------------

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/MailerBundle/Resources/config/services.yml
        parameters:
            yaml_user_provider.class: Acme\YamlUserBundle\Security\User\YamlUserProvider
            
        services:
            yaml_user_provider:
                class: %yaml_user_provider.class%
                arguments:
                    - %kernel.root_dir%/Resources/users.yml
    
    .. code-block:: xml

        <!-- src/Acme/YamlUserBundle/Resources/config/services.xml -->
        <parameters>
            <parameter key="yaml_user_provider.class">Acme\YamlUserBundle\Security\User\YamlUserProvider</parameter>
        </parameters>
 
        <services>
            <service id="yaml_user_provider" class="%yaml_user_provider.class%">
                <argument>%kernel.root_dir%/Resources/users.yml</argument>
            </service>
        </services>
        
    .. code-block:: php
    
        // src/Acme/YamlUserBundle/Resources/config/services.php
        use Symfony\Component\DependencyInjection\Definition;
        
        $container->setParameter('yaml_user_provider.class', 'Acme\YamlUserBundle\Security\User\YamlUserProvider');
        
        $container->setDefinition('yaml_user_provider', new Definition('yaml_user_provider.class', array('%kernel.root_dir%/Resources/users.yml');

As you can see, the user provider will look in ``/app/Resources/users.yml`` for user data.
This file should look something like this:

.. code-block:: yaml

    matthias:
        password: 'kd98d7gl'
        roles: [ROLE_USER]
    lies:
        password: '97dnlo9d'
        roles: [ROLE_ADMIN]

Modify `security.yml`
---------------------

In ``app/config/security.yml`` everything comes together. Add the Yaml user provider
to the list of providers in the "security" section. Choose a name for the user provider 
(e.g. “yaml”) and mention the id of the service you just defined.

.. code-block:: yaml

    security:
        providers:
            yaml:
                id: yaml_user_provider

Symfony also needs to know how to encode passwords that are supplied by users, e.g. 
in a login form. In our case, the ``YamlUser``'s password is not encoded; it is 
stored in plain text. You should therefore add a line to the "encoders" section in 
``/app/config/security.yml`` which tells Symfony to use the "plaintext encoder".

.. code-block:: yaml

    security:
        encoders:
            Acme\YamlUserBundle\Security\User\YamlUser: plaintext

.. note::

    It it very insecure to store passwords in plain text. Please take a moment
    to set this up in a more secure way (see the :doc:`Security</book/security>` chapter)
