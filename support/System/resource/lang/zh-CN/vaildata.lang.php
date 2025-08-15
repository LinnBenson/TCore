<?php
    return [
        'required' => '{{name}} 是一个必填项目',
        'type' => [
            'string' => '{{name}} 必须是一个字符串',
            'number' => '{{name}} 只能填写数字',
            'boolean' => '{{name}} 必须是一个布尔值',
            'json' => '{{name}} 必须是一个 JSON 字符串',
            'email' => '{{name}} 必须是一个有效的邮箱',
            'phone' => '{{name}} 必须是一个有效的号码',
            'uuid' => '{{name}} 必须是一个有效的 UUID',
            'alnum' => '{{name}} 必须是字母或数字',
            'datetime' => '{{name}} 必须是一个有效的完整时间',
            'date' => '{{name}} 必须是一个有效的日期',
            'time' => '{{name}} 必须是一个有效的时间',
            'upload' => '{{name}} 上传了不支持的文件',
            'verify' => '图形验证码错误，请检查或刷新后重试',
        ],
        'length' => [
            'min' => '{{name}} 的长度不能小于 {{length}}',
            'max' => '{{name}} 的长度不能大于 {{length}}',
        ],
        'size' => [
            'min' => '{{name}} 的值不能小于 {{size}}',
            'max' => '{{name}} 的值不能大于 {{size}}',
        ],
        'quantity' => [
            'min' => '{{name}} 的数量不能小于 {{quantity}}',
            'max' => '{{name}} 的数量不能大于 {{quantity}}',
        ],
        'regex' => '{{name}} 的格式不正确',
        'safe' => '{{name}} 包含了不安全的字符',
        'nullReceive' => '请先填写您的接收信息',
    ];