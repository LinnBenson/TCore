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