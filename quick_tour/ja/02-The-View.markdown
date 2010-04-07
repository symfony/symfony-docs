Symfony 2.0クイックツアー：ビューを学ぶ
=====================================

このページでは、Symfonyのテンプレートシステムについて、より詳しく説明を行います。前回の通り、SymfonyはデフォルトのテンプレートエンジンとしてPHPを使用していますが、充実した機能を備えています。

テンプレートのデコレーティング
--------------------

多くの場合、ヘッダーやフッターなどの共通部分は、部品として共有されます。一方、Symfonyでは異なる仕組みで、この共通化しています。その方法とは、あるテンプレートが別のテンプレートにより「デコレート（Decorate）」するというものです。

一見は百聞にしかず。早速`layout.php`ファイルを見てみましょう。

    [php]
    # src/Application/HelloBundle/Resources/views/layout.php
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html>
      <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      </head>
      <body>
        <?php $view->slots->output('_content') ?>
      </body>
    </html>

このファイルは、本アプリケーションの全体デザインを記述したものとなります。
先ほどのHelloControllerに記述されている`index`テンプレートでは、`extend()`というメソッドを呼び出しています。
この記述によって、`index`テンプレートは`layout.php`によりデコレートされます。
このように、デコレートとは、そのテンプレートが別のテンプレートによって取り込まれる（デコレーションされる）事を指します。

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    Hello <?php echo $name ?>!

さて、`HelloBundle::layout`という表記に見覚えがありませんか？これは、アクションにてテンプレートを参照する際に使用した表記法と同じです。コロンが連続しているのは、コントローラーが指定されていない事を示しています。すなわち、そのファイル（layout）は`views/`ディレクトリの直下にあります。

逆に、レイアウトファイルに記述された`$view->slots->output('content')`の部分は、テンプレートである`index.php`の内容に置き換えられます。

このように、Symfonyのテンプレートでは`$view`変数に格納された特別なオブジェクトが利用できます。
この`$view`オブジェクトを通じて、テンプレートエンジンが持つメソッドやプロパティを扱うことができます。

Symfonyでは、デコレーションの多重階層にも対応しています。つまり、レイアウトそのものが他のレイアウトによってデコレートされることが可能です。このテクニックは大きなプロジェクトで本当に便利です。スロットと組み合わせて使うと、さらに強力なものとなります。

スロット
-----

スロットとは、テンプレートにて定義された画面の一部を指します。スロットの定義が行われたテンプレートをレイアウトがデコレートするとそのレイアウトでもスロットを利用することが可能です。

まずは、indexテンプレート内で`title`というスロットを作成します。この`title`スロットには、「Hello World app」という文字列が格納されています。

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php $view->slots->set('title', 'Hello World app') ?>

    Hello <?php echo $name ?>!

次に、レイアウトファイルを修正し、headタグの中で`title`スロットを使ってみます。

    [php]
    # src/Application/HelloBundle/Resources/views/layout.php
    <html>
      <head>
        <title><?php $view->slots->output('title', 'Default Title') ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      </head>
      <body>
        <?php $view->slots->output('_content') ?>
      </body>
    </html>

この記述のように、`output()`メソッドによりスロットの内容が挿入されます。2番目の引数には、そのスロットが定義されていなかった場合のデフォルト値も指定できます。また、`_content`は特殊なスロットであり、レンダリングされる子テンプレートが入っています。

もちろん、スロットを定義する際には、長いHTML記述に対応するための構文も提供されています。

    [php]
    <?php $view->slots->start('title') ?>
      長いHTMLの記述をスロット定義に利用
    <?php $view->slots->stop() ?>

他テンプレートのインクルード
-----------------------

複数のテンプレート同士でコードを共有する便利な方法は、インクルード可能なテンプレートを定義することです。
まずは下記の例のように、`hello.php`テンプレートを作成します。

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/hello.php
    Hello <?php echo $name ?>!

次に`index.php`テンプレートを修正し、このファイルをインクルードしてみます。

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    <?php echo $view->render('HelloBundle:Hello:hello', array('name' => $name)) ?>

`render()`メソッドは他のテンプレートの内容を評価し、戻り値として返します。これはコントローラーで使われるrenderメソッドと同じものです。

別アクションの出力の挿入
-------------------

テンプレートに別のアクションの出力を挿入することも可能です。Ajaxを使う場合や、埋め込まれるテンプレートでロジックが必要な場合、アクションの埋め込みが便利です。

たとえば、`fancy`アクションを作り、`index`テンプレートにインクルードには、下記のような記述となります。

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->actions->output('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green')) ?>

ここで、文字列`HelloBundle:Hello:fancy`は`Hello`コントローラー内の`fancy`アクションを示しています。

    [php]
    # src/Application/HelloBundle/Controller/HelloController.php
    class HelloController extends Controller
    {
      public function fancyAction($name, $color)
      {
        // $color変数を基にオブジェクトを生成する
        $object = ...;

        return $this->render('HelloBundle:Hello:fancy', array('name' => $name, 'object' => $object));
      }

      // ...
    }

この手法は便利で強力ですが、内部でリクエストを生成するために若干動作が遅くなります。よって、可能な限り別の方法を検討する方が良いでしょう。

ここまで特に明記しませんでしたが、`$view->actions`や`$view->slots`プロパティは、テンプレートヘルパーと呼ばれます。次節でこれらについて更に学習します。

テンプレートヘルパー
----------------

テンプレートヘルパーを使うと、Symfonyのテンプレートシステムを簡単に拡張できます。ヘルパーはPHPオブジェクトとなっており、テンプレート内で便利な機能を提供します。先述の`actions`と`slots`は、まさにSymfonyフレームワークに組み込まれたテンプレートヘルパーの一部です。

### ページ間リンク

Webアプリケーションでは、別ページへのリンクを作成する機能が不可欠です。テンプレートに直接URLを記述することもできますが、routerヘルパーを使うとSymfonyのルーティング設定をもとにURLを生成してくれます。

    [php]
    <a href="<?php echo $view->router->generate('hello', array('name' => 'Thomas')) ?>">
      Greet Thomas!
    </a>

このように、`generate()`メソッドはルート名と変数配列を引数として取ります。ルート名とは、ルーティング定義ファイルで記述されていた、各ルートを参照する際の主キーのことです。念のため、ルーティング定義ファイルの例を掲載します。

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello: # ルート名
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index }

### 画像、JavaScript、スタイルシートの管理

Symfonyには、画像、JavaScript、スタイルシートを管理するためのヘルパーも搭載されています。それぞれ、`assets`、`javascripts`、そして`stylesheets`ヘルパーの3つです。

    [php]
    <link href="<?php echo $view->assets->getUrl('css/blog.css') ?>" rel="stylesheet" type="text/css" />

    <img src="<?php echo $view->assets->getUrl('images/logo.png') ?>" />

`assets`ヘルパーの主な目的は、アプリケーションの移植性を高めることにあります。このヘルパーを使うと、Webサーバー内のどの位置にルートディレクトリを移動しても、テンプレートを変更する必要は一切ありません。

`stylesheets`と`javaScripts`ヘルパーも、同様にスタイルシートとJavaScriptを管理できます。

    [php]
    <?php $view->javascripts->add('js/product.js') ?>
    <?php $view->stylesheets->add('css/product.css') ?>

`add()`メソッドは、テンプレートが依存していることを定義しています。実際の出力は、レイアウト内に下記のコードを追加して対応します。

    [php]
    <?php echo $view->javascripts ?>
    <?php echo $view->stylesheets ?>

最後に
--------------

Symfonyのテンプレートシステムは簡単かつ強力な仕組みが備わっています。レイアウト、スロット、デコレーティング、アクション・インクルードなどにより、とても簡単にテンプレートを拡張できます。

さあ、先に進みましょう。コントローラーについて、もう少し深く学ぶ必要があります。これは、次の章で扱うテーマとなります。

