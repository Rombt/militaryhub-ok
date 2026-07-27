{* Title *}
{$meta_title=$btr->users_users scope=parent}

{if $users_count>0}
    <div class="row">
        <progress id="progressbar" class="progress progress-xs progress-info mt-0" style="display: none" value="0" max="100"></progress>
    </div>
{/if}


{*Название страницы*}
<div class="row">
    <div class="col-lg-7 col-md-7">
        <div class="heading_page">
            {$btr->users_users|escape} ({$users_count})
            {if $users_count>0 && !$keyword}
                <div class="export_block export_users hint-bottom-middle-t-info-s-small-mobile  hint-anim" data-hint="{$btr->users_export|escape}">
                    <span class="fn_start_export fa fa-file-excel-o"></span>
                </div>
            {/if}
        </div>
    </div>

    {*Вывод успешных сообщений*}
    {if $message_success}
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'bonuses_added'}
                        {$btr->users_bonuses_added|escape}
                    {elseif $message_success == 'bonuses_deleted'}
                        {$btr->users_bonuses_deleted|escape}
                    {else}
                        {$message_success|escape}
                    {/if}
                </div>
            </div>
        </div>
    {/if}

    <div class="col-md-12 col-lg-5 col-xs-12 float-xs-right">
        <div class="boxed_search">
            <form class="search" method="get">
                <input type="hidden" name="module" value="UsersAdmin">
                <div class="input-group">

                    <input name="keyword" class="form-control" placeholder="{$btr->users_search|escape}" type="text" value="{$keyword|escape}" >

                    <span class="input-group-btn">
                        <button type="submit" class="btn btn_blue"><i class="fa fa-search"></i> <span class="hidden-md-down"></span></button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="boxed fn_toggle_wrap">
    {*Блок фильтров*}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="fn_toggle_wrap">
                <div class="heading_box visible_md">
                    {$btr->general_filter|escape}
                    <div class="toggle_arrow_wrap fn_toggle_card text-primary">
                        <a class="btn-minimize" href="javascript:;" ><i class="fa fn_icon_arrow fa-angle-down"></i></a>
                    </div>
                </div>
                <div class="boxed_sorting action_options toggle_body_wrap off fn_card">
                <div class="row">
                    <div class="col-md-3 col-lg-3 col-sm-12">
                        <select class="selectpicker" onchange="location = this.value;">
                            <option value="{url group_id=null}">{$btr->general_groups|escape}</option>
                            {foreach $groups as $g}
                                <option value="{url group_id=$g->id}" {if $group->id == $g->id}selected{/if}>{$g->name|escape}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
    {if $users}
        {*Главная форма страницы*}
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12">
                <form class="fn_form_list" method="post">
                    <input type="hidden" name="session_id" value="{$smarty.session.id}">

                    <div class="users_wrap main_list products_list fn_sort_list">
                        <div class="main_list_head">
                            <div class="main_list_heading main_list_check">
                                <input class="hidden_check fn_check_all" type="checkbox" id="check_all_1" name="" value=""/>
                                <label class="main_ckeckbox" for="check_all_1"></label>
                            </div>
                            <div class="main_list_heading main_list_users_name">

                                <span>{$btr->general_name|escape}</span>
                                <a href="{url sort=name}" {if $sort == 'name'}class="selected"{/if}>{include file='svg_icon.tpl' svgId='sorts'}</a>

                            </div>
                            <div class="main_list_heading main_list_users_email">
                                <span>Email</span>
                                <a href="{url sort=email}" {if $sort == 'email'}class="selected"{/if}>{include file='svg_icon.tpl' svgId='sorts'}</a>
                            </div>
                            <div class="main_list_heading main_list_users_date">

                                <span>{$btr->general_registration_date|escape}</span>
                                <a href="{url sort=date}" {if $sort == 'date'}class="selected"{/if}>{include file='svg_icon.tpl' svgId='sorts'}</a>
                            </div>
                            <div class="main_list_heading main_list_users_group">{$btr->general_group|escape}</div>
                            <div class="main_list_heading main_list_count">
                                <span>{$btr->users_orders|escape}</span>
                                <a href="{url sort=cnt_order}" {if $sort == 'cnt_order'}}class="selected"{/if}>{include file='svg_icon.tpl' svgId='sorts'}</a>
                            </div>
                            <div class="main_list_heading main_list_count">
                                <span>{$btr->users_bonuses|escape}</span>
                                <a href="{url sort=cnt_bonus}" {if $sort == 'cnt_bonus'}}class="selected"{/if}>{include file='svg_icon.tpl' svgId='sorts'}</a>
                            </div>
                            <div class="main_list_heading main_list_close"></div>
                        </div>
                        {*Параметры элемента*}
                        <div class="main_list_body sortable">
                            {foreach $users as $user}
                                <div class="fn_row main_list_body_item fn_sort_item">
                                    <div class="main_list_row ">
                                        <div class="main_list_boding main_list_check">
                                            <input class="hidden_check" type="checkbox" id="id_{$user->id}" name="check[]" value="{$user->id}"/>
                                            <label class="main_ckeckbox" for="id_{$user->id}"></label>
                                        </div>

                                        <div class="main_list_boding main_list_users_name">
                                            <a href="{url module=UserAdmin id=$user->id}">
                                                {$user->name|escape}
                                            </a>
                                        </div>

                                        <div class="main_list_boding main_list_users_email">
                                            <a href="mailto:{$user->name|escape}<{$user->email|escape}>">
                                                {$user->email|escape}
                                            </a>
                                        </div>

                                        <div class="main_list_boding main_list_users_date">
                                            {$user->created|date} | {$user->created|time}
                                        </div>

                                        <div class="main_list_boding main_list_users_group">
                                            {if $groups[$user->group_id]}
                                                <span>{$groups[$user->group_id]->name|escape}</span>
                                            {/if}
                                        </div>

                                        <div class="main_list_boding main_list_count">
                                            {$user->orders|count}
                                        </div>

                                        <div class="main_list_boding main_list_count">
                                            {$user->bonuses|round:2}
                                        </div>

                                        <div class="main_list_boding main_list_close">
                                            <button data-hint="{$btr->users_delete|escape}" type="button" class="btn_close fn_remove hint-bottom-right-t-info-s-small-mobile  hint-anim" data-toggle="modal" data-target="#fn_action_modal" onclick="success_action($(this));">
                                                {include file='svg_icon.tpl' svgId='delete'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            {/foreach}
                        </div>

                        {*Блок массовых действий*}
                        <div class="main_list_footer fn_action_block">
                            <div class="main_list_foot_left">
                                <div class="main_list_heading main_list_check">
                                    <input class="hidden_check fn_check_all" type="checkbox" id="check_all_2" name="" value=""/>
                                    <label class="main_ckeckbox" for="check_all_2"></label>
                                </div>
                                <div class="main_list_option">
                                    <select name="action" class="selectpicker fn_user_select">
                                        <option value="0">{$btr->general_select_action|escape}</option>
                                        <option value="move_to">{$btr->users_move|escape}</option>
                                        <option value="set_bonuses">{$btr->user_bonuses_add_delete|escape}</option>
                                        <option value="delete">{$btr->general_delete|escape}</option>
                                    </select>
                                </div>
                                <div id="move_to" class="main_list_option hidden fn_hide_block">
                                    <select name="move_group" class="selectpicker">
                                    {if $groups}
                                        {foreach $groups as $group}
                                            <option value="{$group->id}">{$group->name|escape}</option>
                                        {/foreach}
                                    {/if}
                                    </select>
                                </div>
                                <div id="set_bonuses" class="main_list_option hidden fn_hide_block">
                                    <input name="bonuses" class="form-control" placeholder="{$btr->user_bonuses|escape}" type="text" value="" />
                                </div>
                            </div>
                            <button type="submit" class="btn btn_small btn_blue">
                                {include file='svg_icon.tpl' svgId='checked'}
                                <span>{$btr->general_apply|escape}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-12 col-md-12 col-sm 12 txt_center">
                {include file='pagination.tpl'}
            </div>
        </div>
    {else}
        <div class="heading_box mt-1">
            <div class="text_grey">{$btr->users_no|escape}</div>
        </div>
    {/if}
</div>


<script src="{$config->root_url}/backend/design/js/piecon/piecon.js"></script>
<script>
    var group_id='{$group_id|escape}';
    var sort='{$sort|escape}';
</script>

{literal}
<script>
$(function() {

    $(document).on('change','select.fn_user_select',function(){
        var elem = $(this).find('option:selected').val();
        $('.fn_hide_block').addClass('hidden');
        if($('#'+elem).size()>0){
            $('#'+elem).removeClass('hidden');
        }
    });

    // On document load
    $(document).on('click','.fn_start_export',function(){
        Piecon.setOptions({fallback: 'force'});
        Piecon.setProgress(0);
        var progress_item = $("#progressbar"); //указываем селектор элемента с анимацией
        progress_item.show();
        do_export('',progress_item);
    });

    function do_export(page,progress) {
        page = typeof(page) != 'undefined' ? page : 1;
        $.ajax({
            url: "ajax/export_users.php",
            data: {page:page, group_id:group_id, sort:sort},
            dataType: 'json',
            success: function(data){
                if(data && !data.end) {
                    Piecon.setProgress(Math.round(100*data.page/data.totalpages));
                    progress.attr('value',100*data.page/data.totalpages);
                    do_export(data.page*1+1,progress);
                }
                else {
                    Piecon.setProgress(100);
                    progress.attr('value','100');
                    window.location.href = 'files/export_users/users.csv';
                    progress.fadeOut(500);
                }
            },
            error:function(xhr, status, errorThrown) {
                alert(errorThrown+'\n'+xhr.responseText);
            }
        });
    }
});
</script>
{/literal}
