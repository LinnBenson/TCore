<?php

use application\model\admin_menu;
use support\middleware\view;

    class viewController {
        /**
         * 控制面板视图
         */
        public function index( $dir1, $dir2, $dir3, $dir4, $dir5 ) {
            // 检查自定义路径
            $getDir = function( $name ) { return !empty( $name ) ? "/{$name}" : ""; };
            $dir = "admin{$getDir( $dir1 )}{$getDir( $dir2 )}{$getDir( $dir3 )}{$getDir( $dir4 )}{$getDir( $dir5 )}";
            if ( empty( $dir1 ) ) { $dir = 'admin/index'; }
            // 除主页外验证其它页面来源
            $referer = parse_url( $_SERVER['HTTP_REFERER'] );
            if ( $dir !== 'admin/index' && $dir !== 'admin' ) {
                // 权限认证
                $check = admin_menu::where( 'target', preg_replace( '/^admin\/page/', '', $dir ) )->get()->toArray();
                if ( $check && !empty( $check['level'] ) && task::$user->level < $check['level'] ) {
                    return router::error( 404 );
                }
                // 来源认证
                $referer['path'] = rtrim( $referer['path'], '/' );
                if ( $referer['path'] !== '/admin/index' && $referer['path'] !== '/admin' ) { return router::error( 404 ); }
                if ( method_exists( $this, $dir2 ) ) { return $this->$dir2(); }
            }
            // 输出页面
            return view::show( $dir );
        }
        /**
         * 登录界面
         */
        public function login() {
            // 输出页面
            return view::show( 'admin/login' );
        }
        /**
         * 获取菜单
         */
        public static function menu( $res ) {
            $res = $res ? $res->toArray() : [];
            $menu = []; $listMap = [];
            foreach( $res as $item ) {
                if ( task::$user->level < $item['level'] || empty( $item['enable'] ) ) { continue; }
                $itemData = [
                    'type' => $item['type'],
                    'title' => $item['title'],
                    'icon' => $item['icon'],
                    'target' => $item['target']
                ];
                if ( !empty( $item['superior'] ) && $item['type'] !== 'list' ) {
                    if ( empty( $listMap[$item['superior']] ) ) { $listMap[$item['superior']] = []; }
                    $listMap[$item['superior']][] = $itemData;
                }
                if ( empty( $item['superior'] ) ) {
                    $menu[] = $itemData;
                }
            }
            foreach( $menu as $key => $item ) {
                if ( $item['type'] === 'list' ) {
                    $menu[$key]['name'] = $item['target'];
                    $menu[$key]['target'] = is_array( $listMap[$item['target']] ) ? $listMap[$item['target']] : [];
                }
            }
            return $menu;
        }
    }