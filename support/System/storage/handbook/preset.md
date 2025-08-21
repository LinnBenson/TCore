### 系统预设插件
- 管理员面板 : `AdminPanel`
- 视图渲染器 : `ViewRenderer`

### 系统预设接口
- 登录接口 : `/api/account/login`
  - POST
  - `username[手机号|用户名|邮箱], password[密码], remember[记住状态]`
- 请求系统信息 : `/api/base/info/{type}`
  - GET
  - `type[server|text|all]`
- 上传文件 : `/storage/upload/{storage}`
  - POST
  - `storage[存储器名称]`
- 获取文件 : `/storage/file/{{storage}}/{{file}}`
  - GET
  - `storage[存储器名称], file[访问文件]`
- 获取用户头像 : `/storage/avatar/{{uid}}`
  - GET
  - `uid[用户 UID]`

### View renderer 预设模板
- 网页 Header
    ```
    View( 'ViewHeader', [ 'id' => '框架 ID', 'title' => '页面标题' ])

    // 同时支持传递以下变量
    [
        'css' => '绑定 CSS 文件',
        'js' => '绑定 JS 文件',
        'icon' => '网页图标',
        'keywords' => 'SEO 优化关键词',
        'description' => 'SEO 优化描述'
    ]
    ```
- 网页 Footer
    ```
    View( 'ViewFooter', [] )

    // 在模板中使用空的共享变量时系统会自动加上 [ 'request' => $request ]
    ```
- 卡片组件
    ```
    <x-card id="ID" class="CLASS" title="卡片标题" icon="卡片图标" close="关闭状态">卡片内容</x-card>
    ```
- Form 表单
    ```
    // 注：此模块只能搭配 <x-input></x-input> 使用，自建表单无效

    <x-form left="左右分栏" submit="提交处理方式" button="提交按钮" reset="显示重置按钮">
        ... 表单内容
    </x-form>

    // 左右分栏：可填写左栏的宽度（如 100px），或填写 true 使用默认宽度，不使用此值则不分栏
    // 提交处理方式："view.submit" 或 "/api/submit|view.submit" 使用了链接时系统会自动提示，并将回调的值传递给方法，否则将表单值传递给方法
    // 提交按钮：示例（bi-star|按钮名称）
    ```
- input 输入框
    ```
    <x-input type="输入框类型" name="名称" rule="验证规则" icss="输入框 CSS" left="左右分栏" hint="提示文字" tips="列表提示文字" value="默认值">输入框标题</x-input>

    // 输入框类型：string/alnum/number/email/password/uuid/phone/select/datetime/date/time/code/longtext/switch/upload/markdown
    // 验证规则：示例（required|type:alnum|min:8|max:10）
    // 左右分栏：可填写左栏的宽度（如 100px），或填写 true 使用默认宽度，不使用此值则不分栏
    // 列表提示文字: 示例 "每一条|第二条"，也可加上链接 "每一条:/api/a|第二条:/api/b"

    // select 专属值：options="值1:标题1|值2:标题2" [选项]
    // upload 专属值：submit="https://api/upload" [上传地址]
    // upload value 格式："图片地址1|图片地址2|图片地址3"
    ```