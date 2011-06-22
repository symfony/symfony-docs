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
of other users; besides, you yourself want to be able to edit all comments. In
this scenario, ``Comment`` would be our domain object that you want to
restrict access to. You could take several approaches to accomplish this using
Symfony2, two basic approaches are (non-exhaustive):

- *Enforce security in your business methods*: Basically, that means keeping a
  reference inside each ``Comment`` to all users who have access, and then
  compare these users to the provided ``Token``.
- *Enforce security with roles*: In this approach, you would add a role for
  each ``Comment`` object, i.e. ``ROLE_COMMENT_1``, ``ROLE_COMMENT_2``, etc.

Both approaches are perfectly valid. However, they couple your authorization
logic to your business code which makes it less reusable elsewhere, and also
increases the difficulty of unit testing. Besides, you could run into
performance issues if many users would have access to a single domain object.

Fortunately, there is a better way, which we will talk about now.

Bootstrapping
-------------

Now, before we finally can get into action, we need to do some bootstrapping.
First, we need to configure the connection the ACL system is supposed to use:

.. configuration-block::

    .. code-block:: yaml

        # app/config/security.yml
        security:
            acl:
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


.. note::

    The ACL system requires at least one Doctrine DBAL connection to be
    configured. However, that does not mean that you have to use Doctrine for
    mapping your domain objects. You can use whatever mapper you like for your
    objects, be it Doctrine ORM, Mongo ODM, Propel, or raw SQL, the choice is 
    yours.

After the connection is configured, we have to import the database structure.
Fortunately, we have a task for this. Simply run the following command:

.. code-block:: text

    php app/console init:acl

Getting Started
---------------

Coming back to our small example from the beginning, let's implement ACL for
it.

Creating an ACL, and adding an ACE
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

.. code-block:: php

    // BlogController.php
    public function addCommentAction(Post $post)
    {
        $comment = new Comment();

        // setup $form, and bind data
        // ...

        if ($form->isValid()) {
            $entityManager = $this->get('doctrine.orm.default_entity_manager');
            $entityManager->persist($comment);
            $entityManager->flush();

            // creating the ACL
            $aclProvider = $this->get('security.acl.provider');
            $objectIdentity = ObjectIdentity::fromDomainObject($comment);
            $acl = $aclProvider->createAcl($objectIdentity);

            // retrieving the security identity of the currently logged-in user
            $securityContext = $this->get('security.context');
            $user = $securityContext->getToken()->getUser();
            $securityIdentity = UserSecurityIdentity::fromAccount($user);

            // grant owner access
            $acl->insertObjectAce($securityIdentity, MaskBuilder::MASK_OWNER);
            $aclProvider->updateAcl($acl);
        }
    }

There are a couple of important implementation decisions in this code snippet.
For now, I only want to highlight two:

First, you may have noticed that ``->createAcl()`` does not accept domain
objects directly, but only implementations of the ``ObjectIdentityInterface``.
This additional step of indirection allows you to work with ACLs even when you
have no actual domain object instance at hand. This will be extremely helpful
if you want to check permissions for a large number of objects without
actually hydrating these objects.

The other interesting part is the ``->insertObjectAce()`` call. In our
example, we are granting the user who is currently logged in owner access to
the Comment. The ``MaskBuilder::MASK_OWNER`` is a pre-defined integer bitmask;
don't worry the mask builder will abstract away most of the technical details,
but using this technique we can store many different permissions in one
database row which gives us a considerable boost in performance.

.. tip::

    The order in which ACEs are checked is significant. As a general rule, you
    should place more specific entries at the beginning.

Checking Access
~~~~~~~~~~~~~~~

.. code-block:: php

    // BlogController.php
    public function editCommentAction(Comment $comment)
    {
        $securityContext = $this->get('security.context');

        // check for edit access
        if (false === $securityContext->isGranted('EDIT', $comment))
        {
            throw new AccessDeniedException();
        }

        // retrieve actual comment object, and do your editing here
        // ...
    }

In this example, we check whether the user has the ``EDIT`` permission.
Internally, Symfony2 maps the permission to several integer bitmasks, and
checks whether the user has any of them.

.. note::

    You can define up to 32 base permissions (depending on your OS PHP might
    vary between 30 to 32). In addition, you can also define cumulative
    permissions.

Cumulative Permissions
----------------------

In our first example above, we only granted the user the ``OWNER`` base
permission. While this effectively also allows the user to perform any
operation such as view, edit, etc. on the domain object, there are cases where
we want to grant these permissions explicitly.

The ``MaskBuilder`` can be used for creating bit masks easily by combining
several base permissions:

.. code-block:: php

    $builder = new MaskBuilder();
    $builder
        ->add('view')
        ->add('edit')
        ->add('delete')
        ->add('undelete')
    ;
    $mask = $builder->get(); // int(15)

This integer bitmask can then be used to grant a user the base permissions you
added above:

.. code-block:: php

    $acl->insertObjectAce(new UserSecurityIdentity('johannes'), $mask);

The user is now allowed to view, edit, delete, and un-delete objects.
