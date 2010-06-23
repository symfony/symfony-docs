Emails
======

Sending emails with Symfony is a snap. First, enable the `SwiftmailerBundle`
and configure how you want them to be sent:

    # hello/config/config.yml
    swift.mailer:
        transport: gmail # can be any of smtp, mail, sendmail, or gmail
        username:  your_gmail_username
        password:  your_gmail_password

Then, use the mailer from any action:

    [php]
    public function indexAction($name)
    {
        // get the mailer first (mandatory to initialize Swift Mailer)
        $mailer = $this->getMailer();

        $message = \Swift_Message::newInstance()
            ->setSubject('Hello Email')
            ->setFrom('send@example.com')
            ->setTo('recipient@example.com')
            ->setBody($this->renderView('HelloBundle:Hello:email', array('name' => $name)))
        ;
        $mailer->send($message);

        return $this->render(...);
    }

The email body is stored in a template, rendered with the `renderView()`
method.
