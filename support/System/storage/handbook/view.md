### View Renderer 视图渲染器
- 注释内容
  ```
  {{/* 这是一段注释 */}}
  ```
- 输出变量
  ```
  {{ $variable }}
  ```
- PHP 代码
  ```
  {{!! $variable = '1'; echo $variable; !!}}
  ```
- 判断语句
  ```
  @if( $variable === 1 ):
      ...
  @elseif( $variable === 2 ):
      ...
  @else:
      ...
  @endif
  ```
- foreach 循环
  ```
  @foreach( $variable as $key => $value ):
      ...
  @endforeach
  ```
- for 循环
  ```
  @for( $i = 0; $i < 5; $i++ ):
      ...
  @endfor
  ```
- while 循环
  ```
  @while( $variable < 5 ):
      ...
  @endwhile
  ```
- 组件调用
  ```
  // 调用 resource/view/module/ 目录下的组件
  <x-...></x-...>
  // 调用 resource/view/ 目录下的组件
  <x-_...></x-_...>
- 视图名称: String `{{ $ViewName }}`
- 当前主题: Array `{{ $theme['...'] }}`
- 当前主题名称: String `{{ $themeName }}`
- 文件版本: String `{{ $v }}`
- 系统版本: String `{{ $version }}`
- 当前语言: String `{{ $lang }}`
- 调用静态文件: Function `{{ $assets( '...' ) }}`
- 当前请求: Object `{{ $request }}`
- 语言包使用:
  ```
  {{ $t( '...' ) }} // 调用全局语言包
  {{ $t( '&...' ) }} // 调用视图同名下的语言包

### 内置 JS 方法
- tc.init 页面构建完成状态[boolean]
- tc.text 语言包[array]
- tc.size 当前屏幕尺寸[array]
- tc.server 服务器信息[array]
- tc.user 用户信息[array|null]
- 刷新信息
  - tc.refresh( 刷新类型[all|server|text] )
  - return void
- 缓存操作
  - tc.setCache( 缓存名称[string], 缓存值[mixed|null] )
  - return boolean 设置结果
- 发起一个网络请求
  - tc.send( 请求数据[array], 触发加载[method|false]|false )
  - return object Ajax 请求实体
- 验证 API 数据
  - tc.res( 请求数据[array], 返回结果[mixed] )
  - return mixed 返回结果
- 附加请求头
  - tc.header()
  - return array 请求头数据
- 登录用户
  - tc.login( 登录数据[array] )
  - return boolean 登录结果
- 登出用户
  - tc.logout( 显示通知[boolean]|false )
  - return boolean 登出结果
- 路由监听
  - tc.touter( 监听方法[method] )
  - return void
- Toast 提示
  - tc.unit.toast( 通知信息[string], 错误信息[boolean]|false, 超时时间[number]|3000 )
  - return true
- 加载控件
  - tc.unit.load( 加载状态[array|boolean], 超时时间[number]|false )
  - return true
- 加载盒子
  - tc.unit.boxLoad( 元素[string], 加载状态[boolean], 超时时间[number]|false )
  - return true
- 为元素执行动画
  - tc.unit.animation( [jQurey], 动画名[string], 动画时长[number]|180 )
  - return jQurey
- 获取表单值
  - tc.form.value( 框架元素[string] )
  - return array|false 表单内容
- 内容转义
  - tc.markdown.protect( 文档内容[string], 是否还原[boolean]|false )
  - return string 处理后的内容
- Markdown 转 html
  - tc.markdown.render( 文档内容[string] )
  - return string 转换的 html
- 判断变量是否有值
  - empty( 变量[mixed] )
  - return boolean 是否有值
- 判断变量是否为 JSON
  - is_json( 变量[mixed] )
  - return boolean 是否为 JSON
- 判断变量是否为数组或者对象
  - is_array( 变量[mixed] )
  - return boolean 判断结果
- 查询本地存储
  - get( 键名[string] )
  - return mixed 查询结果
- 设置本地存储
  - set( 键名[string], 值[mixed] )
  - return boolean 设置结果
- 删除本地存储
  - del( 键名[string] )
  - return boolean 删除结果
- 判断变量是否为数字
  - is_number( 变量[mixed] )
  - return boolean 判断结果
- 生成 UUID
  - uuid()
  - return string uuid
- 判断变量是否为 UUID
  - is_number( 变量[mixed] )
  - return boolean 判断结果
- 时间补全
  - completionTime( 原始参数[string] )
  - return string 补全后的内容
- 完整日期时间补全
  - completionDatetime( 原始参数[string] )
  - return string 补全后的内容
- SHA256 加密一段内容
  - hash( 原始参数 )
  - return string 加密后的内容
- 使用语言包
  - t( 语言键[array|string], 替换参数[array]|{} )
  - return string 语言包结果