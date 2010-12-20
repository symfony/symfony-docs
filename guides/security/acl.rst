.. index::
   single: Security; Access Control Lists (ACLs)
   
Access Control Lists (ACLs)
===========================

In complex applications, you will often face the problem that access decisions 
cannot only be based on the person (``Token``) who is requesting access, but 
also involve a domain object that access is being requested for. This is where
the ACL system comes in.

Imagine you are designing a blog system where your users can comment on your 
posts. Now, you want a user to be able to edit his own comments, but not those 
of other users; besides, you yourself want to be able to edit all comments. 
In this scenario, ``Comment`` would be our domain object that you want to 
restrict access to. You could take several approaches to accomplish this using 
Symfony2, two basic approaches are (non-exhaustive):

- *Enforce security in your business methods*: Basically, that means keeping 
  a reference inside each ``Comment`` to all users who have access, and then 
  compare these users to the provided ``Token``.
- *Enforce security with roles*: In this approach, you would add a role for 
  each ``Comment`` object, i.e. ``ROLE_COMMENT_1``, ``ROLE_COMMENT_2``, etc.

Both approaches are perfectly valid. However, they couple your authorization 
logic to your business code which makes it less reusable elsewhere, and also 
increases the difficulty of unit testing. Besides, you could run into 
performance issues if many users would have access to a single domain object.

Fortunately, there is a better way, which we will talk about now.


Key Concepts
------------
Symfony2's object instance security capabilities are based on the concept of
an Access Control List. Every domain object **instance** has its own ACL
instance. The ACL instance holds a detailed list of Access Control Entries
(ACEs) which are used to make access decisions. Symfony2's ACL system
focuses on two main objectives:

- providing a way to efficiently retrieve a large amount of ACLs/ACEs for 
  your domain objects, and to modify them
- providing a way to easily make decisions of whether a person is allowed 
  to perform an action on a domain object or not

As indicated by the first point, one of the main capabilities of Symfony2's
ACL system is a high-performance way of retrieving ACLs/ACEs. This is
of utmost importance since each ACL might have several ACEs, and inherit
from another ACL in a tree-like fashion. Therefore, we specifically do not
leverage any ORM, but the default implementation interacts with your 
connection directly.

The default implementation uses five database tables as listed below. The
tables are ordered from least rows to most rows in a typical application:

- *acl_security_identities*: This table records all security identities
  (SID) which hold ACEs. The default implementation ships with two 
  security identities: ``RoleSecurityIdentity``, and ``UserSecurityIdentity``
- *acl_classes*: This table maps class names to a unique id which can be
  referenced from other tables.
- *acl_object_identities*: Each row in this table represents a single
  domain object instance.
- *acl_object_identity_ancestors*: This table allows us to determine
  all the ancestors of an ACL in the blink of an eye, or faster :)
- *acl_entries*: This table contains all ACEs. This is typically the
  table with the most rows. It can contain tens of millions without
  significantly impacting performance.

Bootstrapping
-------------
Now, before we finally can get into action, we need to do some bootstrapping. 
First, we need to configure the connection the ACL system is supposed to use:

.. configuration_block ::
    
    .. code_block:: yaml
    
        # app/config/security.yml
        security.acl:
            connection: default

    .. code-block:: xml

        <!-- app/config/security.xml -->
        <acl>
            <connection>default</connection>
        </acl>

    .. code-block:: php

        // app/config/security.php
        $container->loadFromExtension('security', 'acl', array(
            'connection' => 'default',
        ));        


After the connection is configured. We have to import the database structure.
Fortunately, we have a task for this. Simply run the following command:

``php app/console init:acl``


Getting Started
---------------
Coming back to our small example from the beginning, let's implement ACL for it.

1. Creating an ACL, and adding an ACE

.. code-block:: php

    // BlogController.php
    public function addCommentAction(Post $post)
    {
        $comment = new Comment();
        
        // setup $form, and bind data
        // ...
        
        if ($form->isValid()) {
            $entityManager = $this->container->get('doctrine.orm.default_entity_manager');
            $entityManager->persist($comment);
            $entityManager->flush();
            
            // creating the ACL
            $aclProvider = $this->container->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($comment);
            $acl = $aclProvider->createAcl($objectIdentity);
            
            // retrieving the security identity of the currently logged-in user
            $securityContext = $this->container->get('security.context');
            $user = $securityContext->getToken()->getUser();
            $securityIdentity = new UserSecurityIdentity($user);
            
            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);
        }
    }

There are a couple of important implementation decisions in this code snippet. For now,
I only want to highlight two:

First, you may have noticed that ``->createAcl()`` does not accept domain objects
directly, but only implementations of the ``ObjectIdentityInterface``. This
additional step of indirection allows you to work with ACLs even when you have
no actual domain object instance at hand. This will be extremely helpful if you
want to check permissions for a large number of objects without actually hydrating
these objects.

The other interesting part is the ``->insertObjectAce()`` call. In our example,
we are granting the user who is currently logged in owner access to the comment.
The ``MaskBuilder::MASK_OWNER`` is a pre-defined integer bitmask; don't worry
the mask builder will abstract away most of the technical details, but using
this technique we can store many different permissions in one database row
which gives us a considerable boost in performance.


2. Checking Access

.. code-block:: php
    
    // BlogController.php
    public function editCommentAction($commentId)
    {
        $objectIdentity = new ObjectIdentity($commentId, 'Bundle\BlogBundle\Entity\Comment');
        $securityContext = $this->container->get('security.context');
        
        // check for edit access
        if (false === $securityContext->vote('EDIT', $objectIdentity))
        {
            throw new HttpForbiddenException();
        }
        
        // do your editing here
    }
    
