.. index::
   single: Doctrine; File uploads

How to handle File Uploads with Doctrine
========================================

Handling file uploads with Doctrine entities is no different than handling
any other file upload. In other words, you're free to move the file in your
controller after handling a form submission. For examples of how to do this,
see the :doc:`file type reference</reference/forms/types/file>` page.

If you choose to, you can also integrate the file upload into your entity
lifecycle (i.e. creation, update and removal). In this case, as your entity
is created, updated, and removed from Doctrine, the file uploading and removal
processing will take place automatically (without needing to do anything in
your controller);

To make this work, you'll need to take care of a number of details, which
will be covered in this cookbook entry.

Basic Setup
-----------

First, create a simple ``Doctrine`` Entity class to work with::

    // src/Acme/DemoBundle/Entity/Document.php
    namespace Acme\DemoBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;

    /**
     * @ORM\Entity
     */
    class Document
    {
        /**
         * @ORM\Id
         * @ORM\Column(type="integer")
         * @ORM\GeneratedValue(strategy="AUTO")
         */
        public $id;

        /**
         * @ORM\Column(type="string", length=255)
         * @Assert\NotBlank
         */
        public $name;

        /**
         * @ORM\Column(type="string", length=255, nullable=true)
         */
        public $path;

        public function getAbsolutePath()
        {
            return null === $this->path
                ? null
                : $this->getUploadRootDir().'/'.$this->path;
        }

        public function getWebPath()
        {
            return null === $this->path
                ? null
                : $this->getUploadDir().'/'.$this->path;
        }

        protected function getUploadRootDir()
        {
            // the absolute directory path where uploaded
            // documents should be saved
            return __DIR__.'/../../../../web/'.$this->getUploadDir();
        }

        protected function getUploadDir()
        {
            // get rid of the __DIR__ so it doesn't screw up
            // when displaying uploaded doc/image in the view.
            return 'uploads/documents';
        }
    }

The ``Document`` entity has a name and it is associated with a file. The ``path``
property stores the relative path to the file and is persisted to the database.
The ``getAbsolutePath()`` is a convenience method that returns the absolute
path to the file while the ``getWebPath()`` is a convenience method that
returns the web path, which can be used in a template to link to the uploaded
file.

.. tip::

    If you have not done so already, you should probably read the
    :doc:`file</reference/forms/types/file>` type documentation first to
    understand how the basic upload process works.

.. note::

    If you're using annotations to specify your validation rules (as shown
    in this example), be sure that you've enabled validation by annotation
    (see :ref:`validation configuration<book-validation-configuration>`).

To handle the actual file upload in the form, use a "virtual" ``file`` field.
For example, if you're building your form directly in a controller, it might
look like this::

    public function uploadAction()
    {
        // ...

        $form = $this->createFormBuilder($document)
            ->add('name')
            ->add('file')
            ->getForm();

        // ...
    }

Next, create this property on your ``Document`` class and add some validation
rules::

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    // ...
    class Document
    {
        /**
         * @Assert\File(maxSize="6000000")
         */
        private $file;

        /**
         * Sets file.
         *
         * @param UploadedFile $file
         */
        public function setFile(UploadedFile $file = null)
        {
            $this->file = $file;
        }

        /**
         * Get file.
         *
         * @return UploadedFile
         */
        public function getFile()
        {
            return $this->file;
        }
    }

.. configuration-block::

    .. code-block:: yaml

        # src/Acme/DemoBundle/Resources/config/validation.yml
        Acme\DemoBundle\Entity\Document:
            properties:
                file:
                    - File:
                        maxSize: 6000000

    .. code-block:: php-annotations

        // src/Acme/DemoBundle/Entity/Document.php
        namespace Acme\DemoBundle\Entity;

        // ...
        use Symfony\Component\Validator\Constraints as Assert;

        class Document
        {
            /**
             * @Assert\File(maxSize="6000000")
             */
            private $file;

            // ...
        }

    .. code-block:: xml

        <!-- src/Acme/DemoBundle/Resources/config/validation.yml -->
        <class name="Acme\DemoBundle\Entity\Document">
            <property name="file">
                <constraint name="File">
                    <option name="maxSize">6000000</option>
                </constraint>
            </property>
        </class>

    .. code-block:: php

        // src/Acme/DemoBundle/Entity/Document.php
        namespace Acme\DemoBundle\Entity;

        // ...
        use Symfony\Component\Validator\Mapping\ClassMetadata;
        use Symfony\Component\Validator\Constraints as Assert;

        class Document
        {
            // ...

            public static function loadValidatorMetadata(ClassMetadata $metadata)
            {
                $metadata->addPropertyConstraint('file', new Assert\File(array(
                    'maxSize' => 6000000,
                )));
            }
        }

.. note::

    As you are using the ``File`` constraint, Symfony2 will automatically guess
    that the form field is a file upload input. That's why you did not have
    to set it explicitly when creating the form above (``->add('file')``).

The following controller shows you how to handle the entire process::

    // ...
    use Acme\DemoBundle\Entity\Document;
    use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
    use Symfony\Component\HttpFoundation\Request;
    // ...

    /**
     * @Template()
     */
    public function uploadAction(Request $request)
    {
        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('name')
            ->add('file')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($document);
            $em->flush();

            return $this->redirect($this->generateUrl(...));
        }

        return array('form' => $form->createView());
    }

The previous controller will automatically persist the ``Document`` entity
with the submitted name, but it will do nothing about the file and the ``path``
property will be blank.

An easy way to handle the file upload is to move it just before the entity is
persisted and then set the ``path`` property accordingly. Start by calling
a new ``upload()`` method on the ``Document`` class, which you'll create
in a moment to handle the file upload::

    if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();

        $document->upload();

        $em->persist($document);
        $em->flush();

        return $this->redirect(...);
    }

The ``upload()`` method will take advantage of the :class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile`
object, which is what's returned after a ``file`` field is submitted::

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->path = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

Using Lifecycle Callbacks
-------------------------

Even if this implementation works, it suffers from a major flaw: What if there
is a problem when the entity is persisted? The file would have already moved
to its final location even though the entity's ``path`` property didn't
persist correctly.

To avoid these issues, you should change the implementation so that the database
operation and the moving of the file become atomic: if there is a problem
persisting the entity or if the file cannot be moved, then *nothing* should
happen.

To do this, you need to move the file right as Doctrine persists the entity
to the database. This can be accomplished by hooking into an entity lifecycle
callback::

    /**
     * @ORM\Entity
     * @ORM\HasLifecycleCallbacks
     */
    class Document
    {
    }

Next, refactor the ``Document`` class to take advantage of these callbacks::

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * @ORM\Entity
     * @ORM\HasLifecycleCallbacks
     */
    class Document
    {
        private $temp;

        /**
         * Sets file.
         *
         * @param UploadedFile $file
         */
        public function setFile(UploadedFile $file = null)
        {
            $this->file = $file;
            // check if we have an old image path
            if (isset($this->path)) {
                // store the old name to delete after the update
                $this->temp = $this->path;
                $this->path = null;
            } else {
                $this->path = 'initial';
            }
        }

        /**
         * @ORM\PrePersist()
         * @ORM\PreUpdate()
         */
        public function preUpload()
        {
            if (null !== $this->getFile()) {
                // do whatever you want to generate a unique name
                $filename = sha1(uniqid(mt_rand(), true));
                $this->path = $filename.'.'.$this->getFile()->guessExtension();
            }
        }

        /**
         * @ORM\PostPersist()
         * @ORM\PostUpdate()
         */
        public function upload()
        {
            if (null === $this->getFile()) {
                return;
            }

            // if there is an error when moving the file, an exception will
            // be automatically thrown by move(). This will properly prevent
            // the entity from being persisted to the database on error
            $this->getFile()->move($this->getUploadRootDir(), $this->path);

            // check if we have an old image
            if (isset($this->temp)) {
                // delete the old image
                unlink($this->getUploadRootDir().'/'.$this->temp);
                // clear the temp image path
                $this->temp = null;
            }
            $this->file = null;
        }

        /**
         * @ORM\PostRemove()
         */
        public function removeUpload()
        {
            if ($file = $this->getAbsolutePath()) {
                unlink($file);
            }
        }
    }

The class now does everything you need: it generates a unique filename before
persisting, moves the file after persisting, and removes the file if the
entity is ever deleted.

Now that the moving of the file is handled atomically by the entity, the
call to ``$document->upload()`` should be removed from the controller::

    if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();

        $em->persist($document);
        $em->flush();

        return $this->redirect(...);
    }

.. note::

    The ``@ORM\PrePersist()`` and ``@ORM\PostPersist()`` event callbacks are
    triggered before and after the entity is persisted to the database. On the
    other hand, the ``@ORM\PreUpdate()`` and ``@ORM\PostUpdate()`` event
    callbacks are called when the entity is updated.

.. caution::

    The ``PreUpdate`` and ``PostUpdate`` callbacks are only triggered if there
    is a change in one of the entity's field that are persisted. This means
    that, by default, if you modify only the ``$file`` property, these events
    will not be triggered, as the property itself is not directly persisted
    via Doctrine. One solution would be to use an ``updated`` field that's
    persisted to Doctrine, and to modify it manually when changing the file.

Using the ``id`` as the filename
--------------------------------

If you want to use the ``id`` as the name of the file, the implementation is
slightly different as you need to save the extension under the ``path``
property, instead of the actual filename::

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * @ORM\Entity
     * @ORM\HasLifecycleCallbacks
     */
    class Document
    {
        private $temp;

        /**
         * Sets file.
         *
         * @param UploadedFile $file
         */
        public function setFile(UploadedFile $file = null)
        {
            $this->file = $file;
            // check if we have an old image path
            if (is_file($this->getAbsolutePath())) {
                // store the old name to delete after the update
                $this->temp = $this->getAbsolutePath();
            } else {
                $this->path = 'initial';
            }
        }

        /**
         * @ORM\PrePersist()
         * @ORM\PreUpdate()
         */
        public function preUpload()
        {
            if (null !== $this->getFile()) {
                $this->path = $this->getFile()->guessExtension();
            }
        }

        /**
         * @ORM\PostPersist()
         * @ORM\PostUpdate()
         */
        public function upload()
        {
            if (null === $this->getFile()) {
                return;
            }

            // check if we have an old image
            if (isset($this->temp)) {
                // delete the old image
                unlink($this->temp);
                // clear the temp image path
                $this->temp = null;
            }

            // you must throw an exception here if the file cannot be moved
            // so that the entity is not persisted to the database
            // which the UploadedFile move() method does
            $this->getFile()->move(
                $this->getUploadRootDir(),
                $this->id.'.'.$this->getFile()->guessExtension()
            );

            $this->setFile(null);
        }

        /**
         * @ORM\PreRemove()
         */
        public function storeFilenameForRemove()
        {
            $this->temp = $this->getAbsolutePath();
        }

        /**
         * @ORM\PostRemove()
         */
        public function removeUpload()
        {
            if (isset($this->temp)) {
                unlink($this->temp);
            }
        }

        public function getAbsolutePath()
        {
            return null === $this->path
                ? null
                : $this->getUploadRootDir().'/'.$this->id.'.'.$this->path;
        }
    }

You'll notice in this case that you need to do a little bit more work in
order to remove the file. Before it's removed, you must store the file path
(since it depends on the id). Then, once the object has been fully removed
from the database, you can safely delete the file (in ``PostRemove``).
