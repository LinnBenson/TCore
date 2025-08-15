<?php
    /**
     * Mysql 模型支持
     */
    namespace Support\Slots;

    use Support\Bootstrap;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Schema\Blueprint;
    use Support\Helper\Tool;

    class Mysql extends Model {
        // 表信息
        public static $name = '';
        public static $info = [];
        // 时间转换
        protected function getCreatedAtAttribute( $value ) { return toDate( $value ); }
        protected function getUpdatedAtAttribute( $value ) { return toDate( $value ); }
        /**
         * 创建表
         * - return boolean 创建结果
         */
        public static function up() {
            self::down();
            $tableName = explode( '|', static::$name );
            $tableInfo = static::$info;
            Bootstrap::$db::schema()->create( $tableName[0], function( Blueprint $table )use ( $tableInfo ) {
                $table->id()->comment( 'ID' );
                foreach( $tableInfo as $key => $value ) {
                    $key = explode( '|', $key );
                    $info = Tool::toArray( $value );
                    $word = null;
                    switch ( $info ) {
                        case $info['type'] === 'string':
                            $word = $table->string( $key[0], $info['length'] ?? 255 );
                            break;
                        case $info['type'] === 'uid':
                            $word = $table->bigInteger( $key[0] )->nullable();
                            break;
                        case $info['type'] === 'number':
                            $word = $table->bigInteger( $key[0] )->nullable();
                            break;
                        case $info['type'] === 'boolean':
                            $word = $table->boolean( $key[0] )->default( 0 );
                            break;
                        case $info['type'] === 'float':
                            $word = $table->float( $key[0], $info['decimal'] ?? 16, $info['decimal'] ?? 6 );
                            break;
                        case $info['type'] === 'double':
                            $word = $table->double( $key[0], $info['decimal'] ?? 16, $info['decimal'] ?? 6 );
                            break;
                        case $info['type'] === 'decimal':
                            $word = $table->decimal( $key[0], $info['length'] ?? 16, $info['decimal'] ?? 6 );
                            break;
                        case $info['type'] === 'text':
                            $word = $table->text( $key[0] );
                            break;
                        case $info['type'] === 'longtext':
                            $word = $table->longText( $key[0] );
                            break;
                        case $info['type'] === 'json':
                            $word = $table->json( $key[0] );
                            break;
                        case $info['type'] === 'datetime':
                            $word = $table->dateTime( $key[0] );
                            break;
                        case $info['type'] === 'timestamp':
                            $word = $table->integer( $key[0] );
                            break;
                        case $info['type'] === 'uuid':
                            $word = $table->string( $key[0], 36 );

                        default: break;
                    }
                    if ( empty( $word ) ) { continue; }
                    if ( isset( $info['default'] ) ) { $word->default( $info['default'] ); }
                    if ( $info['null'] === true ) { $word->nullable(); }
                    if ( $info['only'] === true ) { $word->unique(); }
                    if ( !empty( $key[1] ) ) { $word->comment( $key[1] ); }
                }
                $table->boolean( 'is_del' )->default( 0 )->nullable()->comment( 'Deletion Status' );
                $table->timestamps();
            });
            return static::initialization();
        }
        /**
         * 删除表
         * - return boolean 删除结果
         */
        public static function down() {
            $tableName = explode( '|', static::$name );
            if ( Bootstrap::$db::schema()->hasTable( $tableName[0] ) ) {
                Bootstrap::$db::schema()->dropIfExists( $tableName[0] );
            }
            return true;
        }
        /**
         * 初始化写入
         * - return boolean 写入结果
         */
        public static function initialization() { return true; }
        /**
         * 字段查询
         * - $check: 查询字段[string|null]
         * - return string|array 查询结果
         */
        public static function words( $check = null ) {
            $result = [];
            $tableInfo = static::$info;
            foreach( $tableInfo as $key => $value ) {
                $key = explode( '|', $key );
                $result[$key[0]] = $key[1] ?? $key[0];
            }
            return !empty( $check ) ? $result[$check] : $result;
        }
    }