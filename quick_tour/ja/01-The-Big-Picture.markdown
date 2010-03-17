Symfony 2.0 のクイックツアー: 見取り図
========================================

Symfony を試したいが10分ほどしか時間がない方へ。このチュートリアルの最初の部分はそんなあなたのために書かれました。シンプルな既成のプロジェクト構造を示すことで、Symfony でどのように速く始められるのかを説明します。

Web フレームワークを使ったことがあれば、Symfony 2.0 にしっくりくるでしょう。

ダウンロードとインストール
---------------------------

最初に、少なくとも PHP 5.3.0 がインストールされ Apache のような Web サーバーで動くように設定されているかチェックします。

用意はできましたか？Symfony をダウンロードして始めましょう。さらに速く始めるために、「Symfony サンドボックス」を使うことにします。これは Symfony のプロジェクトで、必須のすべてのライブラリとシンプルなコントローラがすでに収められており基本的なコンフィギュレーションの調整が行われています。サンドボックスがほかのインストール方法よりもすぐれている利点は Symfony ですぐに実験を始められることです。

[サンドボックス][1] をダウンロードし、Web 公開ディレクトリのルートで展開します。`sandbox/` ディレクトリが用意されています:

    www/ <- your web root directory
      sandbox/ <- the unpacked archive
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

コンフィギュレーションをチェックする
---------------------------------------

もう少し先の作業で頭痛に悩まされないようにするために、次の URL をリクエストしてあなたのコンフィギュレーションで Symfony プロジェクトがスムーズに動くか確認します:

    http://localhost/sandbox/web/check.php

スクリプトの出力を注意深く読み見つかる問題を修正します。

では、あなたの最初の「実際の」 Symfony の Web ページをリクエストします:

    http://localhost/sandbox/web/index_dev.php/

Symfony はあなたがこれまで頑張ったことをほめてくれます！

最初のアプリケーション
------------------------

サンドボックスにはシンプルな実際の世界の「アプリケーション」が収納されており、これは Symfony をより詳しく学ぶためのものです。Symfony であいさつするために次の URL に移動します (Fabien をあなたのファーストネームに置き換える):

    http://localhost/sandbox/web/index_dev.php/hello/Fabien

ここで何が起きているのでしょうか？URL を細かく調べましょう:

 * `index_dev.php`: これは「フロントコントローラ」です。これは hello アプリケーションへのユニークなエントリポイントですべてのユーザーリクエストに応答します;

 * `/hello/Fabien`: これはユーザーがアクセスしたいリソースへの「バーチャル」パスです。

あなたの開発者としての責務はユーザーリクエスト (`/hello/Fabien`) をそれに関連づけられているリソース (`Hello
Fabien!`) にマッピングするコードを書くことです。

### ルーティング

しかし Symfony はどのようにしてリクエストをあなたのコードに転送するのでしょうか？単にルーティング設定ファイルを読み込むだけです:

    [yml]
    # hello/config/routing.yml
    homepage:
      pattern:  /
      defaults: { _bundle: WebBundle, _controller: Default, _action: index }

    hello:
      resource: HelloBundle/Resources/config/routing.yml

[YAML](http://www.yaml.org/) で書かれているファイル, コンフィギュレーションの設定項目の記述をとても簡単にするフォーマットです。Symfony のすべての設定ファイルはcan be written in XML、YAML、もしくはプレーンな PHP コードで書かれています。このチュートリアルでは簡潔性と初心者が読みやすいように YAML フォーマットを使います。もちろん、「企業アプリケーションを開発している方々」はどこでも XML を使ってきました。

ルーティング設定ファイルの最初の3行はユーザーが 「`/`」 リソースをリクエストするときに呼び出すコードを定義します。 より興味深いのは最後の行で、次のような内容を読み込む別のルーティング設定ファイルをインポートしています:

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index }

さあ始めましょう！ご覧のとおり、「`/hello/:name`」リソースパターン (`:name` のようにコロンで始まる文字列はプレースホルダー) はコントローラにマッピングされ、`_bundle`、`_controller`、 `_action` 変数によって参照されます。

### コントローラ

コントローラはリソースの表現 (ほとんどの場合 HTML リソース) を返す責務を担います。これは PHP クラスとして定義されます:

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

コードはとても直感的ですがこのコードを一行ごとに説明しましょう:

 * `namespace Application\HelloBundle\Controller;`: Symfony は PHP 5.3 の新しい機能を利用するので、すべてのコントローラには適切な名前空間がつけられています (名前空間は `_bundle` のルーティング値: `HelloBundle` を含む)。

 * `class HelloController extends Controller`: コントローラの名前は `_controller` ルーティング値 (`Hello`) と `Controller` を連結したものです。これは便利なショートカットを提供する組み込みの `Controller` クラスを継承します (このチュートリアルの後のほうで見ます)。

 * `public function indexAction($name)`: それぞれのコントローラは複数のアクションで構成されます。コンフィギュレーションごとに、hello ページは `index` アクション (`_action` ルーティング値) によって処理されます。このメソッドは引数としてリソースのプレースホルダの値 (このケースでは `$name`) を受け取ります。

 * `return $this->render('HelloBundle:Hello:index', array('name' => $name));`:
   `render()` メソッドはテンプレートをロードし2番目の引数として渡される変数 (`HelloBundle:Hello:index`) でテンプレートをレンダリングします。

しかしバンドルとは何でしょうか？プロジェクトで書くすべてコードはバンドルに編成されます。Symfony の言い回しでは、バンドルはファイルの構造化された集まり (PHP
ファイル、スタイルシート、JavaScripts、画像、・・・) で単独の機能
(ブログ、フォーラム、・・・) を実装し、ほかの開発者と簡単に共有できます。私たちの例では、`HelloBundle` だけが用意されています。

### テンプレート

ですので、コントローラは `HelloBundle:Hello:index` テンプレートをレンダリングします。しかしテンプレートの名前のなかになるものは何でしょうか？`HelloBundle` はバンドルの名前で、`Hello` はコントローラで、`index` はテンプレートの名前です。テンプレート自身は HTML とシンプルな PHP の式で構成されます:

    [php]
    # src/Application/HelloBundle/Resources/views/Hello/index.php
    <?php $view->extend('HelloBundle::layout') ?>

    Hello <?php echo $name ?>!

おめでとうございます！最初の Symfony のコードピースを見ました。これはそれほどむずかしくなかったでしょう？Symfony は Web サイトをよりよく速く実装できるようにしてくれます。

環境
----

これで Symfony がどのように動くのか理解が進みました。このチュートリアルを読み進めましょう; Symfony
と PHP ロゴがある小さなバーに気づくでしょう。これは「Web デバッグツールバー」と呼ばれ開発者の最良の友です。もちろん、アプリケーションを運用サーバーにデプロイするときにはこのようなツールは表示してはなりません。これが `web/` ディレクトリのなかに運用環境に合わせて最適化された別のフロントコントローラ (`index.php`) が見つかる理由です:

    http://localhost/sandbox/web/index.php/hello/Fabien

`mod_rewrite` をインストールした場合、URL の `index.php` の部分を省略することができます:

    http://localhost/sandbox/web/hello/Fabien

いい忘れていましたが、運用環境では、インストールするアプリケーションを安全にして URL の見た目をよくするために Web ルートディレクトリに `web/` ディレクトリを指定すべきです:

    http://localhost/hello/Fabien

運用環境のアプリケーションをできるかぎり速く動かすために、Symfony は `hello/cache/` ディレクトリのもとでキャッシュを維持しますので、キャッシュ済みファイルを手動で削除する必要があります。これがプロジェクトで作業するとき、開発環境のフロントコントローラ (`index_dev.php`) をつねに使うべき理由です。

考察
-----

10分がすぎました。これで、独自のシンプルなルート、コントローラ、とテンプレートを作ることができるようになります。練習として、Hello アプリケーションよりもっと便利なものを作ってみてください！Symfony を詳しく学びたいのであれば、このチュートリアルのすぐ次のチュートリアルを読めば、テンプレートシステムについて詳しく学ぶことができます。

[1]: http://symfony-reloaded.org/code#sandbox
