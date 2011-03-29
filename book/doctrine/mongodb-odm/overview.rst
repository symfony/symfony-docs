.. index::
   pair: Doctrine; MongoDB ODM

MongoDB ODM
===========

The `MongoDB`_ Object Document Mapper is much like the Doctrine2 ORM in the way
it works and architecture. You only deal with plain PHP objects and they are
persisted transparently without imposing on your domain model.

.. tip::

    You can read more about the Doctrine MongoDB Object Document Mapper on the
    projects `documentation`_.

To get started working with Doctrine and the MongoDB Object Document Mapper you
just need to enable it and specify the bundle that contains your mapped documents:

.. code-block:: yaml

    # app/config/config.yml

    doctrine_mongo_db:
        document_managers:
            default:
                mappings:
                    AcmeHello: ~

Now you can start writing documents and mapping them with annotations, xml or
yaml.

.. configuration-block::

    .. code-block:: php-annotations

        // Acme/HelloBundle/Document/User.php

        namespace Acme\HelloBundle\Document;

        /**
         * @mongodb:Document(collection="users")
         */
        class User
        {
            /**
             * @mongodb:Id
             */
            protected $id;

            /**
             * @mongodb:Field(type="string")
             */
            protected $name;

            /**
             * Get id
             *
             * @return integer $id
             */
            public function getId()
            {
                return $this->id;
            }

            /**
             * Set name
             *
             * @param string $name
             */
            public function setName($name)
            {
                $this->name = $name;
            }

            /**
             * Get name
             *
             * @return string $name
             */
            public function getName()
            {
                return $this->name;
            }
        }

    .. code-block:: yaml

        # Acme/HelloBundle/Resources/config/doctrine/metadata/mongodb/Acme.HelloBundle.Document.User.dcm.yml
        Acme\HelloBundle\Document\User:
            type: document
            collection: user
            fields:
                id:
                    id: true
                name:
                    type: string
                    length: 255

    .. code-block:: xml

        <!-- Acme/HelloBundle/Resources/config/doctrine/metadata/mongodb/Acme.HelloBundle.Document.User.dcm.xml -->
        <doctrine-mapping xmlns="http://doctrine-project.org/schemas/orm/doctrine-mapping"
              xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:schemaLocation="http://doctrine-project.org/schemas/orm/doctrine-mapping
                            http://doctrine-project.org/schemas/orm/doctrine-mapping.xsd">

            <document name="Acme\HelloBundle\Document\User" collection="user">
                <field name="id" id="true" />
                <field name="name" type="string" length="255" />
            </document>

        </doctrine-mapping>

.. note::

    When using annotations in your Symfony2 project you have to namespace all
    Doctrine MongoDB annotations with the ``mongodb:`` prefix.

.. tip::

    If you use YAML or XML to describe your documents, you can omit the creation
    of the Document class, and let the ``doctrine:generate:documents`` command
    do it for you.

Now, use your document and manage its persistent state with Doctrine:

.. code-block:: php

    use Acme\HelloBundle\Document\User;

    class UserController extends Controller
    {
        public function createAction()
        {
            $user = new User();
            $user->setName('Jonathan H. Wage');

            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $dm->persist($user);
            $dm->flush();

            // ...
        }

        public function editAction($id)
        {
            $dm = $this->get('doctrine.odm.mongodb.document_manager');
            $user = $dm->createQuery('find all from AcmeHello:User where id = ?', $id);
            $user->setBody('new body');
            $dm->flush();

            // ...
        }

        public function deleteAction($id)
        {
            $dm = $this->get('doctrine.odm.entity_manager');
            $user = $dm->createQuery('find all from AcmeHello:User where id = ?', $id);
            $dm->remove($user);
            $dm->flush();

            // ...
        }
    }

.. _MongoDB:       http://www.mongodb.org/
.. _documentation: http://www.doctrine-project.org/docs/mongodb_odm/1.0/en
