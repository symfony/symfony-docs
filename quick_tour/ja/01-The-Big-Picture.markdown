Symfony 2.0クイックツアー: 全体構成を学ぶ
============================================

クイックツアーでは、Symfony 2.0の全体構成について、素早く解説していきます。
5ページに分かれた本クイックツアーでは、作成済みのプロジェクトをもとにして、
Symfonyが素早い開発を可能にする仕組みについて説明していきます。

１度でもWebフレームワークを使ったことがある人なら、
このツアーが終わる頃には既にSymfony 2.0について理解していることでしょう。

ダウンロードとインストール
--------------------

最初に、PHP 5.3.0以上がインストールされており、
ApacheなどのWebサーバの設定が完了していることを確認してください。

準備はよろしいですか？では、早速Symfonyをダウンロードしましょう。
すばやく説明に移れるよう、ここでは「Symfony sandbox」を利用してみます。

サンドボックス（砂場）と呼ばれる本パッケージには、
Symfonyの動作に必要なライブラリ一式と、
設定済みのシンプルなコントローラが含まれています。

サンドボックスを活用することで、面倒な手順なしにSymfonyの検証を開始できます。

[sandbox][1]のダウンロードが終わったら、Webサーバーのドキュメントルートに展開してください。
下記の通り、`sandbox/`ディレクトリが展開されます。

    www/ ← Webサーバーのドキュメントルート
      sandbox/ ← 展開したディレクトリ
        hello/
          cache/
          config/
          logs/
        src/
          Application/
            HelloBundle/
              Controller/
              Resources/
          vendor/
            symfony/
        web/

環境設定の確認
-----------------------

まずは、Symfonyを動かすための必要な環境設定が整っていることを、以下のURLで確認しましょう。

    http://localhost/sandbox/web/check.php

この出力結果を確認しながら、問題がある場合には適宜修正を行ってください。
修正が終わったら、早速SymfonyによるWebページにアクセスしてみましょう。

    http://localhost/sandbox/web/index_dev.php/

おめでとうございます！Symfonyによる最初のページが表示されました。

はじめてのSymfony 2.0アプリケーション
----------------------

サンドボックスにはHallo World「アプリケーション」が含まれていますので、
こちらをもとにSymfonyを学習していきます。

試しに、以下のURLにアクセスしてみましょう。
([ Fabien ]というところを、あなたの名前に置き換えてください):

    http://localhost/sandbox/web/index_dev.php/hello/Fabien

ここでは、何が行われているのでしょうか？URLを確認してみましょう。

 * `index_dev.php`: これは「フロントコントローラ」です。
   本アプリケーションへの唯一のエントリポイントで、全てのリクエストに応答します。

 * `/hello/Fabien`: これはユーザがアクセスするリソースへの「仮想的な」パスです。

開発者としてのあなたの役目は、このユーザリクエスト(`/hello/Fabien`)と結びついた
実際のリソースの動き(`Hello Fabien!`)をコードとして記述することです。

### ルーティング（Routing）

SymfonyはリクエストURLをもとに、どのようにコードを実行するのでしょうか？
そこで、ルーティング設定ファイルを読んでみましょう。

    [yml]
    # hello/config/routing.yml
    homepage:
      pattern:  /
      defaults: { _bundle: WebBundle, _controller: Default, _action: index }

    hello:
      resource: HelloBundle/Resources/config/routing.yml

このファイルは[YAML](http://www.yaml.org/)という書式に基づいて記述されています。
Symfonyのすべての設定ファイルは、XML、YAML、もしくはPHPコードで記述することが可能です。

このチュートリアルでは、Symfonyの設定ファイルにYAML形式を用いることにします。
初心者にとっても簡潔で読みやすいことでしょう。
もちろん、エンタープライズ系の方にとっては、使い慣れたXMLを使うことも可能です。

さて、ルーティング設定の`homepage:`以下の３行は、
ユーザリクエストが`/`リソースに対して行われたときに、
どのコードが呼び出されるかを定義したものです。

`hello:`以下の2行については、別のルーティング設定ファイルをインポートしています。
それでは、ここで呼び出されたルーティングファイルを見てみましょう:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index }

`/hello/:name`リソースを`Hello`コントローラへと結びつける記述が見つかります。
(`:name`のようにコロンで始まる文字列は、プレースホルダです)
このように、Symfony 2.0では`_bundle`、`_controller`、`_action`のもと、コードが実行されます。

### コントローラ（Controller）

コントローラはPHPのクラスとして定義され、リクエストされたリソースの表示結果を返します。
表示結果は通常、HTML形式となります。

    [php]
    # src/Application/HelloBundle/Controller/HelloController.php
    namespace Application\HelloBundle\Controller;

    use Symfony\Framework\WebBundle\Controller;

    class HelloController extends Controller
    {
      public function indexAction($name)
      {
        return $this->render('HelloBundle:Hello:index', array('name' => $name));
      }
    }

短いコードですので、１行ずつ説明していきましょう。

 * `namespace Application\HelloBundle\Controller;`: 
   Symfonyでは、PHP 5.3の新機能である名前空間を使用します。
   そのため、コントローラには名前空間が厳密に定義されています。
   （この名前空間が示すとおり、`HelloBundle`名前空間に内包されています。）

 * `class HelloController extends Controller`: 
   コントローラ名は、`_controller`のルーティングの値(`Hello`)に
   `Controller`という単語をつなげたものを指定します。
   コントローラは`Controller`クラスが継承され、多くの機能が引き継がれています。
   （機能の詳細については、チュートリアルの中で説明していきます。）

 * `public function indexAction($name)`: 
   各コントローラは、いくつかのアクションから構成されます。
   今回の場合、HelloControllerには`index`アクション（`indexAction()`メソッド）が定義されています。
   このメソッドは、引数としてプレースホルダの値を受け取ります。
   先ほどのリクエストの場合、この場合`$name`が該当します。

 * `return $this->render('HelloBundle:Hello:index', array('name' => $name));`:
   `render()`メソッドは、テンプレート（`HelloBundle:Hello:index`）を呼び出し、描画します。
   ここで第２引数に与えた値が、テンプレートに変数として渡されます。

駆け足で眺めてきましたが、バンドル（Bundle）とは何でしょう？
実は、全てのコードはSymfonyのプロジェクトを構成するバンドルの中に記述されています。

Symfonyにおけるバンドルとは、特定の機能（たとえばブログやフォーラムなど）の実装に用いられる
ファイルの集合を指します。ファイルには、PHPコードだけでなく、スタイルシートやJavaScript、画像なども含まれます。そして、これらは他の開発者と容易に共有することができます。
サンドボックスでは、`HelloBundle`たった１つだけのバンドルとなっています。

### テンプレート（Templates）

コントローラからは、`HelloBundle:Hello:index`というテンプレートが描画されていました。
次に、このテンプレート記述の意味を理解しましょう。

コロンで区切られたこの記述は、それぞれバンドル名、コントローラ名、そしてテンプレートのファイル名を示します。
指定されているテンプレート自体は、下記の通りHTMLとPHPで記述されています。

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    Hello <?php echo $name ?>!

いかがでしたでしょうか？これで、Symfonyの基本動作の解説は終わりとなります。
バンドル、コントローラ、そしてアクションを組み合わせるだけで、
とても簡単で効率的な開発が可能になるのです。

環境（Environments）
------------

ここまで、Symfonyの動作について一通りの解説を行いました。
では、もう一度先ほどのページに戻り、画面の下部をご覧ください。
よく見ると、SymfonyとPHPのロゴが入った小さなバーがあることに気がつくでしょう。
これは、「Web Debug Toolbar」と呼ばれるもので、開発者の最高のパートナーとなるものです。

もちろん、デバッグ用ツールは本番稼動時のアプリケーションでは表示されてはいけません。
そこで`web/`ディレクトリを眺めてみると、別のフロントコントローラ（`index.php`）の存在に気づくでしょう。
こちらは、開発用フロントコントローラ（`index_dev.php`）と違い、
本番稼動用に環境設定が最適化されたコントローラです。
試しに、下記のURLにアクセスしてみてください。

    http://localhost/sandbox/web/index.php/hello/Fabien

Webサーバーに`mod_rewrite`がインストールされていれば、
`index.php`という名前をURLから取り除くことができます。:

    http://localhost/sandbox/web/hello/Fabien

大事なことを言い忘れていましたが、
本番環境では、セキュリティのためにWebのドキュメントルートとして`web/`を指定するようにしましょう。
そうすると、先ほどインストールしたものは、以下のようなURLでアクセスできます。

    http://localhost/hello/Fabien

なお、本番環境では、より高速な動作ができるように、Symfonyは`hello/cache/`ディレクトリ
にキャッシュを保持しています。

コードに変更があった場合には手動でこのキャッシュファイルを削除する必要があります。
そのため、開発を行う際には、常に開発用のフロント・コントローラ(`index_dev.php`)を使う方が良いでしょう。

最後に
--------------

ここまで読み進めるのに、10分もかからない事でしょう。
しかしながら、ここまででルーティング、コントローラ、テンプレートに関して簡単な理解が得られたはずです。
試しに、HelloWorldアプリケーションを変更したり、改造してみるのも良いでしょう。

Symfonyについて、もう少し興味が沸いてきた方は、是非このチュートリアルを読み進めることをお勧めします。
次のページでは、新しくなったテンプレートシステムについて、より詳細に解説していきます。

[1]: http://symfony-reloaded.org/code#sandbox
