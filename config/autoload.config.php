<?php
return array (
	'core' => array (
		'loader\\user' => 'loader/user.loader.php',
		'support\\middleware\\view' => 'support/middleware/view.middleware.php',
		'support\\middleware\\request' => 'support/middleware/request.middleware.php',
		'support\\middleware\\storage' => 'support/middleware/storage.middleware.php',
		'support\\middleware\\access' => 'support/middleware/access.middleware.php',
		'support\\method\\RE' => 'support/method/redis.method.php',
		'support\\method\\DB' => 'support/method/mysql.method.php',
		'support\\method\\tool' => 'support/method/tool.method.php',
		'support\\method\\web' => 'support/method/web.method.php',
		'support\\method\\push' => 'support/method/push.method.php',
		'support\\transfer\\mysqlBasics' => 'support/transfer/mysql.transfer.php',
	),
	'server' => array (
		'application\\server\\userServer' => 'application/server/user.server.php',
		'application\\server\\manageServer' => 'application/server/manage.server.php',
		'application\\server\\serviceServer' => 'application/server/service.server.php',
		'application\\server\\articleServer' => 'application/server/article.server.php',
		'application\\server\\updateServer' => 'application/server/update.server.php',
	),
	'model' => array (
		'application\\model\\users' => 'application/model/users.model.php',
		'application\\model\\users_login' => 'application/model/users_login.model.php',
		'application\\model\\admin_menu' => 'application/model/admin_menu.model.php',
		'application\\model\\media' => 'application/model/media.model.php',
		'application\\model\\push_record' => 'application/model/push_record.model.php',
		'application\\model\\router_record' => 'application/model/router_record.model.php',
		'application\\model\\article' => 'application/model/article.model.php',
		'application\\model\\article_sort' => 'application/model/article_sort.model.php',
	),
);