<?php
    /**
     * 用于介入干预系统流程
     */
    return [
        // 初始化完成
        'InitializationCompleted' => function() {
            return null;
        },
        // 输出显示给用户的结果
        'OutputReturnResult' => function( $result ) {
            return $result;
        },
    ];