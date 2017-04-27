<?php
/**
 * @author will <wizarot@gmail.com>
 * @link http://wizarot.me/
 *
 * Date: 17/4/26
 * Time: 下午4:19
 */

namespace wechat\User;

use AbstractClass\AbstractClass;
use Medoo\Medoo;

class User extends AbstractClass
{
    /**
     * 处理关注事件
     * @param $openId
     * @return string
     */
    public function updateUser( $openId )
    {
//        $userService = $this->container['wechat_app']->user;
//        $userInfo = $userService->get($openId);
        /** @var Medoo $db */
        $db = $this->container['data_base'];
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

        return TRUE;
    }
}