<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:50:59+08:00



namespace app\admin\controller;

class Control extends Base{
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){

		$model = db('model')->where(['title'=>$this->controller])->find();
		$controller = db('model')->order('sort asc')->select();
		$controller = \Service\Category::LimitForLevel($controller);
		$count = db('model')->count('*');
		$this->assign('count',$count);
		$this->assign('model',$model);
		$this->assign('list',$controller);
		return view();
	}

	public function add($id=0){

		$model = db('model')->where(['title'=>$this->controller])->find();
		if($id){
        	$vo = db('model')->field('dates',true)->find($id);
        	$this->assign('info',$vo);
		}
        $list = \Service\Category::LimitForLevel(db('model')->select());
		$this->assign('model',$model);
		$this->assign('model_list',$list);
		return view();
	}
	/**
	 * [add_handler 修改/添加控制器]
	 * @param integer $id [description]
	 */
	public function add_handler($id=0){
		$param = request()->post();
		$param['title'] = strtolower($param['title']);
		if($id){
			$param['dates']=time();
			if(!db('model')->update($param)){
				return ['status'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
		}else{
			$param['date']=time();
			if(!db('model')->insert($param)){
				return ['status'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('index')];
		}
	}

	/**
	 * [status 状态操作]
	 * @param  [type] $id [修改id]
	 * @param  [type] $type  [操作类型]
	 * @return [type]     [description]
	 */
	public function status($id,$type){
		$type = ($type=="delete-all")?"delete":$type;
		$_result = $this->_status($id,'model',$type,'');
		return $_result;
	}
}
