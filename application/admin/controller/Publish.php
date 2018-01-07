<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:51:57+08:00



namespace app\admin\controller;
use think\Controller;

class Publish extends Base {

	public function index(){
		$admin = db('admin')->field('gid,username,status,last_date,last_ip')->find(session('_id'));
		$power = db('group')->find($admin['gid']);
		$this->assign('title','栏目管理');
		$this->assign('user',$admin);
		$this->assign('power',$power);
		return view();
	}

	public function exchange(){
		return view();
	}
	/**
	 * 修改密码
	 */
	public function exchange_handler($old_password='',$new_password='',$confirm_password=''){
		if(!request()->isPOST()){
			return json(['status'=>0,'msg'=>'非法的请求']);
		}
		$id = session('_id');
		if(empty($id)){
			return ['status'=>0,'msg'=>'请先登录在修改密码'];
		}
		if(empty($old_password)){
			return ['status'=>0,'msg'=>'请输入原始密码'];
		}
		if(empty($new_password)){
			return ['status'=>0,'msg'=>'请输入新的密码'];
		}
		if(empty($confirm_password)){
			return ['status'=>0,'msg'=>'请输入确认密码'];
		}
		$user = db('admin')->field('password,id')->find($id);
		$pwd= substr(md5($new_password),10,15);
		$old_password =  substr(md5($old_password),10,15);
		if($user['password']!=$old_password){
			return ['status'=>0,'msg'=>'原始密码输入不正确'];
		}
		if($confirm_password!=$new_password){
			return ['status'=>0,'msg'=>'新的密码与确认密码不一致'];
		}
		if(!db('admin')->update([
			'id'=>$id,
			'password'=>$pwd,
			'date'=>time()
		])){
			return ['status'=>0,'msg'=>'密码修改失败,请稍后再试'];
		}
		session('_id',null);
		session('_name',null);
		session('_logined',null);
		return ['status'=>1,'msg'=>'密码修改成功,请重新登录','redirect'=>Url('user/login')];
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

//		if(!captcha_check($verify)){
//		 	return array('status'=>0,'msg'=>'请填写正确的验证码');
//		}

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
		session('_id',$admin['id']);
		session('_gid',$admin['gid']);
		session('_name',ucfirst($admin['username']));
        session('_logined',$admin);
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
//		Session::delete('_id');
//		Session::delete('_name');
//		Session::delete('_logined');
		session('_id',null);
		session('_name',null);
		session('_logined',null);
		return array('status'=>1,'msg'=>'退出成功','redirect'=> Url('user/login'));
	}
}
