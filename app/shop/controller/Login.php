<?php
/**
 * 登录控制器
 */

namespace app\shop\controller;

use \think\Controller;
use \think\captcha\Captcha;

class Login extends Controller{
    // 登录
    public function login()
    {
        if(request()->isPost()){
            $post = input('post.');
            // 验证验证码,验证码不对直接就抛出错误，因此返回值肯定是成功的
            $res = $this->validate($post,[
                'captcha|验证码'=>'require|captcha'
            ]);
            if($res === true){
                $model = model("Admin");
                $res = $model -> login($post['username'], $post['password']);
                if($res){
                    $this -> success('登录成功！', url('Index/index'));
                }else{
                    $this -> error($model->getError());
                }
            }else{
                $this -> error($res);
            }
        }else{
            return $this -> fetch();
        }
    }

    // 验证码
    public function captcha()
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

    // 退出登录
    public function logout()
    {
        cookie(null);
        $this -> success('退出成功！', url('Login/login'));
    }
}
?>