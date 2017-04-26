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
use src\wechat\User\User AS WechatUser;
$container = new Container();

$config = Yaml::parse( file_get_contents( __DIR__ . '/../config/config.yml' ) );
$option = $config['wechat'];

$container['data_base'] = function ($c) use ($config) {
    return new \Medoo\Medoo( $config[ 'parameters' ] );
};


$container['wechat_user'] = function ($c) use ($container) {
    return new WechatUser($container);
};

$container['wechat_app'] = function ($c) use ($option) {
    return new WechatApp($option);
};

/** @var WechatApp $app */
$app = $container['wechat_app'];
// 从项目实例中得到服务端应用实例。
$server = $app->server;
//$userService = $app->user;

$server->setMessageHandler(function ($message) use ($container){
    // 处理微信事件 当 $message->MsgType 为 event 时为事件
    if ($message->MsgType == 'event') {
        switch ($message->Event) {
            case 'subscribe':
                $openId = $message->from;
                // 订阅
                $wechatUser = $container['wechat_user'];
                return $wechatUser->subscriber($openId);
                break;
            case 'unsubscribe':
                // 取消订阅
                break;
            default:
                // 其它事件
                break;
        }
    }



    return "您好！我是你的小助手!";
});
$response = $server->serve();
$response->send(); // Laravel 里请使用：return $response;

