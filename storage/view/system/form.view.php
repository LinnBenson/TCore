<?php
    /**
     * 使用示例：
     *<?=view::show( 'system/form', [
     *    'left' => false,
     *    'input' => [
     *        [ 'type' => 'text', 'title' => '文本', 'name' => 'text', 'icon' => false, 'rule' => [ 'min' => 4, 'max' => 8 ], ],
     *        [ 'type' => 'number', 'title' => '数字', 'name' => 'number', 'right' => [ 'icon' => 'bi-eye', 'action' => 'test' ], 'rule' => [] ],
     *        [ 'type' => 'password', 'title' => '密码', 'name' => 'password', 'rule' => [ 'must' => true ] ],
     *        [ 'type' => 'boolean', 'title' => '开关', 'name' => 'boolean', 'value' => true ],
     *        [ 'type' => 'longtext', 'title' => '长文本', 'name' => 'longtext', 'text' => [ 'a', 'b' ] ],
     *        [ 'type' => 'json', 'title' => 'JSON', 'name' => 'json', 'text' => [ 'a', 'b' ] ],
     *        [ 'type' => 'select', 'title' => '选择', 'name' => 'select', 'data' => [ '1' => 'a', '2' => 'b' ] ],
     *        [ 'type' => 'date', 'title' => '日期', 'name' => 'date', 'value' => '2024-05-05' ],
     *        [ 'type' => 'time', 'title' => '时间', 'name' => 'time', 'value' => '00:00:00' ],
     *        [ 'type' => 'datetime', 'title' => '完整时间', 'name' => 'datetime', 'value' => '2024-05-05 00:00:00' ],
     *        [ 'title' => '手机号', 'name' => 'phone', 'type' => 'phone', 'rule' => [ 'must' => true ] ]
     *    ]
     *])?>
     */
    $icon = [
        'text' => 'bi-pen',
        'username' => 'bi-pen',
        'number' => 'bi-123',
        'email' => 'bi-envelope-open',
        'password' => 'bi-key',
        'select' => 'bi-body-text',
        'date' => 'bi-calendar3',
        'time' => 'bi-clock',
        'time' => 'bi-clock',
        'datetime' => 'calendar-minus'
    ];
    $qv = [ 1, 7, 20, 27, 30, 31, 32, 33, 34, 36, 39, 40, 41, 43, 44, 45, 46, 47, 48, 49,
    51, 52, 53, 54, 55, 56, 57, 58, 60, 61, 62, 63, 64, 65, 66, 81, 82, 84, 86, 90,
    91, 92, 93, 94, 95, 98, 211, 212, 213, 216, 218, 220, 221, 222, 223, 224, 225,
    226, 227, 228, 229, 230, 231, 232, 233, 234, 235, 236, 237, 238, 239, 240, 241,
    242, 243, 244, 245, 246, 247, 248, 249, 250, 251, 252, 253, 254, 255, 256, 257,
    258, 260, 261, 262, 263, 264, 265, 266, 267, 268, 269, 290, 291, 297, 298, 299,
    350, 351, 352, 353, 354, 355, 356, 357, 358, 359, 370, 371, 372, 373, 374, 375,
    376, 377, 378, 379, 380, 381, 382, 383, 385, 386, 387, 389, 420, 421, 423, 500,
    501, 502, 503, 504, 505, 506, 507, 508, 509, 590, 591, 592, 593, 594, 595, 596,
    597, 598, 599, 670, 672, 673, 674, 675, 676, 677, 678, 679, 680, 681, 682, 683,
    685, 686, 687, 688, 689, 690, 691, 692, 850, 852, 853, 855, 856, 870, 880, 886,
    960, 961, 962, 963, 964, 965, 966, 967, 968, 970, 971, 972, 973, 974, 975, 976,
    977, 992, 993, 994, 995, 996, 998 ];
?>
<?php foreach ( $val['input'] as $item ): ?>
    <?php $id = UUID(); ?>
    <div class="formInput <?=isset( $item['must'] ) ? 'must' : ''?> <?=isset( $val['left'] ) ? 'left' : ''?> input_<?=isset( $item['type'] ) ? $w( $item['type'] ) : ''?>" style="--left: <?=!empty( $val['left'] ) ? $w( $val['left'] ) : '140px'?>">
        <?php if ( !empty( $item['title'] ) ): ?>
            <div class="formTitle">
                <p class="more"><?=$w( $item['title'] )?></p>
            </div>
        <?php endif; ?>
        <?php if (
            $item['type'] === 'text' ||
            $item['type'] === 'number' ||
            $item['type'] === 'username'
        ): ?>
            <div class="input <?=$id?>">
                <?php if ( $item['icon'] !== false ): ?>
                    <i class="formIcon <?=$item['icon'] ? $w( $item['icon'] ) : $icon[$item['type']]?> block r3"></i>
                <?php endif; ?>
                <input
                    type='text'
                    name="<?=$w( $item['name'] )?>"
                    class="<?=$item['icon'] !== false ? "hasIcon" : ''?> <?=!empty( $item['right'] ) ? "hasAction" : ''?>"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                    <?=isset( $item['value'] ) ? "value='{$w( $item['value'] )}'" : ''?>
                    autocomplete="off"
                />
                <?php if ( !empty( $item['right'] ) ): ?>
                    <i class="formIcon <?=$w( $item['right']['icon'] )?> action r3 block" onclick="<?=$w( $item['right']['action'] )?>( '<?=$id?>' )"></i>
                <?php endif; ?>
            </div>
        <?php elseif ( $item['type'] === 'email' ): ?>
            <div class="input <?=$id?>">
                <?php if ( $item['icon'] !== false ): ?>
                    <i class="formIcon <?=$item['icon'] ? $w( $item['icon'] ) : $icon[$item['type']]?> block r3"></i>
                <?php endif; ?>
                <input
                    type='<?=$w( $item['type'] )?>'
                    name="<?=$w( $item['name'] )?>"
                    class="<?=$item['icon'] !== false ? "hasIcon" : ''?> <?=!empty( $item['right'] ) ? "hasAction" : ''?>"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                    <?=isset( $item['value'] ) ? "value='{$w( $item['value'] )}'" : ''?>
                    autocomplete="off"
                />
                <?php if ( !empty( $item['right'] ) ): ?>
                    <i class="formIcon <?=$w( $item['right']['icon'] )?> action r3 block" onclick="<?=$w( $item['right']['action'] )?>( '<?=$id?>' )"></i>
                <?php endif; ?>
            </div>
        <?php elseif ( $item['type'] === 'date' || $item['type'] === 'time' ): ?>
            <div class="input <?=$id?>">
                <?php if ( $item['icon'] !== false ): ?>
                    <i class="formIcon <?=$item['icon'] ? $w( $item['icon'] ) : $icon[$item['type']]?> block r3"></i>
                <?php endif; ?>
                <input
                    type='<?=$w( $item['type'] )?>'
                    name="<?=$w( $item['name'] )?>"
                    class="<?=$item['icon'] !== false ? "hasIcon" : ''?> <?=!empty( $item['right'] ) ? "hasAction" : ''?>"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                    <?=isset( $item['value'] ) ? "value='{$w( $item['value'] )}'" : ''?>
                    autocomplete="off"
                    step="1"
                />
                <?php if ( !empty( $item['right'] ) ): ?>
                    <i class="formIcon <?=$w( $item['right']['icon'] )?> action r3 block" onclick="<?=$w( $item['right']['action'] )?>( '<?=$id?>' )"></i>
                <?php endif; ?>
            </div>
        <?php elseif ( $item['type'] === 'password' ): ?>
            <div class="input <?=$id?>">
                <?php if ( $item['icon'] !== false ): ?>
                    <i class="formIcon <?=$item['icon'] ? $w( $item['icon'] ) : $icon[$item['type']]?> block r3"></i>
                <?php endif; ?>
                <input
                    type="password"
                    name="<?=$w( $item['name'] )?>"
                    class="<?=$item['icon'] !== false ? "hasIcon" : ''?> hasAction"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                    <?=isset( $item['value'] ) ? "value='{$w( $item['value'] )}'" : ''?>
                    autocomplete="off"
                />
                <?php if ( !empty( $item['right'] ) ): ?>
                    <i class="formIcon <?=$w( $item['right']['icon'] )?> action r3 block" onclick="<?=$w( $item['right']['action'] )?>( '<?=$id?>' )"></i>
                <?php else: ?>
                    <i class="formIcon bi-eye password action block" onclick="c.showPassword( `div.<?=$id?>` )"></i>
                <?php endif; ?>
            </div>
        <?php elseif ( $item['type'] === 'longtext' ): ?>
            <div class="input <?=$id?>">
                <textarea
                    name="<?=$w( $item['name'] )?>"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                ><?=isset( $item['value'] ) ? $w( $item['value'] ) : ''?></textarea>
            </div>
        <?php elseif ( $item['type'] === 'json' ): ?>
            <div class="input <?=$id?>">
                <textarea
                    class="code"
                    name="<?=$w( $item['name'] )?>"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                ><?=isset( $item['value'] ) ? $w( $item['value'] ) : ''?></textarea>
            </div>
        <?php elseif ( $item['type'] === 'select' ): ?>
            <div class="input <?=$id?>">
                <?php if ( $item['icon'] !== false ): ?>
                    <i class="formIcon <?=$item['icon'] ? $w( $item['icon'] ) : $icon[$item['type']]?> block r3"></i>
                <?php endif; ?>
                <select
                    name="<?=$w( $item['name'] )?>"
                    class="<?=$item['icon'] !== false ? "hasIcon" : ''?> <?=!empty( $item['right'] ) ? "hasAction" : ''?>"
                >
                    <?php if ( is_array( $item['data'] ) ): ?>
                        <?php if ( !empty( $item['hint'] ) ): ?>
                            <option><?=$item['hint']?></option>
                        <?php endif; ?>
                        <?php foreach ( $item['data'] as $key => $value ): ?>
                            <option value="<?=$w( $key )?>" <?=$item['value'] === $key ? 'selected' : ''?>><?=$w( $value )?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if ( !empty( $item['right'] ) ): ?>
                    <i class="formIcon <?=$w( $item['right']['icon'] )?> action r3 block" onclick="<?=$w( $item['right']['action'] )?>( '<?=$id?>' )"></i>
                <?php endif; ?>
            </div>
        <?php elseif ( $item['type'] === 'boolean' ): ?>
            <div class="input <?=$id?>">
                <label class="switch">
                    <input name="<?=$w( $item['name'] )?>" type="checkbox" <?=!empty( $item['value'] ) ? 'checked' : ''?>>
                    <div class="switchBox"><div></div></div>
                </label>
            </div>
        <?php elseif ( $item['type'] === 'datetime' ): ?>
            <div class="input datetime <?=$id?>">
                <input type="date" name="<?=$w( $item['name'] )?>-date" autocomplete="off"
                    value="<?=isset( $item['value'] ) ? explode( " ", $item['value'] )[0] : ''?>"
                />
                <input type="time" name="<?=$w( $item['name'] )?>-time" step="1" autocomplete="off"
                    value="<?=isset( $item['value'] ) ? explode( " ", $item['value'] )[1] : ''?>"
                />
            </div>
        <?php elseif ( $item['type'] === 'phone' ): ?>
            <div class="input phone <?=$id?>">
                <?php $phoneValue = explode( " ", $item['value'] ); ?>
                <select name="qv">
                    <?php foreach ( $qv as $qvItem ): ?>
                        <option value="<?=$qvItem?>" <?=$phoneValue[0] === "+{$qvItem}" ? 'selected' : ''?>>+<?=$qvItem?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="phone" autocomplete="off"
                    <?=isset( $item['hint'] ) ? "placeholder='{$w( $item['hint'] )}'" : ''?>
                    <?=isset( $phoneValue[1] ) ? "value='{$w( $phoneValue[1] )}'" : ''?>
                />
            </div>
        <?php elseif ( $item['type'] === 'upload' ): ?>
            <div class="input upload <?=$id?>">
                <input type='text' name="<?=$w( $item['name'] )?>" style='display: none' <?=isset( $item['value'] ) ? "value='{$w( $item['value'] )}'" : ''?> />
                <label>
                    <input id='<?=$id?>' class="upload_file" type="file" name="<?=$w( $item['name'] )?>" upload="<?=$w( $item['upload'] )?>" style='display: none' />
                    <div class="label center">
                        <div class="hasFile <?=isset( $item['value'] ) ? 'action' : ''?>">
                            <div class="previewImg center" name="<?=$id?>" onclick="event.preventDefault()">
                                <img class="preview" src="" />
                            </div>
                            <i class="bi-x-lg r3" onclick="event.preventDefault(); c.deleteUpload( '<?=$id?>' )"></i>
                            <p>{{form.hasFile}}: <br /><span class="fileName" style="font-size: 12px;"><?=isset( $item['value'] ) ? $item['value'] : ''?></span></p>
                        </div>
                        <div class="upload <?=isset( $item['value'] ) ? '' : 'action'?>">
                            <i class="bi-cloud-upload"></i>
                            <p>{{form.upload}}</p>
                        </div>
                    </div>
                </label>
            </div>
            <script>unit.uploadPreview( "<?=$id?>", "<?=$item['value']?>" )</script>
        <?php elseif ( $item['type'] === 'md' ): ?>
            <div class="input md <?=$id?>">
                <textarea id="markdown_<?=$item['name']?>" name="<?=$w( $item['name'] )?>"></textarea>
                <script>
                    <?php if ( isset( $item['value'] ) && !empty( $item['save'] ) ): ?>
                        del( "markdown_save_<?=$item['save']?>" );
                    <?php endif; ?>
                    val["markdown_<?=$item['name']?>"] = new SimpleMDE({
                        element: document.getElementById( "markdown_<?=$item['name']?>" ),
                        autoDownloadFontAwesome: true,
                        <?php if ( !empty( $item['save'] ) ): ?>
                            autosave: {
                                enabled: true,
                                uniqueId: "markdown_save_<?=$item['save']?>"
                            },
                        <?php endif; ?>
                        indentWithTabs: false,
                        previewRender: function( text ) {
                            return `
                                <div class="markdown">
                                    ${md.to( text )}
                                </div>
                            `;
                        },
                        <?php if ( isset( $item['value'] ) ): ?>
                            initialValue: `${md.edit( <?=$item['value']?> )}`,
                        <?php endif; ?>
                        toolbar: [
                            'heading', 'bold', 'italic', 'quote', 'code', 'unordered-list', 'ordered-list', 'link', {
                                name: "image",
                                action: function customAction( editor ) {
                                    const fileInput = document.createElement('input');
                                    const box = 'div.md.<?=$id?>';
                                    fileInput.type = 'file';
                                    fileInput.accept = 'image/*';
                                    fileInput.style.display = 'none';
                                    fileInput.onchange = function () {
                                        const file = fileInput.files[0];
                                        if ( file ) {
                                            c.viewLoad( box );
                                            const formData = new FormData();
                                            formData.append( 'upload', file );
                                            c.send({
                                                link: '/storage/upload/word',
                                                post: formData,
                                                check: true,
                                                run: function( res ) {
                                                    c.viewLoad( box, true );
                                                    unit.toast( ['true',{type:t('upload')}] );
                                                    const cm = editor.codemirror;
                                                    const doc = cm.getDoc();
                                                    const cursor = doc.getCursor();
                                                    const selectedText = doc.getSelection();
                                                    const replacementText = `![${selectedText}](${res})`;
                                                    doc.replaceSelection(replacementText);
                                                    cm.focus();
                                                },
                                                error: function() {
                                                    c.viewLoad( box, true );
                                                },
                                                other: {
                                                    processData: false,
                                                    contentType: false
                                                }
                                            });
                                        }
                                    };
                                    document.body.appendChild( fileInput );
                                    fileInput.click();
                                    document.body.removeChild( fileInput );
                                },
                                className: "Insert Image fa fa-picture-o",
                                title: "Upload",
                            }, 'horizontal-rule', 'table', 'preview'
                        ]
                    });
                </script>
            </div>
            <script>unit.uploadPreview( "<?=$id?>", "<?=$item['value']?>" )</script>
        <?php endif; ?>
        <?php if ( !empty( $item['text'] ) && is_array( $item['text'] ) ): ?>
            <ul class="text">
                <?php foreach ( $item['text'] as $content ): ?>
                    <li><?=$w( $content )?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <?php if ( is_array( $item['rule'] ) ): ?>
            <script>
                <?php
                    $item['rule']['id'] = $id;
                    $item['rule']['title'] = !empty( $item['title'] ) ? $item['title'] : $item['name'];
                    $item['rule']['type'] = $item['type'];
                ?>
                rule.<?=$w( $item['name'] )?> = JSON.parse( '<?=json_encode( $item['rule'] )?>' );
            </script>
        <?php endif; ?>
    </div>
<?php endforeach; ?>