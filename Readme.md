# TCore For PHP

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