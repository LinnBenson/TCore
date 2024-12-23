<?php
namespace application\server;

use application\model\article;
use application\model\article_sort;
use application\model\media;
use support\middleware\storage;
use task;

    class articleServer {
        // 创建分类
        public static function addSort( $res ) {
            // 数据整理
            if ( !isset( $res['uid'] ) ) { $res['uid'] = task::$user->uid; }
            // 创建分类
            return article_sort::create( $res );
        }
        // 删除分类
        public static function delSort( $id ) {
            $check = article_sort::where( 'id', $id )->delete();
            if ( !$check ) { return false; }
            article::where( 'sort', $id )->update([ 'sort' => 1 ]);
            return true;
        }
        // 发布文章
        public static function submit( $res ) {
            // 数据整理
            if ( !isset( $res['uid'] ) ) { $res['uid'] = task::$user->uid; }
            // 检查分类是否有效
            $res['sort'] = self::checkSort( $res );
            // 开始写入数据
            $data = self::ready( $res );
            self::mediaPublic( $data );
            return article::create( $data );
        }
        // 修改文章
        public static function edit( $res ) {
            // 数据整理
            if ( !isset( $res['uid'] ) ) { $res['uid'] = task::$user->uid; }
            // 获取文章信息
            $article = article::find( $res['id'] );
            if ( !$article ) { return false; }
            $res['media'] = $article['media'];
            // 检查分类是否有效
            $res['sort'] = self::checkSort( $res );
            // 开始写入数据
            $upload = self::ready( $res );
            self::mediaPublic( $upload );
            if ( $upload['created_at'] === null ) { unset( $upload['created_at'] ); }
            return article::where( 'id', $res['id'] )->update( $upload );
        }
        // 删除文章
        public static function delete( $id ) {
            $media = article::where('id', $id)->first( 'media' );
            if ( $media ) { $media = $media->media; }
            // 从数据库删除文章
            $check = article::where( 'id', $id )->delete();
            if ( !$check ) { return false; }
            // 清空用户上传的内容
            if ( $media && is_json( $media ) ) {
                $media = json_decode( $media, true );
                if ( !empty( $media ) && is_array( $media ) ) {
                    $storage = new storage( 'word' );
                    foreach( $media as $item ) {
                        $storage->delete( $item );
                    }
                }
            }
            return true;
        }
        // 检查分类是否合法
        private static function checkSort( $res ) {
            $draft = 1;
            if ( empty( $res['sort'] ) ) { return $draft; }
            $sort = article_sort::find( $res['sort'] );
            if ( !$sort ) { return $draft; }
            if ( task::$user->level < 1000 && $sort->uid !== task::$user->uid ) {
                if ( $sort->uid !== 0 ) { return $draft; }
                if ( empty( $sort->posts ) ) { return $draft; }
            }
            return $res['sort'];
        }
        // 整理文章
        private static function ready( $res ) {
            // $tag 整理
            $tag = [];
            $resTag = explode( ',', $res['tag'] );
            foreach( $resTag as $item ) {
                $item = trim( $item );
                if ( strlen( $item ) < 18 ) { $tag[] = $item; }
            }
            $tag = implode( ',', $tag );
            // UID 整理
            $uid = $res['uid'] ?? task::$user->uid;
            // 媒体整理
            $res['media'] = is_json( $res['media'] ) ? json_decode( $res['media'], true ) : [];
            $media = self::media( $uid, $res['content'] );
            $res['content'] = $media['content'];
            $res['media'] = is_array( $media['media'] ) ? array_merge( $res['media'], $media['media'] ) : $res['media'];
            // 准备内容
            $article = [
                'uid' => $uid,
                'type' => 'markdown',
                'title' => $res['title'] ?? '',
                'sort' => $res['sort'] ?? 1,
                'public' => $res['public'] ?? 0,
                'release' => $res['release'] ?? 0,
                'synopsis' => mb_substr( self::stripMarkdown( $res['content'] ), 0, 100, 'UTF-8' ) ?? '',
                'content' => $res['content'] ?? '',
                'media' => json_encode( $res['media'] ),
                'tag' => $tag,
                'created_at' => empty( $res['created_at'] ) ? null : $res['created_at']
            ];
            return $article;
        }
        // 去除 Markdown 标记
        private static function stripMarkdown( $text ) {
            $patterns = [
                '/\*\*(.*?)\*\*/',        // 粗体 **text**
                '/\*(.*?)\*/',            // 斜体 *text*
                '/\_(.*?)\_/',            // 下划线 _text_
                '/\~\~(.*?)\~\~/',        // 删除线 ~~text~~
                '/\[(.*?)\]\((.*?)\)/',   // 链接 [text](url)
                '/\!\[(.*?)\]\((.*?)\)/', // 图片 ![alt](url)
                '/\#/',                   // 标题 #
                '/\>\s/',                 // 引用 >
                '/\`\`\`(.*?)\`\`\`/',            // 代码 `text`
                '/\`(.*?)\`/',            // 行内代码 `text`
                '/\n\s*\n/',              // 多余的换行
            ];
            $text = preg_replace( $patterns, '', $text );
            $text = str_replace( ["\r", "\n"], '', $text );
            return preg_replace( '/&[a-zA-Z0-9#]+;/', '', trim( $text ) );;
        }
        // 媒体处理
        private static function media( $uid, $markdown ) {
            $pattern = '/!\[.*?\]\((.*?)\)/';
            preg_match_all( $pattern, $markdown, $matches );
            $storage = new storage( 'word' ); $media = [];
            foreach( array_unique( $matches[1] ) as $item ) {
                if ( strpos( $item, '/storage/cache' ) === 0 ) {
                    $fileLink = $storage->cacheSave( $item, [
                        'uid' => $uid,
                        'public' => 0,
                        'application' => 'article'
                    ]);
                    $media[] = $fileLink;
                    $markdown = str_replace( $item, $fileLink."?type=abbreviation", $markdown );
                }
            }
            return [
                'media' => $media,
                'content' => $markdown
            ];
        }
        // 同步权限信息
        private static function mediaPublic( $res ) {
            $media = is_json( $res['media'] ) ? json_decode( $res['media'], true ) : [];
            foreach( $media as $item ) {
                $item = explode( '/', $item ); $item = $item[count( $item ) - 1];
                media::where( "uid", $res['uid'] )->where( 'storage', 'word' )->where( 'file', $item )->update([ 'public' => $res['public'] ]);
            };
        }
    }