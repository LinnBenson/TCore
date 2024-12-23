<?php
return array (
	'core' => array (
		'file' => 'storage/log/core.log',
		'maxSize' => 3000000,
	),
	'debug' => array (
		'file' => 'storage/log/debug.log',
		'maxSize' => 3000000,
	),
	'redis' => array (
		'file' => 'storage/log/redis.log',
		'maxSize' => 3000000,
	),
	'service_async' => array (
		'file' => 'storage/log/service_async.log',
		'maxSize' => 3000000,
	),
	'service_chat' => array (
		'file' => 'storage/log/service_chat.log',
		'maxSize' => 3000000,
	),
);