Symfony 2.0クイックツアー：コントローラーを学ぶ
==========================================

このページでは、コントローラーの使い方を見ていきましょう。

対応フォーマット
------------

昨今のWebアプリケーションには、HTML以外のフォーマットに対応できることが求められます。
RSSフィード用のXMLからAjaxリクエスト用のJSONまで様々なフォーマットがありますので、Symfonyでこれらのフォーマットを出力する方法を見ていきましょう。

下の例では、ルーティング設定である`routing.yml`を編集して、`_format`項目を追加しています。`_format`項目には、`xml`という値を指定しています。

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:  /hello/:name
      defaults: { _bundle: HelloBundle, _controller: Hello, _action: index, _format: xml }

次に、`index.xml.php` テンプレートファイルを追加します。 

    [xml]
    # src/Application/HelloBundle/Resources/views/Hello/index.xml.php
    <hello>
      <name><?php echo $name ?></name>
    </hello>


これで、XML形式での出力に対応しました。
このように、フォーマットを変更するために、コントローラーを修正する必要はありません。Symfonyでは自動的に最適な`Content-Type`ヘッダーが、レスポンス時に出力するようになっています。

また、同じアクション内で複数のフォーマットを出力したい場合は、ルーティング設定の`pattern`内で`:_format`プレースホルダーを使います。

    [yml]
    # src/Application/HelloBundle/Resources/config/routing.yml
    hello:
      pattern:      /hello/:name.:_format
      defaults:     { _bundle: HelloBundle, _controller: Hello, _action: index, _format: html }
      requirements: { _format: (html|xml|json) }

これで、`/hello/Fabien.xml`や`/hello/Fabien.json`といったURLで、コントローラーが呼ばれるようになります。`_format`のデフォルトは`html`であるため、`/hello/Fabien`と`/hello/Fabien.html`というURLはHTML形式を返します。

`requirements`のところでは正規表現を定義し、プレースホルダーと一致する条件を指定しています。上記の例では、`/hello/Fabien.js`というリクエストだと`_route`の必要条件に一致しないため、404「File Not Found」エラーが返されます。

レスポンスオブジェクト
----------------------

`Hello`コントローラーを再度見てみましょう。

    [php]
    public function indexAction($name)
    {
      return $this->render('HelloBundle:Hello:index', array('name' => $name));
    }

前回説明した通り、`render()`メソッドはテンプレートを描画します。このメソッドの戻り値は、`Response`オブジェクトとなります。

レスポンスはブラウザに送る前に変更できます。例えば`Content-Type`を変更するには、下記の記述を行います。

    [php]
    public function indexAction($name)
    {
      $response = $this->render('HelloBundle:Hello:index', array('name' => $name));
      $response->setHeader('Content-Type', 'text/plain');

      return $response;
    }

単純なレスポンスを返す場合には、`Response`オブジェクトを自分で作成することも可能です。

    [php]
    public function indexAction($name)
    {
      return $this->createResponse('Hello '.$name);
    }

これは、JSON形式のレスポンスを返す場合などに便利です。

エラー管理
----------

コンテンツが見つからない場合、Symfonyは404レスポンスを返します。これは、プログラムからも明示的に指定できます。

    [php]
    use Symfony\Components\RequestHandler\Exception\NotFoundHttpException;

    public function indexAction()
    {
      $product = // データベースからデータを取り出す
      if (!$product)
      {
        throw new NotFoundHttpException('The product does not exist.');
      }

      return $this->render(...);
    }

このように、`NotFoundHttpException`という例外をスローすることで、ブラウザに404エラーを返します。
同様に、`ForbiddenHttpException`は403エラーを、`UnauthorizedHttpException`は401エラーを返します。

他のエラーレスポンスを返す場合には、ベースとなる`HttpException`を使います。この場合、下記のようにHTTPエラーを戻すことができます。

    [php]
    throw new HttpException('Unauthorized access.', 401);

リダイレクトとフォワード
------------------------

他のページへリダイレクトさせたい場合は、コントローラが持つ`redirect()`メソッドを使います。

    [php]
    $this->redirect($this->generateUrl('hello', array('name' => 'Lucas')));


`generateUrl()`は、以前`router`ヘルパーで使用した`generate()`メソッドと同じようなメソッドです。
引数としてルート名とパラメーター配列を渡すと、URLを返します。

`forward()`メソッドを使うと簡単に別のアクションにフォワードできます。
前回説明した`$view->actions`ヘルパーは内部的なサブリクエストを生成しますが、`forward()`はレスポンスを加工できるように`Response`オブジェクトを返します。

    [php]
    $response = $this->forward('HelloBundle:Hello:fancy', array('name' => $name, 'color' => 'green'));
    // このあと、レスポンスを加工したり、そのまま返したりできます。


リクエストオブジェクト
----------------------

コントローラーからは、`Request`オブジェクトにアクセスできます。

    [php]
    $request = $this->getRequest();

    $request->isXmlHttpRequest(); // Ajaxリクエストかどうかを確認する?

    $request->getPreferredLanguage(array('en', 'fr'));

    $request->getQueryParameter('page'); // $_GETパラメータを取得する

    $request->getRequestParameter('page'); // $_POSTパラメータを取得する

テンプレート内では、`request`ヘルパーを通してリクエストオブジェクトにアクセスできます。

    [php]
    <?php echo $view->request->getParameter('page') ?>

まとめ
------

このページでは、コントローラの基本的な使い方について見ていきました。コントローラーを拡張すると、もっと簡単に処理を記述できるようになります。次は、コントローラーの拡張を取り上げます。
