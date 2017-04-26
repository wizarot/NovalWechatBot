<?php
/**
 * @author will <wizarot@gmail.com>
 * @link http://wizarot.me/
 *
 * Date: 17/4/26
 * Time: 下午4:19
 */

namespace src\wechat\User;


use src\AbstractClass\AbstractClass;

class User extends AbstractClass
{
    /**
     * 处理关注事件
     * @param $openId
     * @return string
     */
    public function subscriber( $openId )
    {
        $userService = $this->container['wechat_app']->user;
        $userInfo = $userService->get($openId);


        return '你好,欢迎关注. \\help  获取帮助 '.var_export($userInfo,TRUE);
    }
}