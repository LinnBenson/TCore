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