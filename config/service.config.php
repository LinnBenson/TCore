<?php
return array (
	'async' => array (
		'public' => '',
		'protocol' => 'websocket',
		'port' => 36001,
		'name' => 'Async Service',
		'run' => 'www',
		'thread' => 10,
	),
	'chat' => array (
		'public' => 'ws://dev.io/chat',
		'protocol' => 'websocket',
		'port' => 36002,
		'name' => 'Chat Service',
		'run' => 'www',
		'thread' => 10,
	),
);