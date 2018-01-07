<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:50:53+08:00



namespace app\admin\controller;

class Column extends Base{
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){
		$model = [
			'name'=>'栏目管理'
		];
		$controller = db('column')->select();
		$controller = \Service\Category::LimitForLevel($controller);
		$count = db('column')->count('*');
		$this->assign('count',$count);
		$this->assign('model',$model);
		$this->assign('list',$controller);
		return view();
	}

	public function add($id=0){
		$model = [
			'name'=>'栏目管理'
		];
		if($id){
        	$vo = db('column')->field('dates',true)->find($id);
        	$this->assign('info',$vo);
		}
        $list = \Service\Category::LimitForLevel(db('column')->select());
		$this->assign('model',$model);
		$this->assign('column_list',$list);
		return view();
	}
	/**
	 * [add_handler 修改/添加控制器]
	 * @param integer $id [description]
	 */
	public function add_handler($id=0){
		$param = request()->param();
        $param['name'] = strtolower($param['name']);
		if($id){
			$param['dates']=time();
			if(!db('column')->update($param)){
				return ['status'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
		}else{
			$param['date']=time();
			if(!db('column')->insert($param)){
				return ['status'=>0,'msg'=>'添加失败请重试'];
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
		$_result = $this->_status($id,'column',$type,'');
		return $_result;
	}
}
