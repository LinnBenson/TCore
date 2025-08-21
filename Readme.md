# TCore For PHP

> 轻巧便携的 PHP 开发框架

### 安装
1. 修改运行目录为 `public`
2. 设置伪静态
    ```
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    ```
3. 在根目录执行命令安装扩展
    ```
    composer install
    ```
4. 执行管理菜单安装需要的数据库
    ```
    php cmd
    // 开发功能->数据库模型->[需要安装的模型]->重装模型
    ```
5. [可选] 如果您需要默认的后台管理系统
    ```
    php cmd
    // 插件管理->Admin panel->初始化面板
    ```

### Shell 命令
- 主管理菜单
    ```
    php cmd
    ```
- 系统更新
    ```
    php update
    // 系统将自动从 config( 'update.git' ) 链接下获取最新版本，并自动更新。
    // 此更新为强制覆盖，如果版本号相同，也会刷新为远程版本。
    ```

### 其它需知
- 系统原生配置保存在 `support/System/config/` 目录在，您可以在 `config/` 目录下创建同名配置文件，系统将自动合并两个配置文件，并优先使用用户配置的值。（ 语言包文件同理 ）

### 链接到其它帮助文档
- [目录说明](support/System/storage/handbook/directory.md)
- [视图渲染器](support/System/storage/handbook/view.md)
- [插件及权限](support/System/storage/handbook/permissions.md)
- [方法声明](support/System/storage/handbook/method.md)
- [预设说明](support/System/storage/handbook/preset.md)