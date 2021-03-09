<?php
/**
 * 用户控制器
 */
namespace app\home\controller;

use \think\captcha\Captcha;

class User extends Mypublic
{
    // 注册
    public function regist()
    {
        if(request()->isPost()){
            $post = input('post.');
            // 验证验证码,验证码不对直接就抛出错误，因此返回值肯定是成功的
            $res = $this->validate($post,[
                'checkcode|验证码'=>'require|captcha'
            ]);
            if($res === true){
                $model = model('User');
                $result = $model -> regist($post['username'], $post['password']);
                if($result){
                    return json(['state' => 1, 'msg' => 'ok']);
                }else{
                    return json(['state' => 0, 'msg' => $model->getError()]);
                }
            }else{
                return json(['state' => 0, 'msg' => $res]);
            }
        }else{
            return $this -> fetch();
        }
    }

    // 验证码
    public function code()
    {
        $conf = [
            'fontSize'      => 22,
            'useCurve'      => false,
            'useNoise'      => false,
            'length'        => 4,
        ];
        $captcha = new Captcha($conf);
        return $captcha->entry();
    }

    // 登录
    public function login()
    {
        if(request()->isPost()){
            $post = input('post.');
            // 验证验证码,验证码不对直接就抛出错误，因此返回值肯定是成功的
            $res = $this->validate($post,[
                'checkcode|验证码'=>'require|captcha'
            ]);
            if($res === true){
                $model = model('User');
                $result = $model -> login($post['username'], $post['password']);
                if($result){
                    return json(['state' => 1, 'msg' => 'ok']);
                }else{
                    return json(['state' => 0, 'msg' => $model->getError()]);
                }
            }else{
                return json(['state' => 0, 'msg' => $res]);
            }
        }else{
            return $this -> fetch();
        }
    }

    // 退出登录
    public function logout()
    {
        session('user_id', null);
        session('user_name', null);
        $this -> redirect('index/index');
    }
}
