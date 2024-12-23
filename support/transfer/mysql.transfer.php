<?php
namespace support\transfer;

use core;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;

    class mysqlBasics extends Model {
        /**
         * 重建表
         */
        public static function reset() {
            // 表名
            $tableName = (new static())->getTable();
            // 删除表
            if ( core::$db::schema()->hasTable( $tableName ) ) { core::$db::schema()->drop( $tableName ); }
            // 创建表
            core::$db::schema()->create( $tableName, function( Blueprint $table ) {
                /**
                 * $table->increments / $table->string( $word, $len ) / $table->unsignedTinyInteger( $word ) / $table->timestamps( $word )
                 * ->unique() 唯一
                 * ->unsigned() 无符号
                 * ->nullable() 允许为空
                 * ->default( 1 ) 默认值
                 * ->comment( 'comment' ) 备注
                 */
                $table->increments( 'id' )->comment( 'ID' );
                foreach( static::$words as $key => $info ) {
                    $item = null;
                    switch ( $info['type'][0] ) {
                        case 'string':
                            $item = $table->string( $key, $info['type'][1] );
                            break;
                        case 'int':
                            $item = $table->unsignedTinyInteger( $key );
                            break;
                        case 'integer':
                            $item = $table->integer( $key );
                            break;
                        case 'unsignedInteger':
                            $item = $table->unsignedInteger( $key );
                            break;
                        case 'decimal':
                            $item = $table->integer( $key, $info['type'][1], $info['type'][2] );
                            break;
                        case 'boolean':
                            $item = $table->boolean( $key );
                            break;
                        case 'time':
                            $item = $table->timestamp( $key );
                            break;
                        case 'longtext':
                            $item = $table->longtext( $key, $info['type'][1] );
                            break;
                        case 'text':
                            $item = $table->text( $key, $info['type'][1] );
                            break;
                        case 'json':
                            $item = $table->json( $key, $info['type'][1] );
                            break;

                        default: break;
                    }
                    if( !empty( $info['unique'] ) ) { $item = $item->unique(); }
                    if( !empty( $info['unsigned'] ) ) { $item = $item->unsigned(); }
                    if( !empty( $info['nullable'] ) ) { $item = $item->nullable(); }
                    if( !empty( $info['default'] ) || $info['default'] === 0 || $info['default'] === '0' ) { $item = $item->default( $info['default'] ); }
                    if( !empty( $info['comment'] ) || $info['default'] === 0 || $info['default'] === '0' ) { $item = $item->comment( $info['comment'] ); }
                }
                $table->text( 'remark' )->comment( '备注' )->nullable();
                $table->timestamps();
            });
            // 插入数据
            static::setData();
            return true;
        }
        /**
         * 查询字段备注
         */
        public static function comment( $check = false ) {
            $comment = [];
            foreach( static::$words as $key => $info ) {
                $comment[$key] = !empty( $info['comment'] ) ? $info['comment'] : $key;
            }
            if ( !empty( $check ) && empty( $comment[$check] ) ) { return $check; }
            return !empty( $check ) ? $comment[$check] : $comment;
        }
    }