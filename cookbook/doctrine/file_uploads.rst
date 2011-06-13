How to handle File Uploads with Doctrine
========================================

Handling file uploads with Doctrine entities is no much different than
handling any other upload. But if you want to integrate the file upload into
the entity lifecycle (creation, update, and removal), you need to take care of
a lot of details we will talk about in this cookbook entry.

First, let's create a simple Doctrine Entity to work with::

    use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     */
    class Document
    {
        /**
         * @ORM\Id @ORM\Column(type="integer")
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

        public function getFullPath()
        {
            return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
        }

        protected function getUploadRootDir()
        {
            return '/path/to/uploaded/documents';
        }
    }

A ``Document`` has a name and it is associated with a file. The ``path``
property stores the relative path to the file and ``getFullPath()`` uses the
``getUploadRootDir()`` to return the absolute path to the document.

.. tip::

    If you have not done so yet, you should probably read the
    :doc:`file</reference/forms/types/file>` type documentation first to
    understand how the basic upload process works.

To receive the uploaded file, we use a "virtual" ``file`` field::

    $form = $this->createFormBuilder($document)
        ->add('name')
        ->add('file')
        ->getForm()
    ;

Validation rules should be declared on this virtual ``file`` property::

    use Symfony\Component\Validator\Constraints as Assert;

    class Document
    {
        /**
         * @Assert\File(maxSize="6000000")
         */
        public $file;

        // ...
    }

.. note::

    As we are using the ``File`` constraint, Symfony2 will automatically guess
    that the field is a file upload input; that's why we have not set it
    explicitly during form creation.

The following controller shows you how to manage the form::

    public function uploadAction(Post $post)
    {
        $document = new Document();
        $form = $this->createFormBuilder($document)
            ->add('name')
            ->add('file')
            ->getForm()
        ;

        if ($this->getRequest()->getMethod() === 'POST') {
            $form->bindRequest($this->getRequest());
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $em->persist($document);
                $em->flush();

                $this->redirect('...');
            }
        }

        return array('post' => $post, 'form' => $form->createView());
    }

.. note::

    When writing the template, don't forget to set the ``enctype`` attribute:

    .. code-block:: html+php

        <h1>Upload File</h1>

        <form action="#" method="post" {{ form_enctype(form) }}>
            {{ form_widget(form) }}

            <input type="submit" value="Upload Document" />
        </form>

The previous code will automatically persist document entities with their
names, but it will do nothing about the file, because it is not managed by
Doctrine. However, moving the file can be done just before the document is
persisted to the database by calling the ``move()`` method of the
:class:`Symfony\\Component\\HttpFoundation\\File\\UploadedFile` instance
returned for the ``file`` field when the form is submitted::

    if ($form->isValid()) {
        $em = $this->getDoctrine()->getEntityManager();

        $document->upload();

        $em->persist($document);
        $em->flush();

        $this->redirect('...');
    }

And here is the implementation of the ``upload`` method::

    public function upload()
    {
        // the file property can be empty if the field is not required
        if (!$this->file) {
            return;
        }

        // we use the original file name here but you should
        // sanitize at least it to avoid any security issues
        $this->file->move($this->getUploadRootDir(), $this->file->getOriginalName());

        $this->setPath($this->file->getOriginalName());

        // clean up the file property as we won't need it anymore
        unset($this->file);
    }

Even if this implementation works, it suffers from a major flaw: What if there
is a problem when the entity is persisted? The file is already moved to its
final location but the entity still references the previous file.

To avoid these issues, we are going to change the implementation so that the
database operation and the moving of the file becomes atomic: if there is a
problem when persisting the entity or if the file cannot be moved, then
nothing happens.

To make the operation atomic, we need to do the moving of the file when
Doctrine persists the entity to the database. This can be accomplished by
hooking into the entity lifecycle::

    /**
     * @ORM\Entity
     * @ORM\HasLifecycleCallbacks
     */
    class Document
    {
    }

And here is the ``Document`` class that shows the final version with all
lifecycle callbacks implemented::

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * @ORM\Entity
     * @ORM\HasLifecycleCallbacks
     */
    class Document
    {
        /**
         * @ORM\PrePersist()
         */
        public function preUpload()
        {
            if ($this->file) {
                $this->setPath($this->generatePath($this->file));
            }
        }

        /**
         * @ORM\PostPersist()
         */
        public function upload()
        {
            if (!$this->file) {
                return;
            }

            // you must throw an exception here if the file cannot be moved
            // so that the entity is not persisted to the database
            // which the UploadedFile move() method does
            $this->file->move($this->getUploadRootDir(), $this->generatePath($this->file));

            unset($this->file);
        }

        /**
         * @ORM\PostRemove()
         */
        public function removeUpload()
        {
            if ($file = $this->getFullPath()) {
                unlink($file);
            }
        }

        protected function generatePath(UploadedFile $file)
        {
            // do whatever you want to generate a unique name
            return uniq().'.'.$this->file->guessExtension();
        }
    }

If you want to use the ``id`` as the name of the file, the implementation is
slightly different as we need to save the extension under the ``path``
property, instead of the path::

    use Symfony\Component\HttpFoundation\File\UploadedFile;

    /**
     * @ORM\Entity
     * @ORM\HasLifecycleCallbacks
     */
    class Document
    {
        /**
         * @ORM\PrePersist()
         */
        public function preUpload()
        {
            if ($this->file) {
                $this->setPath($this->file->guessExtension());
            }
        }

        /**
         * @ORM\PostPersist()
         */
        public function upload()
        {
            if (!$this->file) {
                return;
            }

            // you must throw an exception here if the file cannot be moved
            // so that the entity is not persisted to the database
            // which the UploadedFile move() method does
            $this->file->move($this->getUploadRootDir(), $this->id.'.'.$this->file->guessExtension());
            unset($this->file);
        }

        /**
         * @ORM\PostRemove()
         */
        public function removeUpload()
        {
            if ($file = $this->getFullPath()) {
                unlink($file);
            }
        }

        public function getFullPath()
        {
            return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->id.'.'.$this->path;
        }
    }
