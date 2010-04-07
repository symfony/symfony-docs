Symfony 2.0クイックツアー：アーキテクチャを学ぶ
=============================================

ここまでの一連の説明で、Symfony 2.0に関する概要のほとんどを説明してきました。しかし、まだプロジェクトのディレクトリ構造について、詳しく説明していません。本ページでは、この部分について解説を行います。

Symfonyアプリケーションのディレクトリ構造は柔軟性に富んでいます。このページの内容を理解することで、アプリケーションすべてのディレクトリをカスタマイズできるようになるでしょう。

ディレクトリ構造
-----------------------
サンドボックスを例にすると、Symfonyアプリケーションの一般的なディレクトリ構造は下記の通りです。

 * `hello/`: アプリケーション名がディレクトリとなり、設定ファイルなどが含まれます。

 * `src/`: すべてのPHPのコードは、このディレクトリ配下に設置されています。

 * `web/`: Webのルートディレクトリとなります。

### webディレクトリ

Webディレクトリ以下には、画像、スタイルシート、Javascriptコードなど、外部から参照されるファイルが配置されます。フロントコントローラもwebディレクトリに配置され、下記のような内容となります。

    [php]
    # web/index.php
    <?php

    require_once __DIR__.'/../hello/HelloKernel.php';

    $kernel = new HelloKernel('prod', false);
    $kernel->run();

このように、`index.php`ではカーネルクラス（ここでは`HelloKernel`）をアプリケーションの起動に利用しています。

### アプリケーションディレクトリ

`HelloKernel`クラスは、アプリケーションのエントリーポイントとなります。このクラスの実体は、`hello/`ディレクトリのHelloKernel.phpというファイルに記述されています。

このクラスでは、下記のメソッドが実装されている必要があります。

  * `registerRootDir()`: rootディレクトリの設定を返します。

  * `registerBundles()`: アプリケーション動作に必要なバンドルを格納した配列を返します。

  * `registerBundleDirs()`: ネームスペースとそれらのディレクトリを結合する配列を返します。

  * `registerContainerConfiguration()`: 設定に関するオブジェクトを返します。

  * `registerRoutes()`: ルーティング設定を返します。

これらのメソッドの実装に目を通しておくと、フレームワークについて詳しく知ることができるでしょう。
たとえば、ルーティング設定を編集するため、1回目では`hello/config/routing.yml`ファイルを用いました。例えばパスは、`registerRoutes()`メソッドで下記の通り設定されています。

    [php]
    public function registerRoutes()
    {
      $loader = new RoutingLoader($this->getBundleDirs());

      return $loader->load(__DIR__.'/config/routing.yml');
    }

この記述を変更するだけで、設定ファイルにXMLやPHPコードを使うことが可能となります。

さて、フレームワークの実行のため、カーネルでは`src/`ディレクトリにある2個のファイルを必要とします。

    [php]
    # hello/HelloKernel.php
    require_once __DIR__.'/../src/autoload.php';
    require_once __DIR__.'/../src/vendor/symfony/src/Symfony/Foundation/bootstrap.php';

### srcディレクトリ

この`src/autoload.php`ファイルでは、`src/`ディレクトリにあるファイルを自動的に読み込むためのものです。ファイルの内容は、下記のようになっています。

    [php]
    # src/autoload.php
    require_once __DIR__.'/vendor/symfony/src/Symfony/Foundation/UniversalClassLoader.php';

    use Symfony\Foundation\UniversalClassLoader;

    $loader = new UniversalClassLoader();
    $loader->registerNamespaces(array(
      'Symfony'     => __DIR__.'/vendor/symfony/src',
      'Application' => __DIR__,
      'Bundle'      => __DIR__,
      'Doctrine'    => __DIR__.'/vendor/doctrine/lib',
    ));
    $loader->registerPrefixes(array(
      'Swift_' => __DIR__.'/vendor/swiftmailer/lib/classes',
      'Zend_'  => __DIR__.'/vendor/zend/library',
    ));
    $loader->register();

    // for Zend Framework & SwiftMailer
    set_include_path(__DIR__.'/vendor/zend/library'.PATH_SEPARATOR.__DIR__.'/vendor/swiftmailer/lib'.PATH_SEPARATOR.get_include_path());

Symfonyの`UniversalClassLoader`クラスは、PHP 5.3のネームスペースに対応しているか、PEARのクラス名規則のどちらに対応したファイルを自動的に読み込んでくれます。このコードの通り、デフォルトでは依存関係はすべて`vendor/`ディレクトリ配下に格納されています。ただし、これは慣習であって、実際には好きなところに配置できます.

バンドルについて
------------------

これまでの解説のように、アプリケーションは`registerBundles()`メソッドで定義されたバンドルから構成されます。

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

Symfonyがバンドルを検索する方法は、とても柔軟性が高いものとなっています。`registerBundleDirs()`メソッドでは、下記の通り、ディレクトリとネームスペースを対応づけた連想配列を返します。

    [php]
    public function registerBundleDirs()
    {
      return array(
        'Application'        => __DIR__.'/../src/Application',
        'Bundle'             => __DIR__.'/../src/Bundle',
        'Symfony\\Framework' => __DIR__.'/../src/vendor/symfony/src/Symfony/Framework',
      );
    }

このため、たとえば`HelloBandle`を参照した場合には、Symfonyでは指定されたディレクトリ配下から検索します。

ベンダー
-------

サードパーティ製のライブラリを利用する場合、`src/vender/`ディレクトリに格納することが推奨されます。
このディレクトリには、Symfonyライブラリ、SwiftMailer、Doctrine、Zend Frameworkのクラス群などが含まれています。

キャッシュとログ出力
--------------

Symfonyはもっとも高速なフルスタック・フレームワークの一つです。その高速性を保つため、YAMLファイルやXMLファイルは部分的にキャッシュされ、毎回解釈が行われないようになっています。またアプリケーションの設定についても、最初の読み込み時にパースされ、それらはPHPコードにコンパイルされます。これらのキャッシュファイルは、アプリケーションの`cache/`ディレクトリに格納されます。開発環境では、ファイルを変更するたびにキャッシュが自動的に再作成されます。しかし、本番環境ではコードを変更した後は、明示的にキャッシュをクリアする必要があります。

また、Webアプリケーション開発で重宝するログは、`logs/`ディレクトリに格納されます。ここでは、各リクエストに関するすべての情報を提供するため、問題点の素早い解決を手助けするでしょう。

コマンドライン機能
--------------------------

アプリケーションには、メンテナンスに役立つコマンドライン・ツール(`console`)が付随しています.これは、冗長なタスクを自動化することで、あなたの生産性を向上させるものです.

操作方法を学ぶために、下記のコマンドを実行してみましょう。

    $ php hello/console

`--help`オプションをコマンドにつけることで、その使い方が表示されます。

    $ php hello/console router:debug --help

最後に
--------------

さぁ、短くて長かったクイックスタートもこれでおしまいです。ここまで読み進めて頂いたあなたは、きっとSymfonyに満足して頂いたことでしょう。

今、Symfonyマスターになるための第一歩を踏み出したのです。そう、きっとあなたはSymfonyを使って素晴らしいアプリケーションを構築していけるでしょう。

[1]: http://groups.google.com/group/php-standards/web/psr-0-final-proposal
[2]: http://pear.php.net/
