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

$app = new Silex\Application();

//use Symfony\Component\HttpFoundation\Request;
use EasyWeChat\Foundation\Application as WechatApp;

$config = Yaml::parse( file_get_contents( __DIR__ . '/../config/config.yml' ) );
$option = $config['wechat'];
//echo '<pre>';
//var_dump($option);
//die;
$app = new WechatApp($option);
// 从项目实例中得到服务端应用实例。
$server = $app->server;
$server->setMessageHandler(function ($message) {
    // $message->FromUserName // 用户的 openid
    // $message->MsgType // 消息类型：event, text....
    return "您好！欢迎关注我!";
});
$response = $server->serve();
$response->send(); // Laravel 里请使用：return $response;

