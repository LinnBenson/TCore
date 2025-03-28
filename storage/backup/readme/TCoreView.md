### TCore 视图驱动器

---

#### 视图模板语法
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
  ```

#### 内置变量

> 这些变量的优先级会高于用户传入的共享参数，如果同名，用户传入的参数将会被覆盖

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
  ```

#### Core.js 说明
- 页面加载状态: `tc.complete` boolean
- 语言包: `tc.text` object
- 服务器信息: `tc.server` object
- 用户信息: `tc.user` object
- 屏幕尺寸: `tc.size` object
- 复制对象: `tc.clipboard` object
- 初始化页面
  ```
  tc.SystemDefaultLoading()
  return null
  ```
- 从服务器刷新信息
  ```
  tc.refresh( type:string(类型) )
  return null
  // 类型支持: [ 'text', 'server', 'all' ]
  ```
- 缓存操作
  ```
  tc.cache( name:string(缓存名), value|false:string(缓存值) )
  return boolean:操作结果
  // 允许的缓存: [ 'id', 'lang', 'themeName', 'theme' ]
  ```
- 发起一个网络请求
  ```
  tc.send( data:array(请求数据), load|false:function( 加载函数 ) )
  return async:异步请求实体;
  // data 示例
  const data = {
      url: 链接[string],
      type: 请求方法[string],
      data: 传递参数[json/array],
      async: 是否为异步请求[boolean],
      check: 是否使用内部检查器[boolean],
      run: 请求成功执行方法[function],
      error: 请求失败执行方法[function],
      other: 其它传递给 ajax 的内容[array]
  };
  ```
- 验证 API 数据
  ```
  tc.res( data:array(请求配置), res:any(数据) )
  return any:数据
  // data 示例
  const data = {
      run: 验证成功执行方法[function],
      error: 验证失败执行方法[function]
  };
  ```
- 附加请求头
  ```
  tc.header()
  return array:请求头数据
  ```
- 视图操作
  ```
  tc.view( 'name' )
  // 添加代码 ( code 为 null 时返回组件内代码 )
  .html( code|null:string(代码) )
  // 检查元素是否存在
  .has()
  // 检查类名是否存在
  .hasClass()
  // 激活元素 ( state 为 null 时自动切换，并返回当前元素激活状态 )
  .action( state|null:boolean(状态) )
  // 类名交换
  .replace( params1:string(类名), params2:string(类名) )
  // 添加类名
  .addClass( name:string(类名) )
  // 移除类名
  .removeClass( name:string(类名) )
  // 设置类名
  .setClass( value:string(类名) )
  // 设置样式
  .style( data:array(样式集合) )
  // 设置属性
  .attr( name:string(属性名), value:string(属性值) )
  // 移除元素
  .remove()
  // 查询子元素
  .find( name:string(子元素) )
  // 为元素执行动画
  .animation( name:string(动画名称), time|180:number(动画时长) )
  // 为元素添加加载动画
  .load( state:boolean(状态), timeout|3000:number(自动消失时间) )
  // 渲染列表
  .list( list:array(列表数据), code:string(项目代码) )
  // 组件滚动
  .scroll( type:string(滚动方向), offset|null:number(偏移数值), timeout|180:number(偏移时间) )
  // 事件注册
  .on( type:string(事件类型), method:function(事件) )

  return ohject|链式调用
  ```
- Toast 通知
  ```
  tc.unit.toast( text:string(通知内容), error|false:boolean(错误消息), timeout|3000:number(自动消失时间) )
  return boolean:响应结果
  ```
- 加载动画
  ```
  tc.unit.load( state:boolean(状态), timeout|3000:number(自动消失时间) )
  return boolean:响应结果
  ```
- 弹窗组件
  ```
  tc.unit.popup( title|false:string(标题), body|'':string(主内容) option|{}:array(可选参数) )
  return boolean:响应结果
  // 使用示例
  tc.unit.popup( '这是一个标题', '弹窗内容代码', {
      width: '300px', // 弹窗宽度
      run: () => {} // 确认执行函数
      icon: 'bi-star', // 确认执行图标
      text: '确定', // 确认执行文本
      close: false // 点击确认后是否自动关闭
  });
  ```
- 判断变量是否存在
  ```
  empty( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为 JSON
  ```
  is_json( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为数组或者对象
  ```
  is_array( v:any(检查参数) )
  return boolean:判断结果
  ```
- 查询本地存储
  ```
  get( key:string(键名) )
  return any:查询结果
  ```
- 设置本地存储
  ```
  set( key:string(键名), value:any(值) )
  return boolean:设置结果
  ```
- 删除本地存储
  ```
  del( key:string(键名) )
  return boolean:删除结果
  ```
- 判断变量是否为数字
  ```
  is_number( v:any(检查参数) )
  return boolean:判断结果
  ```
- 生成 UUID
  ```
  uuid( check|false:boolean(检查参数) )
  return string:UUID
  ```
- 判断变量是否为 UUID
  ```
  is_uuid( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为日期
  ```
  is_date( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为时间
  ```
  is_time( v:any(检查参数) )
  return boolean:判断结果
  ```
- 判断变量是否为完整时间
  ```
  is_datetime( v:any(检查参数) )
  return boolean:判断结果
  ```
- 使用语言包
  ```
  t( word:string(语言键), replace|{}:array(替换参数) )
  return string:传出语言
  ```