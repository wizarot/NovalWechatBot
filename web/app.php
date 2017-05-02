<?php
/**
 * @author will <wizarot@gmail.com>
 * @link http://wizarot.me/
 *
 * Date: 17/3/10
 * Time: 下午7:18
 */

// php -S localhost:8888 -t web web/app.php
require __DIR__ . '/../vendor/autoload.php';
use Symfony\Component\Yaml\Yaml;

use EasyWeChat\Foundation\Application as WechatApp;
use Pimple\Container;
//use src\wechat\User\User AS WechatUser;

$app = new Silex\Application();
$container = new Container();

use Symfony\Component\HttpFoundation\Request;

$app[ 'config' ] = Yaml::parse( file_get_contents( __DIR__ . '/../config/config.yml' ) );
// msyql connect
$database = new \Medoo\Medoo( $app[ 'config' ][ 'parameters' ] );
$app[ 'db' ] = $database;

// 简单定一个模板
$temp = <<<EOT
<!doctype html>
<html lang="zh">
    <meta name ="viewport" content ="initial-scale=1, maximum-scale=3, minimum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="https://unpkg.com/mobi.css/dist/mobi.min.css" />
  <body>
    <div class="flex-center">
      <div class="container">
       <% body %>
      </div>
    </div>
  </body>
</html>
EOT;


// 各种路由api
$app->get( '/', function ( ) use ( $app ) {
    $datas = [
        '小说(暂时不分页)'=> [
            'url' => '<a href="/books">/books</a>',
            'parameters' => [
            ],
        ],
        '小说章节'=> [
            'url' => '/chapter/{bookId}',
            'parameters' => [
                'bookId' => '小说id',
            ],
        ],
        '小说内容'=> [
            'url' => '/chapter/{bookId}/detail/{pageId}',
            'parameters' => [
                'bookId' => '小说id',
                'pageId' => '小说详情Id',
            ],
        ],
    ];
    return $app->json( $datas, 200 );
} );


/**
 * 小说列表
 */
$app->match( '/books', function ( Request $request ) use ( $app ,$temp ) {
    try {
        $books = $app[ 'db' ]->select( "books","*" );
//              echo json_encode($books);die;
        $body = "<h1>小说列表</h1><ul>";
        foreach ( $books as $book ) {
            $body .= "<li><a href='/chapter/{$book['id']}'>{$book['name']}</a></li>";
        }
        $body .= "</ul>";
        return str_replace(['<% body %>'],[$body],$temp);
    } catch ( \Exception $e ) {
        return $app->json( [ 'status' => 'error', 'code' => $e->getCode(), 'info' => $e->getMessage() ], 200 );
    }
} )->method( 'GET|POST' );

/**
 * 小说列表
 */
$app->match( '/chapter/{bookId}', function ( Request $request , $bookId ) use ( $app ,$temp ) {
    try {
        $book = $app[ 'db' ]->get( "books","*",['id'=>$bookId] );
        $chapters = $app[ 'db' ]->select( "chapter","*",['bookId'=>$bookId] );
//              echo json_encode($books);die;
        $body = "<h1>{$book['name']}</h1><ul>";
        foreach ( $chapters as $chapter ) {
            $body .= "<li><a href='{$chapter['href']}'>{$chapter['title']}</a></li>";
        }
        $body .= "</ul>";
        return str_replace(['<% body %>'],[$body],$temp);
    } catch ( \Exception $e ) {
        return $app->json( [ 'status' => 'error', 'code' => $e->getCode(), 'info' => $e->getMessage() ], 200 );
    }
} )->method( 'GET|POST' );


/**
 * 微信推送
 */
$app->match( '/wechat/{openId}/', function ( Request $request , $openId ) use ( $app ,$container ) {
    $message = $request->get('msg');

    return $app->json( [ 'status' => 'success', 'openId' => $openId, 'message'=>$message ], 200 );

} )->method( 'GET|POST' );

// run
$app->run();