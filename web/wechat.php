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

$container = new Container();

$config = Yaml::parse( file_get_contents( __DIR__ . '/../config/config.yml' ) );
$option = $config['wechat'];

$container['data_base'] = function ($c) use ($config) {
    return new \Medoo\Medoo( $config[ 'parameters' ] );
};


//$container['wechat_user'] = function ($c) use ($container) {
//    return new WechatUser($container);
//};

$container['wechat_app'] = function ($c) use ($option) {
    return new WechatApp($option);
};

/** @var WechatApp $app */
$app = $container['wechat_app'];
// 从项目实例中得到服务端应用实例。
$server = $app->server;
//$userService = $app->user;

$server->setMessageHandler(function ($message) use ($container){

    $openId = $message->FromUserName;
    /** @var \Medoo\Medoo $db */
    $db = $container['data_base'];
    $result = $db->get('wechat_account','*',['openId'=>$openId]);
    $insert = FALSE;
    if(empty($result)){
        $insert = TRUE;
        $result['openId'] = $openId;
        $result['subscribedAt'] = date('Y-m-d H:i:s');
    }
    $result['isSubscribed'] = TRUE;
    $result['lastResponseAt'] = date('Y-m-d H:i:s');
    $result['infoRefreshedAt'] = date('Y-m-d H:i:s');

    if($insert){
        $db->insert('wechat_account',$result);
    }else{
        $db->update('wechat_account',$result,['openId'=>$openId]);
    }
    $db->replace('wechat_account',$result,['openId'=>$openId]);

    // 处理微信事件 当 $message->MsgType 为 event 时为事件
    if ($message->MsgType == 'event') {
        switch ($message->Event) {
            case 'subscribe':
                // 订阅 (个人公众号没法获取用户基本信息.. 那就只能先凑合了.)

                return '你好,欢迎关注. help  获取帮助 ';
                break;
            case 'unsubscribe':
                // 取消订阅
                $db->update('wechat_account',['isSubscribed'=>FALSE],['openId'=>$openId]);
                break;
            default:
                // 其它事件
                break;
        }
    }elseif($message->MsgType == 'text'){
        // 处理文本消息
        switch ($message->Content) {
            case 'help':
                // 帮助信息

                return "请求帮助: help \n接受推送地址 http://xxx.com/{$openId}/msg=yourmessage ";
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

