Symfony 2.0クイックツアー：バンドルを学ぶ
========================================

このページでは、Symfonyの最も優れた機能の一つであるバンドル（Bundle）について説明します。

バンドル
-----------------

バンドルは、他のソフトウェアでいうプラグインのような仕組みです。通常、「プラグイン」は誰か別の人がまとめてくれたコード一式を意味します。これに対し、バンドルは、Symfonyフレームワークのコア部分からあなたが書き上げたコードまで、そのすべてがバンドルとして扱われます。そのため、バンドルはSymfonyの中で最も重要な機能でもあります。

アプリケーションは複数のバンドルから構成されています。その定義は`HelloKernel`クラスの`registerBundles()`メソッドで定義されています。

    [php]
    # hello/HelloKernel.php
    public function registerBundles()
    {
      return array(
        new Symfony\Foundation\Bundle\KernelBundle(),
        new Symfony\Framework\WebBundle\Bundle(),
        new Symfony\Framework\DoctrineBundle\Bundle(),
        new Symfony\Framework\SwiftmailerBundle\Bundle(),
        new Symfony\Framework\ZendBundle\Bundle(),
        new Application\HelloBundle\Bundle(),
      );
    }

この通り、`KernelBundle`、`WebBundle`、`DoctrineBundle`、`SwiftmailerBundle`、`ZendBundle`などのバンドルを、このアプリケーションが使うことを意味しています。これらのバンドルは、すべてコアフレームワークに含まれるものです。なお、HelloBundleについては、最初に説明した通り、本アプリケーションのHelloコントローラなどが含まれているバンドルです。

それぞれのバンドルは、YAMLやXML形式の設定ファイルを編集することでカスタマイズできます。それではデフォルトの設定を見てみましょう。

    [yml]
    # hello/config/config.yml
    kernel.config: ~
    web.web: ~
    web.templating: ~

それぞれの行は、バンドルの設定を定義したものです。これらの初期設定は、次のように上書きすることができます。

    [yml]
    # hello/config/config_dev.yml
    imports:
      - { resource: config.yml }

    web.debug:
      exception: %kernel.debug%
      toolbar:   %kernel.debug%

    zend.logger:
      priority: info
      path:     %kernel.root_dir%/logs/%kernel.environment%.log

これで、バンドルの設定方法だけは説明しました。次に、バンドルが何を行うのか見ていきましょう。

ユーザー管理
--------

HTTPはステートレスなプロトコルですが、Symfonyはクライアントユーザー（ブラウザを使用している人、ボットやWebサービスなど）を抽象化し、ユーザーオブジェクトとして扱うことができます。SymfonyはPHPのセッション機能を使用し、クッキーに情報を保存することで、リクエスト間の情報を引き継ぎます。

この機能は、多くのバンドルのうち`WebBundle`が提供します。WebBundleでユーザー機能を使用するには、`config.yml`に以下の行を追加します。

    [yml]
    # hello/config/config.yml
    web.user: ~

ユーザー情報に対する追加や取得は、以下のようにして簡単に行うことができます。

    [php]
    // ユーザー情報に新しい属性を追加する
    $this->getUser()->setAttribute('foo', 'bar');

    // ユーザー属性を取り出す
    $this->getUser()->getAttribute('foo');

    // ユーザーのカルチャーを取得する
    $this->getUser()->setCulture('fr');

また、フラッシュ機能を使うと、直後のリクエストにだけ引き継げるデータをセットできます。

    [php]
    $this->getUser()->setFlash('notice', 'おめでとうございます。あなたの設定が完了しました!')

データベースへのアクセス
----------------------

データベース接続が必要であれば、好みのデータベースを選択できます。データベースを抽象化したいのであれば、DoctrineやPropelなどのORMを使用することもできます。ただし今回は、簡単に説明を行うためにPDOのレイヤーであるDoctrine DBALを使用して解説します。

以下の行を`config.yml`に追記してください。これで`DoctrineBundle`が有効になり、データベース接続が行われます。

    # hello/config/config.yml
    doctrine.dbal:
      driver:   PDOMySql # OCI8、PDOMsSql、PDOMySql、PDOOracle、PDOPgSql、PDOSqliteから選択可能
      dbname:   your_db_name
      user:     root
      password: your_password # パスワードが指定されていない場合はnullと記述

これで設定は完了です。プログラムコードから、DB接続を使用できるようになります。

    [php]
    public function showAction($id)
    {
      $stmt = $this->getDatabaseConnection()->execute('SELECT * FROM product WHERE id = ?', array($id));

      if (!$product = $stmt->fetch())
      {
        throw new NotFoundHttpException('The product does not exist.');
      }

      return $this->render(...);
    }

Eメールの送信
--------------

SymfonyのEメール送信機能はとても簡単です。最初に`SwiftmailerBundle`を使用可能にし、次にどのように送信するのかを設定します。

    # hello/config/config.yml
    swift.mailer:
      transport: gmail # smtp、mail、sendmail、gmailから選択できます
      username:  your_gmail_username
      password:  your_gmail_password
      
次にアクション内でこのクラスを使用します。

    [php]
    public function indexAction($name)
    {
      // まずはSwiftMailerを初期化し、Mailerオブジェクトを取得する
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

これで、テンプレートに格納され、`renderView()`メソッドによってレンダリングされた本文がメールに取り込まれ、送信されます。

最後に
--------------

このパートではSymfonyのバンドル機能に関する説明を行いました。次のパートでは、Symfonyがどのように動作しているか、そしてSymfonyをどのように設定するのかを解説します。

