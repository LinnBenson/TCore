<div style="padding: 20px; background: rgb( {{--r0}} ); box-sizing: border-box;">
    <h1 style="margin: 0px; font-size: 22px;">{{_app.name}}</h1>
    <p style="margin: 0px; margin-bottom: 16px; font-size: 14px;">{{account.verify}}</p>
    <div style="padding: 20px; margin-bottom: 32px; background: rgb( {{--r6}} ); border-radius: 8px; box-sizing: border-box;">
        <p style="margin: 0px; color: rgb( {{--r1}} )"><?=t('account.verify_email',['code'=>$val['code']])?></p>
    </div>
    <p style="margin: 0px; margin-bottom: 4px; color: rgb( {{--r1}} ); font-size: 14px; text-align: right;">{{_app.name}} Support Team</p>
    <p style="margin: 0px; margin-bottom: 4px; color: rgb( {{--r1}} ); font-size: 14px; text-align: right;"><?=date( 'Y-m-d' )?></p>
</div>