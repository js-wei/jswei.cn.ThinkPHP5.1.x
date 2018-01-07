<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:52:23+08:00



namespace app\admin\controller;
use think\Controller;

class User extends Controller {

	public function login(){
		return view();
	}
	/**
	 * [login_handler 登录操作]
	 * @param  string $username         [用户名]
	 * @param  string $password         [密码]
	 * @param  string $confirm_password [确认密码]
	 * @param  string $verify           [验证码]
	 * @return [type]                   [登录信息]
	 */
	public function login_handler($username='',$password='',$verify=''){

		if(!captcha_check($verify)){
		 	return array('status'=>0,'msg'=>'请填写正确的验证码');
		}

		$pwd = substr(input('password','','MD5'),10,15);
		$username=strtolower(input('username'));
		$admin=db("admin")->where(array('username'=>$username))->find();

        //账号验证
		if(empty($_POST['password']) || empty($_POST['username'])){
			return array('status'=>0,'msg'=>'账号或者密码不能为空');
		}
		if(!$admin){
			return array('status'=>0,'msg'=>'账号错误，请重试');
		}
		if($admin['password']!=$pwd){
			return array('status'=>0,'msg'=>'密码错误，请重试');
		}
		if($admin['status']==1){
			return array('status'=>0,'msg'=>'账号已锁定，请联系管理员');
		}

        //更新登录信息
		$data=[
			'id'=>$admin['id'],
			'last_date'=>time(),
			'last_ip'=>get_client_ip()
		];

		if(!db('admin')->update($data)){
			return array('status'=>0,'msg'=>'登录失败请重试');
		}

		//保存登录状态
		Session('_id',$admin['id']);
		session('_gid',$admin['gid']);
		Session('_name',ucfirst($admin['username']));
        Session('_logined',$admin);
        //跳转目标页
		return array('status'=>1,'msg'=>'登录成功','redirect'=> Url('index/index'));
	}

	public function profile(){
		return view();
	}

	/**
	 * [logout 用户退出]
	 * @return [type] [description]
	 */
	public function logout(){
		session('_id',null);
		session('_name',null);
		session('_logined',null);
		return array('status'=>1,'msg'=>'退出成功','redirect'=> Url('user/login'));
	}
}
