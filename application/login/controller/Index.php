<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/18 0018
 * Time: 19:42
 */

namespace app\Login\controller;


use think\Controller;

class Index extends Controller
{
    function index(){
        return $this->fetch('Index/index');
    }

    function login(){
        $name = request()->post('user_name');
        $pwd = request()->post('user_pwd');

        if(empty($name)){

            $this->error('用户名不能为空');
        }

        if(empty($pwd)){

            $this->error('密码不能为空');
        }

        // 验证用户名
        $has = db('users')->where('user_name', $name)->find();
        if(empty($has)){

            $this->error('用户名密码错误');
        }

        // 验证密码
        if($has['user_pwd'] != md5($pwd)){

            $this->error('用户名密码错误');
        }

        // 记录用户登录信息
        cookie('user_id', $has['id'], 3600);  // 一个小时有效期
        cookie('user_name', $has['user_name'], 3600);

        $this->redirect(url('admin/Index/index'));
    }

    function logout(){
        cookie('user_id', null);
        cookie('user_name', null);

        $this->redirect(url('login/Index/index'));
    }
}