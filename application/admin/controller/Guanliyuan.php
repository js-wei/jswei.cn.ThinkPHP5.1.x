<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:51:02+08:00



/**
 * Created by PhpStorm.
 * User: 魏巍
 * Date: 2017/8/2
 * Time: 17:50
 */
namespace app\admin\controller;

class Guanliyuan extends Base{
    protected function  initialize(){
        parent::initialize();
    }

    /**
     * 首页
     * @param int $aid
     * @return \think\response\View
     */
    public function index(){
        $where=[];
        $search = $this->_search();
        $where = array_merge($where,$search);

        $list = db('admin')->where($where)->order('date desc')->paginate(15,false,[
            'query'=>[
                'username'=>input('s_keywords'),
                "date"=>input('s_date'),
                "status"=>input('s_status')
            ]
        ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('admin')->count('*');
        $this->assign('count',$count);
        $this->assign('list',$list);
        return view();
    }

    /**
     * 添加管理员
     * @param int $id
     * @return \think\response\View
     */
    public function tianjia($id=0){
        $model = [
            'name'=>'管理员操作'
        ];
        if($id){
            $vo = db('admin')->field('dates',true)->find($id);
            $this->assign('info',$vo);
        }
		$power = db('group')->field('id,title')->where('status','eq',0)->select();
		$this->assign('power',$power);
        $this->assign('model',$model);
        return view();
    }

    public function add_tianjia_handler($id=0){
        $param = request()->post();
		if(isset($param['password'])){
			$param['password'] = $this->_password($param['password']);
			unset($param['comfrim_password']);
		}
        if($id){
            $param['dates']=time();
            if(!db('admin')->update($param)){
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
        }else{
            $param['date']=time();
            if(!db('admin')->insert($param)){
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('index')];
        }
    }


	public function quanxian(){
		$where=[];
        $search = $this->_search1();
        $where = array_merge($where,$search);

        $list = db('group')->where($where)->order('date desc')->paginate(15,false,[
            'query'=>[
                'title'=>input('s_keywords'),
                "date"=>input('s_date'),
                "status"=>input('s_status')
            ]
        ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('group')->count('*');
        $this->assign('count',$count);
        $this->assign('list',$list);
		return view();
	}

	public function add_quanxian($id=0){
		$model = [
            'name'=>'添加权限组'
        ];
        if($id){
            $vo = db('group')->field('dates',true)->find($id);
			$vo['power'] = $vo['power']?explode(',', $vo['power']):'';
            $this->assign('info',$vo);
        }
		$controller = db('model')
			->field('id,fid,title,name')
			->where('status','eq',0)
			->where('id','neq',1)
			->order('sort asc')
			->select();
		$controller = \Service\Category::unlimitedForLevel($controller);
        $this->assign('model',$model);
		$this->assign('power',$controller);
		return view();
	}


	public function add_group_handler($id=0){
        $param = request()->post();
		$param['power'] = isset($param['power'])?implode(',', $param['power']):'';
        if($id){
            $param['dates']=time();
            if(!db('group')->update($param)){
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('quanxian')];
        }else{
            $param['date']=time();
            if(!db('group')->insert($param)){
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('quanxian')];
        }
    }

	public function reset($id=0){
		if(!$id){
			return ['status'=>0,'msg'=>'参数错误'];
		}
		if(!db('admin')->update([
			'id'=>$id,
			'password'=>$this->_password('123456'),
			'dates'=>time()
		])){
			return ['status'=>0,'msg'=>'重置密码失败'];
		}
		return ['status'=>0,'msg'=>'重置密码成功,重置后初始密码是:123456'];
	}

	protected function _search($param=[]){
        $where=[];
        if(empty($param)){
            $param = request()->param();
        }
        if(!empty($param['s_keywords'])){
            $where['username']=['like',"%".$param['s_keywords']."%"];
        }
        if(!empty($param['s_status'])){
            $where['status']=$param['s_status']>-1?$param['s_status']:'';
        }
        if(!empty($param['s_date'])){
            $date = explode('-',$param['s_date']);
            $date[1] = "$date[1] 24:00";
            $where['date']=['between',[strtotime($date[0]),strtotime($date[1])]];
        }

        $this->assign('search',[
            's_keywords'=>!empty($param['s_keywords'])?$param['s_keywords']:'',
            's_date'=>!empty($param['s_date'])?$param['s_date']:'',
            's_status'=>!empty($param['s_status'])?$param['s_status']:''
        ]);
        return $where;
    }

	protected function _search1($param=[]){
        $where=[];
        if(empty($param)){
            $param = request()->param();
        }
        if(!empty($param['s_keywords'])){
            $where['title']=['like',"%".$param['s_keywords']."%"];
        }
        if(!empty($param['s_status'])){
            $where['status']=$param['s_status']>-1?$param['s_status']:'';
        }
        if(!empty($param['s_date'])){
            $date = explode('-',$param['s_date']);
            $date[1] = "$date[1] 24:00";
            $where['date']=['between',[strtotime($date[0]),strtotime($date[1])]];
        }

        $this->assign('search',[
            's_keywords'=>!empty($param['s_keywords'])?$param['s_keywords']:'',
            's_date'=>!empty($param['s_date'])?$param['s_date']:'',
            's_status'=>!empty($param['s_status'])?$param['s_status']:''
        ]);
        return $where;
    }
    /**
     * [status 状态操作]
     * @param  [type] $id [修改id]
     * @param  [type] $type  [操作类型]
     * @return [type]     [description]
     */
    public function status($id,$type){
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id,'group',$type,'','quanxian');
        return $_result;
    }

	protected function _password($pwd){
		return substr(md5($pwd),10,15);
	}
}
