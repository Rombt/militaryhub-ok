{*Название мета*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {if !$seo_comment->id}
                    {$btr->seocomment_add|escape}
                {else}
                    {$seo_comment->url|escape}
                {/if}
            </div>
            {if $seo_comment->id}
                <div class="box_btn_heading">
                    <a class="btn btn_small btn-info add" target="_blank" href="../{$seo_comment->url}">
                        {include file='svg_icon.tpl' svgId='icon_desktop'}
                        <span>{$btr->general_open|escape}</span>
                    </a>
                </div>
            {/if}
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-sm-12 float-xs-right"></div>
</div>

{*Вывод успешных сообщений*}
{if $message_success}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_success">
                <div class="heading_box">
                    {if $message_success == 'added'}
                        {$btr->seocomment_added|escape}
                    {elseif $message_success == 'updated'}
                        {$btr->seocomment_updated|escape}
                    {/if}
                    {if $smarty.get.return}
                        <a class="btn btn_return float-xs-right" href="{$smarty.get.return}">
                            {include file='svg_icon.tpl' svgId='return'}
                            <span>{$btr->general_back|escape}</span>
                        </a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Вывод ошибок*}
{if $message_error}
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="boxed boxed_warning">
                <div class="heading_box">
                    {if $message_error == 'url_exists'}
                        {$btr->general_exists|escape}
                    {elseif $message_error=='empty_name'}
                        {$btr->general_enter_title|escape}
                    {elseif $message_error == 'url_wrong'}
                        {$btr->general_not_underscore|escape}
                    {else}
                        {$message_error|escape}
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}

{*Главная форма страницы*}
<form method="post" enctype="multipart/form-data">
    <input type=hidden name="session_id" value="{$smarty.session.id}">
    <input type="hidden" name="lang_id" value="{$lang_id}" />

    <div class="row">
        <div class="col-xs-12 ">
            <div class="boxed match_matchHeight_true">
                {*Название элемента сайта*}
                <div class="row d_flex">
                    <div class="col-lg-10 col-md-9 col-sm-12">
                        <div class="heading_label">
                            {$btr->seocomments_name|escape}
                        </div>
                        <div class="form-group">
                            <input class="form-control" name="url" type="text" value="{$seo_comment->url|escape}"/>
                            <input name="id" type="hidden" value="{$seo_comment->id|escape}"/>
                        </div>
						<div class="form-group">
							<div class="heading_label" >Body <span id="fn_body_counter"></span></div>
							<textarea name="body" class="form-control main_textarea fn_meta_field">{$seo_comment->body|escape}</textarea>
						</div>
                    </div>
                </div>
				<div class="row">
					<div class="col-lg-12 col-md-12">
						<div class="boxed match fn_toggle_wrap tabs">
							<div class="row">
							   <div class="col-lg-12 col-md-12 mt-1">
									<button type="submit" class="btn btn_small btn_blue float-md-right">
										{include file='svg_icon.tpl' svgId='checked'}
										<span>{$btr->general_apply|escape}</span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>
        </div>
    </div>
</form>