# TCore For PHP
---
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

### 更新框架
在主目录中运行以下代码
```
php update
# 使用 php update all 忽略版本号进行强制更新
```

### 开发说明
- `app/Bootstrap/MainProcess.php` 可用于介入系统的运行逻辑。此处的代码将高于系统进行执行。
- `controller()` 与 `task()` 调用时会首先检查对象中是否存在公共 `__` 函数，如果存在，则会先执行它，如果它返回的不是 null ，则会直接返回它的内容，不再执行用户指定的方法。
- 在给路由添加验证时，验证器返回 null 则表示验证通过，否则会将验证器的输出值返回给用户。
- 路由目标支持模糊化书写，如 `/test/{{p1}}/{{p2?}}` ，p1 将为此位置的变量传递给执行方法，加问号的 p2 则为接下来的所有内容