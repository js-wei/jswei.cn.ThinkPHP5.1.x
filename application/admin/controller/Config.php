<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:50:56+08:00



namespace app\admin\controller;

class Config extends Base{
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){
		$model = [
			'name'=>'网站设置'
		];
		$list = db('config')->find();

		// 查询状态为1的用户数据 并且每页显示10条数据
		$count = db('config')->count('*');
		$this->assign('count',$count);
		$this->assign('model',$model);
		$this->assign('info',$list);
		return $this->fetch();
	}

	/**
	 *
	 */
	public function add($id=0){
		$model = [
			'name'=>'网站设置'
		];
		if($id){
        	$vo = db('config')->field('dates',true)->find($id);
        	$this->assign('info',$vo);
		}
		$this->assign('model',$model);
		return view();
	}
	/**
	 * [add_handler 修改/添加控制器]
	 * @param integer $id [description]
	 */
	public function add_handler($id=0){
		$param = request()->param();
		if(isset($array['shard'])){
            $param['shard'] = htmlspecialchars($param['shard']);
        }
        if($param['code']){
            $param['code'] = htmlspecialchars($param['code']);
        }
		if($id){
			$param['dates']=time();
			if(!db('config')->update($param)){
				return ['status'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
		}else{
			$param['date']=time();
			if(!db('config')->insert($param)){
				return ['status'=>0,'msg'=>'添加失败请重试'];
			}
			return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('index')];
		}
	}

    /**
     * 拦截ip
     */
    public function intercept(){
        $model = [
            'name' => '放行IP设置'
        ];
        $list = db('intercept')->find();
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('intercept')->count('*');
        $this->assign('count', $count);
        $this->assign('model', $model);
        $this->assign('info', $list);
        return view();
    }

    /**
     * 拦截ip
     * @param int $id
     * @return array
     */
    public function addip_handler($id=0){
        $param = request()->param();
        if($id){
            $param['dates']=time();
            if(!db('intercept')->update($param)){
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('intercept')];
        }else{
            $param['date']=time();
            if(!db('intercept')->insert($param)){
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('intercept')];
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
		$_result = $this->_status($id,'config',$type,'logo');
		return $_result;
	}
}
