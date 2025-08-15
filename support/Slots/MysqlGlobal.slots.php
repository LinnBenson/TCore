<?php
    /**
     * Mysql 模型全局作用域
     */
    namespace Support\Slots;

    use Illuminate\Database\Eloquent\Scope;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\Builder;

    class MysqlGlobal implements Scope {
        public function apply( Builder $builder, Model $model ) {
            // 限制只查询未删除的数据
            $builder->where( 'is_del', 0 );
        }
    }