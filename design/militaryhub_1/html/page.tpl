{* Page template *}

{* The canonical address of the page *}
{$canonical="/{$page->url}" scope=parent}

{if $page->url == '404'}
    {include file='page_404.tpl'}
{else}
    {if $page->url == 'about'}
        {* The page heading *}
        <h1 class="main_section_title"><span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span></h1>
        <div class="block-page about-page">
            {$page->description}
            <div class="main_section_title"><span data-language="project_timeline">{$lang->project_timeline}</span></div>
            <div class="project_timeline_wrap">
                <div class="project_timeline">
                    <div class="row">
                        <div class="period col-xs-3 col-sm-3 col-lg-3">
                            <div class="round_check">{include file="svg.tpl" svgId="checked"}</div>
                            <div class="dashed"></div>
                            <div class="year">
                                <span data-language="year1">{$lang->year1}</span>
                            </div>
                            <div class="timeline_info">
                                <p><span data-language="timeline_info1">{$lang->timeline_info1}</span></p>
                            </div>
                        </div>
                        <div class="period col-xs-3 col-sm-3 col-lg-3">
                            <div class="round_check">{include file="svg.tpl" svgId="checked"}</div>
                            <div class="dashed"></div>
                            <div class="year">
                                <span data-language="year2">{$lang->year2}</span>
                            </div>
                            <div class="timeline_info">
                                <p><span data-language="timeline_info2">{$lang->timeline_info2}</span></p>
                            </div>
                        </div>
                        <div class="period col-xs-3 col-sm-3 col-lg-3">
                            <div class="round_check">{include file="svg.tpl" svgId="checked"}</div>
                            <div class="dashed"></div>
                            <div class="year">
                                <span data-language="year3">{$lang->year3}</span>
                            </div>
                            <div class="timeline_info">
                                <p><span data-language="timeline_info3">{$lang->timeline_info3}</span></p>
                            </div>
                        </div>
                        <div class="period col-xs-3 col-sm-3 col-lg-3">
                            <div class="round_check">{include file="svg.tpl" svgId="checked"}</div>
                            <div class="dashed"></div>
                            <div class="year">
                                <span data-language="year4">{$lang->year4}</span>
                            </div>
                            <div class="timeline_info">
                                <p><span data-language="timeline_info4">{$lang->timeline_info4}</span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {else}
        {* The page heading *}
        <h1 class="main_section_title"><span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span></h1>
        {if $page->url == 'reviews'}
            <div class="block-page">
                {* Comments *}
                <div id="comments">
                    <div class="comments_block_page">
                        <div class="col-sm-7 col-lg-8">
                            <div class="comment-wrapper">
                                {if $comments}
                                    {function name=comments_tree level=0}
                                        {foreach $comments as $comment}
                                            {* Comment anchor *}
                                            <a name="comment_{$comment->id}"></a>
                                            {* Comment list *}
                                            <div class="comment_item{if $level > 0} admin_note{/if}">

                                                <div class="comment_header">
                                                    {* Comment img *}
                                                    <div class="flex_col flex_jc">
                                                        {* Comment name *}
                                                        <span class="comment_author">{$comment->name|escape}</span>
                                                        {* Comment date *}
                                                        <div class="flex-item">
                                                            <span class="comment_date">{$comment->date|date}, {$comment->date|time}</span>
                                                            {if !$comment->approved}
                                                                <span class="approved" data-language="post_comment_status">({$lang->post_comment_status})</span>
                                                            {/if}
                                                        </div>
                                                    </div>
                                                </div>

                                                {* Comment content *}
                                                <div class="comment_content">
                                                    {$comment->text|escape|nl2br}
                                                </div>
                                                {if isset($children[$comment->id])}
                                                    {comments_tree comments=$children[$comment->id] level=$level+1}
                                                {/if}
                                            </div>
                                        {/foreach}
                                    {/function}
                                    {comments_tree comments=$comments}
                                {else}
                                    <div class="no_comments">
                                        {include file="svg.tpl" svgId="custom_comment"}
                                        <span data-language="product_no_comments">{$lang->product_no_comments}</span>
                                    </div>
                                {/if}
                            </div>
                        </div>

                        <div class="col-sm-5 col-lg-4 comments_form">
                            {* Comment form *}
                            <form id="captcha_id" class="comment_form fn_validate_product" method="post">

                                <div class="h3">
                                    <span data-language="product_write_comment">{$lang->product_write_comment}</span>
                                </div>

                                <p><span data-language="product_write_need">{$lang->product_write_need}</span></p>

                                {* Form error messages *}
                                {if $error}
                                    <div class="message_error">
                                        {if $error=='captcha'}
                                            <span data-language="form_error_captcha">{$lang->form_error_captcha}</span>
                                        {elseif $error=='empty_name'}
                                            <span data-language="form_enter_name">{$lang->form_enter_name}</span>
                                        {elseif $error=='empty_comment'}
                                            <span data-language="form_enter_comment">{$lang->form_enter_comment}</span>
                                        {elseif $error=='empty_email'}
                                            <span data-language="form_enter_email">{$lang->form_enter_email}</span>
                                        {/if}
                                    </div>
                                {/if}

                                <input type="hidden" name="user_id" value="{$user_id}">
                            
                                {* User's name *}
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->form_name}*</span>
                                    <input class="form_input placeholder_focus" type="text" name="name"
                                        value="{$comment_name|escape}"/>
                                </div>
                                
                                
                                {* User's email *}
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->form_email}</span>
                                    <input class="form_input placeholder_focus" type="text" name="email"
                                        value="{$comment_email|escape}" data-language="form_email"/>
                                </div>
                                
                            
                                {* User's comment *}
                                <div class="form_group">
                                    <span class="form_placeholder">{$lang->form_enter_comment}*</span>
                                    <textarea class="form_textarea placeholder_focus" rows="3"
                                            name="text">{$comment_text}</textarea>
                                </div>

                                {* Captcha *}
                                {if $settings->captcha_product}
                                    {if $settings->captcha_type == "v3"}
                                        <div class="captcha row" style="display: none;">
                                            <div class="fn_recaptchav3">
                                                <input type="hidden" name="recaptcha_token" value=""
                                                    class="fn_recaptcha_token"/>
                                            </div>
                                        </div>
                                    {elseif $settings->captcha_type == "v2"}
                                        <div class="captcha">
                                            <div id="recaptcha1"></div>
                                        </div>
                                    {elseif $settings->captcha_type == "default"}
                                        {get_captcha var="captcha_product"}
                                        <div class="captcha">
                                            <div class="secret_number">{$captcha_product[0]|escape} + ?
                                                = {$captcha_product[1]|escape}</div>
                                            <span class="form_captcha">
                                                        <input class="form_input input_captcha placeholder_focus" type="text"
                                                            name="captcha_code" value=""/>
                                                        <span class="form_placeholder">{$lang->form_enter_captcha}*</span>
                                                    </span>
                                        </div>
                                    {/if}
                                {/if}
                                <input type="hidden" name="comment" value="1">
                                {* Submit button *}
                                <input class="button btn_black g-recaptcha" type="submit" name="comment"
                                    data-language="form_send"
                                    {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}"
                                    data-badge='bottomleft' data-callback="onSubmit"{/if}
                                    value="{$lang->form_send}"/>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        {/if}
        {* The page content *}
        <div class="block">
            {$page->description}
        </div>
    {/if}
    
{/if}
