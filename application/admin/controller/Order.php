<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   jswei
# @Last modified time: 2017-11-17T20:51:23+08:00



namespace app\admin\controller;

class Order extends Base{
	protected function  initialize(){
		parent::initialize();
	}

	public function index(){
		$model = [
			'name'=>'订单管理'
		];
        $where=[];
        $search = $this->_search();
        $where = array_merge($where,$search);
		$list = db('orderlist')
            ->alias('a')
            ->join('think_member b','b.id=a.userid')
            ->field('a.id,a.userid,a.ordid,a.ordtime,a.ordtitle,a.finishtime,a.ordfee,a.ordstatus,a.payment_type,b.phone,b.nickname')
            ->order('id desc')->where($where)
            ->paginate(10,false,[
                'query'=>[
                    's_keywords'=>input('s_keywords'),
                    "s_date"=>input('s_date'),
                    "s_status"=>input('s_status')
                ]
            ]);
		//p($list);die;
		// 查询状态为1的用户数据 并且每页显示10条数据
		$count = db('orderlist')->count('*');
		$this->assign('count',$count);
		$this->assign('model',$model);
		$this->assign('list',$list);
		return view();
	}

	public function add($id=0){
		$model = [
			'name'=>'查看用户信息'
		];
		if($id){
        	$vo = db('orderlist')->field('dates',true)->find($id);
        	$this->assign('info',$vo);
		}
		$this->assign('model',$model);
		return view();
	}

	public function set_account($id=0,$p=''){
		$model = [
			'name'=>'查看用户信息'
		];
		if($id){
        	$vo = db('wechat_config')->field('dates',true)->where(['fid'=>$id])->find();
        	$this->assign('info',$vo);
        	$this->assign('fid',$id);
		}
		$this->assign('model',$model);
		return $this->fetch();
	}

	public function see_user($id=0,$t=0){
		if(!$id){
			echo "参数错误";
		}
		$vo = db('orderlist')->field('subscribe,fans,collection')->find($id);

		if($t==0){
			$orderlist = db('orderlist')->field('id,phone,head,nickname')->where(['id'=>['in',$vo['subscribe']]])->select();
			$this->assign('list',$orderlist);
		}else if($t==1){
			$orderlist = db('orderlist')->field('id,phone,head,nickname')->where(['id'=>['in',$vo['fans']]])->select();
			$this->assign('list',$orderlist);
		}else{
			$orderlist = db('article')->field('id,title,description')->where(['id'=>['in',$vo['collection']]])->order('id desc')->select();
			$this->assign('list',$orderlist);
		}
		$this->assign('t',$t);
		return $this->fetch();
	}

    /**
     * 修改/添加控制器
     * @param int $id
     * @return array
     */
	public function add_handler($id=0){
		$param = request()->param();
		if($id){
			$param['dates']=time();
			if(!db('orderlist')->update($param)){
				return ['status'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
		}else{
			$param['date']=time();
			if(!db('orderlist')->insert($param)){
				return ['status'=>0,'msg'=>'添加失败请重试'];
			}
			return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('index')];
		}
	}

	public function add_account($id=0){
		$param = request()->param();
		if($id){
			$param['dates']=time();
			if(!db('orderlist')->update($param)){
				return ['wechat_config'=>0,'msg'=>'修改失败请重试'];
			}
			return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
		}else{
			$param['date']=time();
			if(!db('wechat_config')->insert($param)){
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
		$_result = $this->_status($id,'orderlist',$type,'');
		return $_result;
	}

    public function export($time='',$user=''){
        $where=[];
        if($time){
            $time1 = explode('-',$time);
            $where['ordtime']=['between',[strtotime($time1[0]),strtotime($time1[1])]];
        }

        $title = $user?$time."|".$user:$time;
        $list = db('orderlist')
            ->alias('a')
            ->join('think_article b','b.id=a.proid')
            ->join('think_member c','c.id=a.userid')
            ->where($where)
            ->field('a.ordid,a.ordtitle,a.ordprice,a.ordfee,a.ordstatus,a.ordbuynum,a.ordtime,a.finishtime,b.title,c.phone,c.nickname')
            ->order('ordtime desc')
            ->select();

        if(request()->isPost()){
            if(!empty($list)){
                return array('status'=>1,'msg'=>'有导出数据','redirect'=>Url('export')."?time={$time}&user={$user}");
            }else{
                return array('status'=>0,'msg'=>'暂无数据可导出');
            }
        }

        foreach ($list as $k=>$v){
            $list[$k]['ordtime']=date('Y-m-d H:i:s',$v['ordtime']);
            $list[$k]['finishtime']=$v['finishtime']?date('Y-m-d H:i:s',$v['finishtime']):'/';
            $list[$k]['ordstatus']=$v['ordstatus']?'已支付':'未支付';
        }
        $xlsCell  = array(
            array('ordid','订单号'),
            array('title','收款/捐赠'),
            array('ordtitle','订单名称'),
            array('ordbuynum','购买数量'),
            array('ordtime','下单时间'),
            array('finishtime','支付时间'),
            array('ordprice','单价/元'),
            array('username','付款人'),
            array('ordfee','支付金额/元'),
            array('ordstatus','是否付款')
        );
        $this->exportExcel($title,$xlsCell,$list,"{$title}账单信息   生成日期:".date('Y-m-d',time()));
        return '';
    }
    /**
     * 搜索
     * @param int $t
     * @return array
     */
	public function _search($t=0){
        $where=[];
        $param = request()->param();

        if(!empty($param['s_username'])){
            $where1['phone']=['like',"%".$param['s_username']."%"];
            $member = db('member')->field('id')->where($where1)->select();
            $_id = [];
            foreach ($member as $k=>$v){
                $_id[]=$v['id'];
            }
            $where[$t?'a.mid':'mid']=['in',$_id];
        }

        if(!empty($param['s_keywords'])){
            $where[$t?'a.ordid':'ordid']=['like',"%".$param['s_keywords']."%"];
        }

        if(isset($param['s_status'])){

            if($param['s_status']==0){
                $where[$t?'a.ordstatus':'ordstatus']=0;
            }else if($param['s_status']==1){
                $where[$t?'a.ordstatus':'ordstatus']=1;
            }else if($param['s_status']==2){
                $where[$t?'a.is_send':'is_send']=0;
            }else if($param['s_status']==3){
                $where[$t?'a.is_send':'is_send']=1;
            }
        }

        if(!empty($param['s_date'])){
            $date = explode('-',$param['s_date']);
            $date[1] = "$date[1] 24:00";
            $where[$t?'a.ordtime':'ordtime']=['between',[strtotime($date[0]),strtotime($date[1])]];
        }

        $this->assign('search',[
            's_keywords'=>!empty($param['s_keywords'])?$param['s_keywords']:'',
            's_username'=>!empty($param['s_username'])?$param['s_username']:'',
            's_date'=>!empty($param['s_date'])?$param['s_date']:'',
            's_status'=>!empty($param['s_status'])?$param['s_status']:''
        ]);
        return $where;
    }
}
