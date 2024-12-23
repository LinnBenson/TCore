<?php
use support\middleware\view;

    echo view::show( view::$project.'/frame', [
        'title' => "{{user.title}}",
        'image' => "/{{project}}/user.jpg"
    ]);
?>
@start['content']

<link href="/{{project}}/user.css?{{v}}" rel="stylesheet" />
<div id="user">
    <div class="avatar card r0" style="
            background-image: url( '/{{project}}/user_texture.png?{{v}}' );
            background-repeat: repeat;
        ">
        <div class="box">
            <div class="left">
                <img class="avatar" src="/storage/avatar/<?=$user->uid?>" alt="UID <?=$user->uid?>" onclick="unit.checkBig( `/storage/avatar/<?=$user->uid?>` )" />
            </div>
            <div class="right">
                <h5 class="nickname more"><?=$user->info['nickname']?></h5>
                <p class="email more"><?=empty( $user->info['slogan'] ) ? '{{user.noSlogan}}' : $user->info['slogan']?></p>
            </div>
            <i class="bi-box-arrow-left block logout" title="{{logout}}" onclick="c.logout()"></i>
        </div>
    </div>
    <div class="card r0" style="
            background-image: url( '/{{project}}/user_texture.png?{{v}}' );
            background-repeat: repeat;
        ">
        <div class="words">
            <div><p>UID</p><p><?=$user->uid?><i class="bi-copy copy" data-clipboard-text='<?=$user->uid?>' style="margin-left: 8px"></i></p></div>
            <div><p>{{item.username}}</p><p><?=$user->info['username']?></p></div>
            <div><p>{{item.email}}</p><p><?=$user->info['email']?></p></div>
            <div><p>{{item.phone}}</p><p><?=$user->info['phone']?></p></div>
            <div><p>{{item.status}}</p><p><?=$user->info['status']?></p></div>
            <div><p>{{item.invite}}</p><p><?=$user->info['invite']?><i class="bi-copy copy" data-clipboard-text='<?=$user->info['invite']?>' style="margin-left: 8px"></i></p></div>
            <div><p>{{item.created_at}}</p><p><?=toTime( $user->info['created_at'] )?></p></div>
        </div>
    </div>
    <div class="card r0 hasTitle">
        <div class="title">
            <h4><i class="bi-person-circle"></i>{{user.control}}</h4>
        </div>
        <ul class="control">
            <li onclick="view.edit()"><i class="bi-pencil-square block icon"></i>{{user.edit}}<i class="bi-chevron-right block right"></i></li>
            <li onclick="view.bind()"><i class="bi-link-45deg block icon"></i>{{user.bind}}<i class="bi-chevron-right block right"></i></li>
            <li onclick="view.safety()"><i class="bi-shield-check block icon"></i>{{user.safety}}<i class="bi-chevron-right block right"></i></li>
            <li class="noBorder" onclick="c.logout()"><i class="bi-box-arrow-left block icon"></i>{{logout}}<i class="bi-chevron-right block right"></i></li>
        </ul>
    </div>
    <div class="edit hidden">
        <?php
            $avatar = file_exists( "storage/media/avatar/{$user->uid}.png" ) ? "/storage/media/avatar/{$user->uid}.png" : null;
            echo view::show( 'system/form', [ 'input' => [
                [ 'type' => 'upload', 'upload' => '/storage/upload/avatar', 'name' => 'avatar', 'title' => '{{item.avatar}}', 'value' => $avatar, 'rule' => [ 'type' => [ 'png', 'jpg', 'jpeg', 'min' => 0, 'max' => 3000000 ] ] ],
                [ 'type' => 'username', 'name' => 'username','title' => '{{item.username}}', 'value' => $user->info['username'], 'rule' => [ 'must' => true, 'min' => 4, 'max' => 12 ] ],
                [ 'type' => 'text', 'name' => 'nickname','title' => '{{item.nickname}}', 'value' => $user->info['nickname'], 'rule' => [ 'must' => true, 'min' => 1, 'max' => 28 ] ],
                [ 'type' => 'text', 'name' => 'slogan','title' => '{{item.slogan}}', 'value' => $user->info['slogan'], 'rule' => [] ]
            ]]);
        ?>
    </div>
    <div class="bind hidden">
        <?php
            $form = [
                [ 'type' => 'phone', 'name' => 'phone','title' => '{{item.phone}}', 'value' => $user->info['phone'], 'rule' => [] ],
                [ 'type' => 'email', 'name' => 'email','title' => '{{item.email}}', 'value' => $user->info['email'], 'rule' => [ 'must' => true ] ]
            ];
            // 是否启用邮箱验证
            if ( config( 'user.verify.email' ) ) {
                $form[] = [ 'type' => 'number', 'name' => 'verify', 'title' => '{{item.verify}}', 'right' => [ 'icon' => 'bi-send', 'action' => 'view.verify' ], 'rule' => [ 'must' => true ] ];
            }
            echo view::show( 'system/form', [ 'input' => $form ]);
        ?>
    </div>
    <div class="safety hidden">
        <?=view::show( 'system/form', [ 'input' => [
            [ 'type' => 'password', 'name' => 'password','title' => '{{item.password_old}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'password', 'name' => 'password_new1','title' => '{{item.password_new1}}', 'rule' => [ 'must' => true ] ],
            [ 'type' => 'password', 'name' => 'password_new2','title' => '{{item.password_new2}}', 'rule' => [ 'must' => true ] ]
        ]])?>
    </div>
</div>
<script src="/{{project}}/user.js?{{v}}"></script>

@end['content']