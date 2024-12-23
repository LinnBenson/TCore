<?php
use application\model\media;
use application\model\users_login;
use application\server\articleServer;
use application\server\userServer;
use support\middleware\request;
use support\middleware\storage;
use support\middleware\view;

    class tableController {
        /**
         * 允许操作的表配置
         */
        private $config = [
            'users' => [
                'level' => 600,
                'delete' => 600,
                'edit' => [
                    'username' =>  [ 'input' => 'username', 'rule' => [ 'must' => true, 'min' => 4, 'max' => 12 ] ],
                    'email' => [ 'input' => 'email', 'rule' => [ 'must' => true ] ],
                    'phone' => [ 'input' => 'phone', 'rule' => [ 'must' => false ] ],
                    'password' => [ 'input' => 'password', 'rule' => [ 'must' => false ] ],
                    'nickname' => [ 'input' => 'text' ],
                    'slogan' => [ 'input' => 'text', 'rule' => [ 'max' => 120 ] ],
                    'status' => [ 'input' => 'select',  'rule' => [ 'must' => true ], 'data' => [ 'admin' => 'Admin', 'agent' => 'Agent', 'manage' => 'Manage', 'vip' => 'VIP', 'approve' => 'Approve', 'user' => 'User', 'virtual' => 'Virtual' ] ],
                    'enable' => [ 'input' => 'boolean', 'rule' => [] ],
                    'agent' => [ 'input' => 'number', 'rule' => [] ],
                    'remark' => [ 'input' => 'longtext' ]
                ],
                'words' => [ 'id', 'username', 'email', 'phone', 'nickname', 'slogan', 'status', 'enable', 'invite', 'agent', 'agent_node', 'register_ip', 'register_device', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'id', 'username', 'email', 'phone', 'status', 'enable', 'invite', 'agent', 'created_at' ],
                'sort' => [ 'id', 'status', 'enable', 'agent', 'created_at' ],
                'defaultSort' => 'id'
            ],
            'users_login' => [
                'level' => 600,
                'delete' => 600,
                'edit' => [
                    'auth' => [ 'input' => 'boolean', 'rule' => [] ],
                    'enable' => [ 'input' => 'boolean', 'rule' => [] ],
                    'expired' => [ 'input' => 'datetime', 'rule' => [ 'must' => true ] ],
                    'expired_time' => [ 'input' => 'number', 'rule' => [ 'must' => true, 'min' => 0 ] ],
                    'remark' => [ 'input' => 'longtext' ]
                ],
                'words' => [ 'id', 'uid', 'type', 'token', 'auth', 'enable', 'expired', 'expired_time', 'login_id', 'login_ip', 'login_device', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'uid', 'type', 'auth', 'enable', 'expired', 'expired_time', 'login_id', 'login_ip', 'updated_at' ],
                'sort' => [ 'uid', 'type', 'auth', 'enable', 'expired', 'updated_at' ],
                'defaultSort' => 'updated_at'
            ],
            'media' => [
                'level' => 600,
                'delete' => 1000,
                'edit' => false,
                'words' => [ 'id', 'uid', 'storage', 'file', 'public', 'application', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'id', 'uid', 'storage', 'file', 'public', 'application', 'created_at' ],
                'sort' => [ 'id', 'uid', 'storage', 'public', 'created_at' ],
                'defaultSort' => 'id'
            ],
            'push_record' => [
                'level' => 600,
                'delete' => 1000,
                'edit' => false,
                'words' => [ 'id', 'uid', 'type', 'to', 'title', 'content', 'source', 'send_id', 'send_ip', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'id', 'uid', 'type', 'to', 'title', 'content', 'source', 'send_id', 'send_ip', 'created_at' ],
                'sort' => [ 'id', 'uid', 'type', 'to', 'source', 'send_id', 'send_ip', 'created_at' ],
                'defaultSort' => 'id'
            ],
            'router_record' => [
                'level' => 600,
                'delete' => 1000,
                'edit' => false,
                'words' => [ 'id', 'router', 'type', 'result', 'target', 'uid', 'access_id', 'access_ip', 'access_ua', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'id', 'router', 'type', 'result', 'target', 'uid', 'access_id', 'access_ip', 'created_at' ],
                'sort' => [ 'id', 'router', 'type', 'result', 'uid', 'access_id', 'access_ip', 'created_at' ],
                'defaultSort' => 'id'
            ],
            'article_sort' => [
                'level' => 600,
                'delete' => 600,
                'edit' => [
                    'uid' => [ 'input' => 'number', 'rule' => [ 'must' => true ] ],
                    'public' => [ 'input' => 'boolean', 'rule' => [] ],
                    'name' => [ 'input' => 'text', 'rule' => [ 'must' => true ] ],
                    'posts' => [ 'input' => 'boolean', 'rule' => [] ],
                    'remark' => [ 'input' => 'longtext' ]
                ],
                'words' => [ 'id', 'uid', 'public', 'name', 'posts', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'id', 'uid', 'public', 'name', 'posts', 'created_at' ],
                'sort' => [ 'id', 'uid', 'public', 'posts', 'created_at' ],
                'defaultSort' => 'id'
            ],
            'article' => [
                'level' => 600,
                'delete' => 600,
                'edit' => false,
                'words' => [ 'id', 'type', 'uid', 'title', 'sort', 'public', 'release', 'synopsis', 'tag', 'remark', 'updated_at', 'created_at' ],
                'search' => [ 'id', 'type', 'uid', 'title', 'sort', 'public', 'release', 'synopsis', 'tag', 'updated_at', 'created_at' ],
                'sort' => [ 'id', 'type', 'uid', 'sort', 'public', 'release', 'updated_at', 'created_at' ],
                'defaultSort' => 'id'
            ]
        ];
        /**
         * 查询表数据
         */
        public function index() {
            $res = request::get([
                'table' => 'must:true',
                'draw' => 'must:true,type:number',
                'start' => 'must:true,type:number',
                'length' => 'must:true,type:number',
                'search_item' => 'must:false'
            ]);
            // 查询权限
            if ( empty( $this->config[$res['table']] ) || task::$user->level < $this->config[$res['table']]['level'] ) {
                return task::echo( 2, ['error.illegal'] );
            }
            // 整理数据
            $config = $this->config[$res['table']];
            $class = "\application\model\\{$res['table']}";
            $start = $res['start'];
            $size = $res['length'];
            $searchItem = $res['search_item'];
            // 开始查询数据
            $recordsTotal = $class::count();
            $sql = $class::select( $config['words'] );
            // 查询要求
            $search = $_POST['search']; $search = !empty( $search['value'] ) ? $search['value'] : '';
            $order = $_POST['order']; $order = is_array( $order[0] ) ? $order[0] : [];
            $accurate = [ 'like', 'LIKE', '=', '>', '<', '<=', '>=' ];
            // 搜索
            if (
                !empty( $searchItem ) && in_array( $searchItem, $config['search'] ) &&
                !empty( $search )
            ) {
                if ( $search === 'null' ) {
                    $sql = $sql->whereNull( $searchItem );
                }else {
                }
                $type = 'LIKE';
                foreach( $accurate as $item ) {
                    if ( strncmp( $search, $item, strlen( $item ) ) === 0 ) {
                        $search = substr( $search, strlen( $item ) );
                        $type = $item;
                        break;
                    }
                }
                if ( $type ) {
                    if ( $type === 'like' || $type === 'LIKE' ) { $search = "%{$search}%"; }
                    $sql = $sql->where( $searchItem, $type, $search );
                }
            }
            // 排序
            $column = [];
            foreach( $_POST['columns'] as $key => $item ) { $column[$key] = $item['data']; }
            if (
                !empty( $order['column'] ) && !empty( $order['dir'] ) && !empty( $column[$order['column']] ) &&
                in_array( $column[$order['column']], $config['sort'] )
            ) {
                $sql = $sql->orderBy( $column[$order['column']], $order['dir'] );
            }else {
                $sql = $sql->orderBy( $config['defaultSort'], 'DESC' );
            }
            // 分页获取数据
            $recordsFiltered = $sql->count();
            $data = $sql->offset( $start )->limit( $size )->get();
            if ( $data ) { $data = $data->toArray(); }
            // 字段二次处理
            $data = $this->toTime( $data );
            // 输出数据
            $result = [
                'draw' => $res['draw'],
                'recordsTotal' => is_numeric( $recordsTotal ) ? $recordsTotal : 0,
                'recordsFiltered' => is_numeric( $recordsFiltered ) ? $recordsFiltered : 0,
                'data' => is_array( $data ) ? $data : []
            ];
            header( 'Content-Type: application/json' );
            return json_encode( $result, JSON_UNESCAPED_UNICODE );
        }
        /**
         * 查询表行
         */
        public function check() {
            $res = request::get([
                'table' => 'must:true',
                'id' => 'must:true,type:number'
            ]);
            // 查询权限
            if ( empty( $this->config[$res['table']] ) || task::$user->level < $this->config[$res['table']]['level'] ) {
                return task::echo( 2, ['error.illegal'] );
            }
            // 整理数据
            $config = $this->config[$res['table']];
            $class = "\application\model\\{$res['table']}";
            // 开始查询数据
            $row = $class::select( $config['words'] )->where( 'id', $res['id'] )->first();
            if ( $row ) {
                $row = $row->toArray();
                $row = $this->toTime( $row );
                $comment = $class::comment();
                return task::echo( 0, [ 'row' => $row, 'comment' => $comment ] );
            }
            return task::echo( 2, ['false',['type'=>'check']]);
        }
        /**
         * 删除行
         */
        public function delete() {
            $res = request::get([
                'table' => 'must:true',
                'id' => 'must:true,type:number'
            ]);
            // 查询权限
            if ( $this->config[$res['table']]['delete'] && ( empty( $this->config[$res['table']] ) || task::$user->level < $this->config[$res['table']]['delete'] ) ) {
                return task::echo( 2, ['error.illegal'] );
            }
            // 整理数据
            $class = "\application\model\\{$res['table']}";
            // 开始删除数据
            $delete = false;
            if ( $res['table'] === 'users' ) {
                $delete = userServer::delete( $res['id'] );
            }else if ( $res['table'] === 'media' ) {
                $media = media::find( $res['id'] );
                if ( $media ) {
                    $storage = new storage( $media->storage );
                    $delete = $storage->delete( $media->file );
                }
            }else if ( $res['table'] === 'article' ) {
                $delete = articleServer::delete( $res['id'] );
            }else if ( $res['table'] === 'article_sort' ) {
                $delete = articleServer::delSort( $res['id'] );
            }else {
                $delete = $class::where( 'id', $res['id'] )->delete();
            }
            return task::echo( $delete ? 0 : 2, [$delete ? 'true' : 'false',['type'=>'check']]);
        }
        /**
         * 修改行视图
         */
        public function edit_view() {
            $res = request::get([
                'table' => 'must:true',
                'id' => 'must:true,type:number'
            ]);
            // 查询权限
            if ( $this->config[$res['table']]['edit'] && ( empty( $this->config[$res['table']] ) || task::$user->level < $this->config[$res['table']]['level'] ) ) {
                return task::echo( 2, ['error.illegal'] );
            }
            // 整理数据
            $config = $this->config[$res['table']];
            if ( empty( $config['edit'] ) ) {
                return task::echo( 2, ['error.private'] );
            }
            $class = "\application\model\\{$res['table']}";
            $row = $class::select( $config['words'] )->where( 'id', $res['id'] )->first();
            // 输出视图
            $data = [ 'input' => [] ];
            foreach( $config['edit'] as $key => $con ) {
                $wordType = $con['input'];
                $wordRule = $con['rule'];
                $wordData = $con['data'];
                $wordTitle = $class::comment( $key );
                $data['input'][$key] = [
                    'type' => $wordType, 'name' => $key, 'title' => $wordTitle, 'value' => $row[$key]
                ];
                if ( !empty( $wordData ) ) { $data['input'][$key]['data'] = $wordData; }
                if ( isset( $wordRule ) ) { $data['input'][$key]['rule'] = is_array( $wordRule ) ? $wordRule : []; }
            }
            return view::show( 'system/form', $data );
        }
        /**
         * 修改行
         */
        public function edit() {
            $res = request::get([
                'table' => 'must:true',
                'id' => 'must:true,type:number'
            ]);
            // 查询权限
            if ( $this->config[$res['table']]['edit'] && ( empty( $this->config[$res['table']] ) || task::$user->level < $this->config[$res['table']]['level'] ) ) {
                return task::echo( 2, ['error.illegal'] );
            }
            // 整理数据
            $config = $this->config[$res['table']];
            if ( empty( $config['edit'] ) ) {
                return task::echo( 2, ['error.private'] );
            }
            $class = "\application\model\\{$res['table']}";
            // 获取用户填写的数据
            $dataRule = [];
            foreach( $config['edit'] as $key => $con ) {
                $wordRule = $con['rule'] ?? [];
                $rule = [];
                $rule[] = !empty( $wordRule['must'] ) ? 'must:true' : 'must:false';
                if ( !empty( $con['input'] ) ) { $rule[] = "type:{$con['input']}"; }
                if ( !empty( $wordRule['min'] ) ) { $rule[] = "min:{$wordRule['min']}"; }
                if ( !empty( $wordRule['max'] ) ) { $rule[] = "max:{$wordRule['max']}"; }
                $dataRule[$key] = implode( ',', $rule );
            }
            $data = request::get( $dataRule );
            // 辨别需要修改的数据
            foreach( $data as $key => $value ) {
                // 密码检查
                if ( $key === 'password' ) {
                    if ( !empty( $value ) ) {
                        $data[$key] = task::$user->setPassword( $value );
                        users_login::where( 'uid', $res['id'] )->update([ 'enable' => 0 ]);
                    }else { unset( $data[$key] ); continue; }
                }
                // 为空时转为 NULL
                if ( $value === '' ) { $data[$key] = null; }
            }
            $sql = $class::where( 'id', $res['id'] )->update( $data );
            return task::echo( $sql ? 0 : 2, [$sql ? 'true' : 'false',['type'=>'edit']]);
        }
        /**
         * 时间转换
         */
        private function toTime( $data ) {
            if ( !empty( $data['id'] ) ) {
                if ( $data['updated_at'] ) { $data['updated_at'] = toTime( $data['updated_at'], task::$user->time ); }
                if ( $data['created_at'] ) { $data['created_at'] = toTime( $data['created_at'], task::$user->time ); }
                if ( $data['expired'] ) { $data['expired'] = toTime( $data['expired'], task::$user->time ); }
                return $data;
            }
            foreach( $data as $key => $value ) {
                if ( $value['updated_at'] ) { $data[$key]['updated_at'] = toTime( $value['updated_at'], task::$user->time ); }
                if ( $value['created_at'] ) { $data[$key]['created_at'] = toTime( $value['created_at'], task::$user->time ); }
                if ( $value['expired'] ) { $data[$key]['expired'] = toTime( $value['expired'], task::$user->time ); }
            }
            return $data;
        }
    }