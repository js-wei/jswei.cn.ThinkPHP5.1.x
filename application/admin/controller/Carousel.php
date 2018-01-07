<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:50:49+08:00



namespace app\admin\controller;

class Carousel extends Base{
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){
		$model = [
			'name'=>'广告管理'
		];
        $where=[];
        $search = $this->_search();
        $where = array_merge($where,$search);

		$list = db('carousel')->where($where)->paginate(15,false,[
            'query'=>[
                's_keywords'=>input('s_keywords'),
                "s_date"=>input('s_date'),
                "s_status"=>input('s_status')
            ]
        ]);
        //p(db('carousel')->getLastSql());die;
		// 查询状态为1的用户数据 并且每页显示10条数据
		$count = db('carousel')->count('*');
		$this->assign('count',$count);
		$this->assign('model',$model);
		$this->assign('list',$list);
		return $this->fetch();
	}

	public function add($id=0){
		$model = [
			'name'=>'广告管理'
		];
		if($id){
        	$vo = db('carousel')->field('dates',true)->find($id);
        	$this->assign('info',$vo);
		}
		$this->assign('model',$model);
		return view();
	}

    /**
     *  修改/添加控制器
     * @param int $id
     * @return array
     */
	public function carouseld_handler($id=0){
		$param = request()->param();

		if($param['water_type']==1){
		    $water = $param['water'];
            $uploader = new Uploadify();
            $uploader->water($param['image'],$param['water_type'],$water);
        }
        if($param['water_type']==2){
            $water = $param['logo'];
            $uploader = new Uploadify();
            $uploader->water($param['image'],$param['water_type'],$water);
        }

        unset($param['water_type']);
        unset($param['water']);
        unset($param['logo']);

		if($id){
			$param['dates']=time();
			if(!db('carousel')->update($param)){
				return ['status'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
		}else{
			$param['date']=time();
			if(!db('carousel')->insert($param)){
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
		$_result = $this->_status($id,'carousel',$type,'');
		return $_result;
	}
}
