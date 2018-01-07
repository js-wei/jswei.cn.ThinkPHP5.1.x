<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:51:10+08:00



namespace app\admin\controller;

class Member extends Base {
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){
        $where=[];
        $search = $this->_search();
        $where = array_merge($where,$search);

        $list = db('member')->where($where)->order('date desc')->paginate(15,false,[
            'query'=>[
                's_keywords'=>input('s_keywords'),
                "s_date"=>input('s_date'),
                "s_status"=>input('s_status')
            ]
        ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('member')->count('*');
        $this->assign('count',$count);
        $this->assign('list',$list);
        return view();
	}

    public function add($id=0){
        $model = [
            'name'=>'添加用户'
        ];
        if($id){
            $vo = db('member')->field('dates',true)->find($id);
            //p($vo);die;
            $this->assign('info',$vo);
        }
        $this->assign('model',$model);
        return view();
    }

    /**
     * 锁定/解锁
     * @param int $id
     * @param int $type
     * @param string $content
     * @return mixed|\think\response\Json
     */
    public function unlock($id=0,$type=0,$content=''){
        if(!$id){
            return json(['status'=>0,'msg'=>'参数错误']);
        }
        if($type && $content==''){
            return json(['status'=>0,'msg'=>'敏感操作,填写理由']);
        }
        if(!db('member')->update([
            'id'=>$id,
            'status'=>$type,
            'dates'=>time()
        ])){
            return json(['status'=>0,'msg'=>'锁定失败']);
        }

        db('message')->insert(['title'=>'系统消息','content'=>$content,'date'=>time(),'mid'=>$id]);

        return json(['status'=>1,'msg'=>$type?'锁定成功':'解锁成功']);
    }

    public function auth(){
        $where=[];
        $search = $this->_search([],'a');
        $where = array_merge($where,$search);
        $list = db('authentication')
            ->alias('a')
            ->join('think_member b','b.id=a.mid')
            ->field('a.id,a.real_name,a.idcard_type,a.card,a.image,a.status,a.date,b.phone')
            ->where($where)->order('date desc')->paginate(15,false,[
            'query'=>[
                's_keywords'=>input('s_keywords'),
                "s_date"=>input('s_date'),
                "s_status"=>input('s_status')
            ]
        ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('authentication')->count('*');
        $this->assign('count',$count);
        $this->assign('list',$list);
        return view();
    }

    public function see($id=0,$k=0){
        if(request()->isGet()){
            $model = [
                'name'=>'添加用户'
            ];
            if($k){
                $auth = db('authentication')
                    ->alias('a')
                    ->field('a.id,a.mid,a.real_name,a.idcard_type,a.card,a.image,a.status,a.date,b.phone,c.content')
                    ->join('think_member b','b.id=a.mid')
                    ->join('think_message c','c.aid=a.id')
                    ->where('a.id','eq',$id)
                    ->find();
            }else{
                $auth = db('authentication')
                    ->alias('a')
                    ->field('a.id,a.mid,a.real_name,a.idcard_type,a.card,a.image,a.status,a.date,b.phone')
                    ->join('think_member b','b.id=a.mid')
                    ->where('a.id','eq',$id)
                    ->find();
            }
            $this->assign('k',$k);
            $this->assign('model',$model);
            $this->assign('info',$auth);
            return view();
        }else{
            $param = request()->param();
            $_id = $param['id'];
            unset($param['aid']);
            unset($param['id']);
            $param['title']='认证通知';
            $param['date']=time();
            //认证:0未认证,1认证通过,2认证中,3认证失败
            $s = $param['status']?1:3;
            $member = [
                'id'=>$param['mid'],
                'is_check'=>$s,
                'dates'=>time()
            ];
            $auth = [
                'id' =>$_id,
                'status'=>$param['status']?1:2
            ];
            $param['aid']=$_id;
            $param['status']=0;
            if(!db('message')->insert($param)){
                return json([
                    'status'=>0,
                    'msg'=>'操作失败'
                ]);
            }

            db('member')->update($member);
            db('authentication')->update($auth);
            return json([
                'status'=>1,
                'msg'=>'操作成功'
            ]);
        }
    }

    protected function _search($param=[],$a=''){
        $where=[];
        if(empty($param)){
            $param = request()->param();
        }
        if(!empty($param['s_keywords'])){
            $where[$a.'.card']=['like',"%".$param['s_keywords']."%"];
        }

		if(isset($param['s_status'])){
			 if(!empty($param['s_status']) || $param['s_status']==0){
				if($param['s_status']>-1){
					$where[$a.'.status']=$param['s_status'];
				}
			}
		}

        if(!empty($param['s_date'])){
            $date = explode('-',$param['s_date']);
            $date[1] = "$date[1] 24:00";
            $where[$a.'.date']=['between',[strtotime($date[0]),strtotime($date[1])]];
        }

        $this->assign('search',[
            's_keywords'=>!empty($param['s_keywords'])?$param['s_keywords']:'',
            's_date'=>!empty($param['s_date'])?$param['s_date']:'',
            's_status'=>isset($param['s_status'])?$param['s_status']!=0?$param['s_status']:0:''
        ]);
		//p($where);die;
        return $where;
    }

    /**
     * 重置密码
     * @param int $id
     * @return \think\response\Json
     */
    public function set_password($id=0){
        if(!$id){
            return json([
               'status'=>0,
               'msg'=>'参数错误'
            ]);
        }
        if(!db('member')->update([
            'id'=>$id,
            'password'=>substr(md5('123456'),10,15),
            'dates'=>time(),
            'status'=>0
        ])){
            return json([
                'status'=>0,
                'msg'=>'操作失败'
            ]);
        }
        return json([
            'status'=>1,
            'msg'=>'恭喜您初始化成功,初始密码是:123456',
            'reditect'=>Url('index')
        ]);
    }

    /**
     * [status 状态操作]
     * @param  [type] $id [修改id]
     * @param  [type] $type  [操作类型]
     * @return [type]     [description]
     */
    public function status($id,$type){
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id,'member',$type,'head');
        return $_result;
    }

    /**
     * 认证操作状态
     * @param $id
     * @param $type
     * @return array
     */
    public function status_auth($id,$type){
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id,'authentication',$type,'image','auth');
        return $_result;
    }
}
