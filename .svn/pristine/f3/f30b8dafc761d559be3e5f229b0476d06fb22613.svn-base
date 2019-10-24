<?php
namespace Estool;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/10/14 0014
 * Time: 15:51
 */
class es{
    private $CONN = null;
    private $index;
    private $type;
    private $where = [];
    private $limit = [];
    private $keyword = [];
    private $between = [];
    private $fields = [];
    private $whereNot = [];
    private $keyword_high = [];
    private $group = null;
    private $operator_type = [
        '>'=>'gt',
        '<'=>'lt',
        '>='=>'gte',
        '<='=>'lte',
    ];
    private $orderBy = [];
    function __construct($index){
        $this->index = $index;
        $this->type = $index;
    }

    private function conn(){
        if($this->CONN === null){
            $conf = \tool::appConfig('es');
            $hosts = [
                [
                    'host' => $conf['host'],
                    'port' => $conf['port'],
                    'scheme' => $conf['scheme'],
                ],
            ];
            $this->CONN = \Elasticsearch\ClientBuilder::create()
                ->setHosts($hosts)
                ->build();
        }
        return $this->CONN;
    }

    /**
     *
     * @param $field
     */
    function fields($field){
        $field = explode(',',$field);
        if(empty($field)){
            throw new \Exception('fields : field cannot be empty');
        }
        $this->fields = $field;
        return $this;
    }

    /**
     * 添加where条件
     * @param $field
     * @param $value
     * @throws \Exception
     */
    function where($field,$value,$operator = '='){
        if(empty($field)){
            throw new \Exception('where : field cannot be empty');
        }
        if($operator == '='){
            $this->where[] = [
                'term' => [
                    $field=>$value
                ]
            ];
        }else{
            if(!isset($this->operator_type[$operator])){
                throw new \Exception('where : operator not defined');
            }
            $this->where[] = [
                'range' => [
                    $field=>[
                        $this->operator_type[$operator] => $value,
                    ]
                ]
            ];
        }
        return $this;
    }

    /**
     * where条件 不等于
     * @param $field
     * @param $value
     * @return $this
     * @throws \Exception
     */
    function whereNot($field,$value){

        if(empty($field)){
            throw new \Exception('whereNot : field cannot be empty');
        }

        $this->whereNot[] = [
            'term'=>[
                $field=>[
                    'value'=>$value
                ]
            ]
        ];

        return $this;
    }

    /**
     * 设置分页
     * @param $page
     * @param $size
     */
    function limit($page,$size){
        $page = intval($page);
        $size = intval($size);

        $this->limit = [($page-1)*$size,$size];
        return $this;
    }

    /**
     * 设置查询关键字
     * @param $field
     * @param $value
     * @throws \Exception
     */
    function keyword($field,$value,$high = false){
        if(empty($field)){
            throw new \Exception('keyword : field cannot be empty');
        }
        if(empty($value)){
            throw new \Exception('keyword : value cannot be empty');
        }
        $this->keyword[$field] = $value;
        if($high === true){
            $this->keyword_high[] = $field;
        }
        return $this;
    }

    /**
     * 设置范围值条件
     * @param $field
     * @param $min_val
     * @param $max_val
     * @throws \Exception
     */
    function between($field,$min_val,$max_val){
        if(empty($field)){
            throw new \Exception('between : field cannot be empty');
        }
        $this->between[$field] = [$min_val,$max_val];
        return $this;
    }

    /**
     * 排序条件
     * @param $field
     * @param string $sort
     * @throws \Exception
     */
    function orderBy($field,$sort='desc'){
        $sort = strtolower($sort);
        if(!in_array($sort,['desc','asc'])){
            throw new \Exception('orderBy : sort type must be asc or desc');
        }
        if(empty($field)){
            throw new \Exception('orderBy : field cannot be empty');
        }
        $this->orderBy[$field] = $sort;
        return $this;
    }

//    /**
//     * 分组
//     * @param $field
//     * @return $this
//     * @throws \Exception
//     */
//    function groupBy($field){
//        if(empty($field)){
//            throw new \Exception('between : field cannot be empty');
//        }
//        $this->group = $field;
//        return $this;
//    }

    /**
     * 添加索引数据
     * @param $data
     */
    public function addData($data,$_id=null){
        $client = $this->conn();
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'body' => $data
        ];
        if($_id){
            $params['id'] = $_id;
        }
        $res = $client->index($params);
        if($res['_shards']['successful'] <= 0){
            return false;
        }else{
            return $res;
        }
    }

    /**
     * 查询数据[多行]
     * @return array
     */
    function select(){
        $params = [];
        $body = [];
        $res1 = [];
        $body['query']['bool']['must'] = [];
        $params['index'] = $this->index;
        $params['type'] = $this->type;
        $params['from'] = $this->limit[0];
        $params['size'] = $this->limit[1];

        //  添加关键字
        if($this->keyword){
            foreach($this->keyword as $k=>$v){
                $body['query']['bool']['must'][] = [
                    'match' => [
                        $k=>$v
                    ]
                ];
            }
        }

        //  添加where条件
        if($this->where){
            foreach($this->where as $k=>$v){
                $body['query']['bool']['must'][] = $v;
            }
        }

        //  添加范围过滤条件
        if($this->between){
            foreach($this->between as $k=>$v){
                $body['query']['bool']['must'][] = [
                    'range' => [
                        $k=>[
                            'gt' => $v[0],
                            'lt' => $v[1],
                        ]
                    ]
                ];
            }
        }

        //  添加排序
        if($this->orderBy){
            foreach($this->orderBy as $k=>$v){
                $body['sort'][] = [$k=>['order'=>$v]];
            }
        }

        //  添加搜索字段
        if($this->fields){
            $body['_source'] = $this->fields;
        }

        //  添加不等于条件
        if($this->whereNot){
            foreach($this->whereNot as $k=>$v){
                $body['query']['bool']['must_not'][] = $v;
            }
        }

        //  添加高亮字段
        if($this->keyword_high){
            foreach($this->keyword_high as $k=>$v){
                $body['highlight']['fields'][$v] = (object)[];
            }
        }

        //  分组
//        if($this->group !== null){
//            $params['aggs']['group_by_state']['terms']['field'] = $this->group;
//        }

        $params['body'] = $body;
        $res = $this->conn()->search($params);

        if(!$res['hits']['hits']){
            $res = [];
        }else{
            $res = $res['hits']['hits'];
            foreach($res as $k=>$v){
                $_source = $v['_source'];
                $_source['_id'] = $v['_id'];
                $_source['_score'] = $v['_score'];

                //  过滤高亮字段
                if(isset($v['highlight'])){
                    foreach($v['highlight'] as $k1=>$v1){
                        $_source[$k1 . '_high'] = $v1[0];
                    }
                }

                $res1[] = $_source;
            }
            $res = $res1;

        }
        return $res;
    }

    /**
     * 根据索引id更新数据
     * @param $id
     * @param $data
     * @return bool
     * @throws \Exception
     */
    function updateID($id,$data){
        if(!is_array($data)){
            throw new \Exception('updateID : data has to be an array');
        }
        $params['index'] = $this->index;
        $params['type'] = $this->type;
        $params['id'] = $id;
        $params['body']['doc'] = $data;
        $res = es::conn()->update($params);
        if($res['_shards']['successful'] <= 0){
            return false;
        }else{
            return true;
        }
    }

    /**
     * 根据where条件更新数据
     * @param array $where
     * @param $data
     * @throws \Exception
     */
    function updateWhere($data){
        $body = [];
        $params = [];
        $params['index'] = $this->index;
        $params['type'] = $this->type;

        //  添加where条件
        if($this->where){
            foreach($this->where as $k=>$v){
                $body['query']['bool']['must'][] = $v;
            }
        }

        //  添加不等于条件
        if($this->whereNot){
            foreach($this->whereNot as $k=>$v){
                $body['query']['bool']['must_not'][] = $v;
            }
        }

        if(!is_array($data)){
            throw new \Exception('updateWhere : data has to be an array');
        }
        if(empty($data)){
            throw new \Exception('updateWhere : data cannot be empty');
        }

        $script = [];

        foreach($data as $k=>$v){
            $script[] = "ctx._source.{$k}='{$v}'";
        }
        $body['script']['inline'] = implode(';',$script);

        $params['body'] = $body;

        $this->conn()->updateByQuery($params);
    }

    /**
     * 根据where删除数据
     * @param $where
     * @throws \Exception
     */
    function deleteWhere(){
        $params = [];
        $body = [];
        $params['index'] = $this->index;
        $params['type'] = $this->type;

        if(empty($this->where)){
            throw new \Exception('deleteWhere : where cannot be empty');
        }

        //  添加where条件
        foreach($this->where as $k=>$v){
            $body['query']['bool']['must'][] = $v;
        }

        //  添加不等于条件
        if($this->whereNot){
            foreach($this->whereNot as $k=>$v){
                $body['query']['bool']['must_not'][] = $v;
            }
        }

        $params['body'] = $body;
        $this->conn()->deleteByQuery($params);
    }

    /**
     * 删除索引
     */
    function deleteIndex(){
        $this->conn()->indices()->delete(['index'=>$this->index]);
    }

    /**
     * 根据主键id删除数据
     * @param $id
     */
    function deleteID($id){
        $params = [
            'index' => $this->index,
            'type' => $this->type,
            'id' => $id
        ];
        $this->conn()->delete($params);
    }


}