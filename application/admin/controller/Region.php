<?php
# @Author: 魏巍 <jswei>
# @Date:   2017-11-16T17:42:05+08:00
# @Email:  524314430@qq.com
# @Last modified by:   魏巍
# @Last modified time: 2017-12-01T16:51:16+08:00



namespace app\admin\controller;

class Region extends Base
{
    protected function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $model = [
            'name'=>$this->current
        ];
        $where['type']=0;
        $search = $this->_search();
        $where = array_merge($where, $search);
        $list = db('provinces')
            ->where($where)
            ->order('date desc,status asc')
            ->paginate(15, false, [
            'query'=>[
                's_keywords'=>input('s_keywords'),
                "s_date"=>input('s_date'),
                "s_status"=>input('s_status')
            ]
        ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('provinces')->count('*');
        $this->assign('count', $count);
        $this->assign('model', $model);
        $this->assign('list', $list);
        return $this->fetch();
    }

    public function custom()
    {
        $model = [
            'name'=>$this->current
        ];
        $where = [];
        $search = $this->_search();
        $where = array_merge($where, $search);
        $list = db('custom')
            ->where($where)
            ->order('date desc,status asc')
            ->paginate(15, false, [
                'query'=>[
                    's_keywords'=>input('s_keywords'),
                    "s_date"=>input('s_date'),
                    "s_status"=>input('s_status')
                ]
            ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('provinces')->count('*');
        $this->assign('count', $count);
        $this->assign('model', $model);
        $this->assign('list', $list);
        return view();
    }

    public function add($id=0)
    {
        $model = [
            'name'=>$this->current
        ];
        $c = [];
        if ($id) {
            $vo = db('provinces')->field('date', true)->find($id);
            $c = db('cities')->field('dates', true)->where('provinceid', 'eq', $vo['provinceid'])->select();
            $this->assign('info', $vo);
        }
        $this->assign('c', $c);
        $this->assign('model', $model);
        return view();
    }

    public function add_custom($id=0)
    {
        $model = [
            'name'=>'区域管理'
        ];
        $c = [];
        $vo = [];
        $city=[];
        if ($id) {
            $vo = db('custom')->field('date', true)->find($id);
            $c = db('cities')->field('dates', true)->where('provinceid', 'eq', $vo['provid'])->select();
            $this->assign('info', $vo);
        }
        $_list = db('provinces')->where('status=0')->select();
        if ($vo && isset($vo['city_id'])) {
            $prov = db('provinces')->find($vo['provid']);
            $city = db('cities')->where('provinceid', 'eq', $prov['provinceid'])->select();
        }

        $this->assign('c', $c);
        $this->assign('model', $model);
        $this->assign('province_list', $_list);
        $this->assign('city_list', $city);
        $_id = db('cities')->max('id');
        $this->assign('last_id', $_id);
        return view();
    }

    public function add_custom_handler($id=0)
    {
        $param = request()->param();
        if ($id) {
            $param['dates']=time();
            if (!db('custom')->update($param)) {
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('custom')];
        } else {
            $param['date']=time();
            if (!db('custom')->insert($param)) {
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('custom')];
        }
    }

    public function get_city($id=0)
    {
        if (!$id) {
            return ['status'=>0,'msg'=>'参数错误'];
        }
        $pro = db('provinces')->where('id', 'eq', $id)->find();
        $list = db('cities')->where('provinceid', 'eq', $pro['provinceid'])->select();
        return json([
            'status'=>1,
            'data'=>$list
        ]);
    }

    public function add_abroad($id=0)
    {
        $model = [
            'name'=>'区域管理'
        ];
        $c = [];
        if ($id) {
            $vo = db('provinces')->field('date', true)->find($id);
            $c = db('cities')->field('dates', true)->where('provinceid', 'eq', $vo['provinceid'])->select();
            $this->assign('info', $vo);
        }
        $this->assign('c', $c);
        $this->assign('model', $model);
        $_id = db('cities')->max('id');
        $this->assign('last_id', $_id);
        return view();
    }

    /**
     * [add_handler 修改/添加控制器]
     * @param integer $id [description]
     */
    public function add_handler($id=0)
    {
        $param = request()->param();
        foreach ($param['id_city'] as $k => $v) {
            $city=[
                'id'=>$k,
                'sort'=>$v,
                'status'=>$param['status_city'][$k],
                'dates'=>time()
            ];
            db('cities')->update($city);
        }
        unset($param['id_city']);
        unset($param['status_city']);
        if ($id) {
            $param['dates']=time();
            if (!db('provinces')->update($param)) {
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('index')];
        } else {
            $param['date']=time();
            if (!db('provinces')->insert($param)) {
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('index')];
        }
    }

    /**
     * 国外
     * @param int $id
     */
    public function add_abroad_handler($id=0)
    {
        $param = request()->param();
        if ($id) {
            $province = [
                'id'=>$id,
                'province'=>$param['province'],
                'keywords'=>$param['keywords'],
                'sort'=>$param['sort'],
                'status'=>$param['status'],
                'dates'=>time()
            ];

            foreach ($param['id_city'] as $k => $v) {
                $_s = db('cities')->find($k);
                if ($_s) {
                    $city=[
                        'id'=>$k,
                        'sort'=>$v,
                        'status'=>$param['status_city'][$k],
                        'dates'=>time()
                    ];
                    db('cities')->update($city);
                } else {
                    $city=[
                        'city'=>$param['city_name'][$k],
                        'provinceid'=>$param['provinceid'],
                        'sort'=>$param['id_city'][$k],
                        'status'=>$param['status_city'][$k],
                        'type'=>1,
                        'date'=>time()
                    ];
                    db('cities')->insert($city);
                }
            }
            if (!db('provinces')->update($province)) {
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('abroad')];
        } else {
            $province = [
                'provinceid'=>$param['provinceid'],
                'province'=>$param['province'],
                'keywords'=>$param['keywords'],
                'sort'=>$param['sort'],
                'status'=>$param['status'],
                'type'=>1,
                'date'=>time()
            ];
            foreach ($param['city_name'] as $k => $v) {
                $city=[
                    'city'=>$v,
                    'provinceid'=>$param['provinceid'],
                    'sort'=>$param['id_city'][$k],
                    'status'=>$param['status_city'][$k],
                    'type'=>1,
                    'dates'=>time()
                ];
                db('cities')->insert($city);
            }
            if (!db('provinces')->insert($province)) {
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('abroad')];
        }
    }

    /**
     * [status 状态操作]
     * @param  [type] $id [修改id]
     * @param  [type] $type  [操作类型]
     * @return [type]     [description]
     */
    public function status($id, $type)
    {
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id, 'provinces', $type, '');
        return $_result;
    }

    public function status1($id, $type)
    {
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id, 'provinces', $type, '', Url('abroad'));
        return $_result;
    }

    public function status_custom($id, $type)
    {
        $type = ($type=="delete-all")?"delete":$type;
        $_result = $this->_status($id, 'custom', $type, '', Url('custom'));
        return $_result;
    }


    public function abroad()
    {
        $model = [
            'name'=>$this->current
        ];
        $where['type']=1;
        $search = $this->_search();
        $where = array_merge($where, $search);
        $list = db('provinces')
            ->where($where)
            ->order('date desc,status asc')
            ->paginate(15, false, [
                'query'=>[
                    's_keywords'=>input('s_keywords'),
                    "s_date"=>input('s_date'),
                    "s_status"=>input('s_status')
                ]
            ]);
        // 查询状态为1的用户数据 并且每页显示10条数据
        $count = db('provinces')->count('*');
        $this->assign('count', $count);
        $this->assign('model', $model);
        $this->assign('list', $list);
        return view();
    }

    public function set_city($id=0)
    {
        $model = [
            'name'=>'城市管理'
        ];
        if ($id) {
            $city = db('cities')->find($id);
            $this->assign('info', $city);
        }
        $this->assign('model', $model);
        return view();
    }

    public function set_city_handler($id=0, $fid=0)
    {
        $param = request()->param();
        unset($param['fid']);
        if ($id) {
            $param['dates']=time();
            if (!db('cities')->update($param)) {
                return ['status'=>0,'msg'=>'修改失败请重试'];
            }
            return ['status'=>1,'msg'=>'修改成功','redirect'=>Url('add?id='.$fid)];
        } else {
            $param['date']=time();
            if (!db('cities')->insert($param)) {
                return ['status'=>0,'msg'=>'添加失败请重试'];
            }
            return ['status'=>1,'msg'=>'添加成功','redirect'=>Url('add?id='.$fid)];
        }
    }
    /**
     * [del 删除城市]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function del($id=0)
    {
        if (!$id) {
            return ['status'=>0,'msg'=>'缺少参数'];
        }
        $city = db('cities')->find($id);
        if (!$city) {
            return ['status'=>0,'msg'=>'城市不存在'];
        }
        if (!db('cities')->delete($id)) {
            return ['status'=>0,'msg'=>'删除失败'];
        }
        if ($city['image'] && is_file($city['image'])) {
            unlink($city['image']);
        }
        return ['status'=>0,'msg'=>'删除成功'];
    }

    protected function _search($param=[])
    {
        $where=[];
        if (empty($param)) {
            $param = request()->param();
        }
        if (!empty($param['s_keywords'])) {
            $where['province']=['like',"%".$param['s_keywords']."%"];
        }
        if (!empty($param['s_status'])) {
            $where['status']=$param['s_status']>-1?$param['s_status']:'';
        }
        if (!empty($param['s_date'])) {
            $date = explode('-', $param['s_date']);
            $date[1] = "$date[1] 24:00";
            $where['date']=['between',[strtotime($date[0]),strtotime($date[1])]];
        }

        $this->assign('search', [
            's_keywords'=>!empty($param['s_keywords'])?$param['s_keywords']:'',
            's_date'=>!empty($param['s_date'])?$param['s_date']:'',
            's_status'=>!empty($param['s_status'])?$param['s_status']:-1
        ]);
        return $where;
    }
}
