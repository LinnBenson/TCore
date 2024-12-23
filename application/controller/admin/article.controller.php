<?php

use application\model\article;
use application\model\article_sort;
use application\server\articleServer;
use support\middleware\request;

    class articleController {
        // 添加分类
        public function add_sort() {
            $res = request::get([
                'uid' => 'type:number',
                'name' => 'must:true',
                'public' => 'type:boolean',
                'posts' => 'type:boolean'
            ]);
            $res['uid'] = !empty( $res['uid'] ) ? $res['uid'] : 0;
            $run = articleServer::addSort( $res );
            return task::result( 'create', $run );
        }
        // 发布文章
        public function submit() {
            $res = request::get([
                'uid' => 'must:true,type:number',
                'title' => 'must:true',
                'content' => 'type:md',
                'sort' => 'must:true,type:number',
                'public' => 'type:boolean',
                'release' => 'type:boolean',
                'tag' => '',
                'created_at' => 'type:datetime'
            ]);
            $run = articleServer::submit( $res );
            return task::result( 'release', $run );
        }
        // 修改文章
        public function edit() {
            $res = request::get([
                'id' => 'must:true,type:number',
                'uid' => 'must:true,type:number',
                'title' => 'must:true',
                'content' => 'type:md',
                'sort' => 'must:true,type:number',
                'public' => 'type:boolean',
                'release' => 'type:boolean',
                'tag' => '',
                'created_at' => 'type:datetime'
            ]);
            $run = articleServer::edit( $res );
            return task::result( 'edit', $run );
        }
        // 查看文章
        public function check() {
            $res = request::get([
                'id' => ' must:true,type:number'
            ]);
            $word = article::find( $res['id'] );
            if ( !$word ) { return task::echo( 2, ['error.404'] ); }
            return task::echo( 0, [
                'id' => $word->id,
                'uid' => $word->uid,
                'sort' => $word->sort,
                'sort_name' => article_sort::find( $word->sort )->name,
                'title' => $word->title,
                'content' => $word->content,
                'public' => $word->public,
                'release' => $word->release,
                'tag' => $word->tag,
                'created_at' => toTime( $word->created_at ),
            ]);
        }
    }