<?php

namespace Support\Slots;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

    /**
     * 全局作用域
     */
    class MysqlGlobal implements Scope {
        public function apply( Builder $builder, Model $model ) {
            $builder->where( 'is_del', 0 );
        }
    }