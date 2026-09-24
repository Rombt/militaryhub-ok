{* Post page *}

{* The canonical address of the page *}
{if $type_post == "blog"}
    {$canonical="/blog/{$post->url}" scope=parent}
{else}
    {$canonical="/news/{$post->url}" scope=parent}
{/if}

{* The page heading *}
<h1 class="main_section_title post_title">
    <span data-post="{$post->id}">{$post->name|escape}</span>
</h1>

<div class="post_container__wallpaper" style="background: #F4F6F9 url({$post->image|resize:1100:600:false:$config->resized_blog_dir}) no-repeat center center;"></div>

<div class="block">
    {* Post date *}
    <div class="post_date">
        <span>{$post->date|date:"d cFR Y, cD"}</span>
    </div>

    {* Post content *}
    {$post->description}

    <div class="post_comments_wrapper">
        <button class="fn_leave_review leave_review_btn btn_black" data-language="leave_review" type="button">{$lang->leave_review}</button>
        {* Comments *}
        <div id="comments">
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
                                    {* <div class="comment_author-img round_img">
                                        {get_user var=user id=$comment->user_id}
                                        {if $user->image}
                                            <img src="files/user_img/{$user->image}" alt="">
                                        {else}
                                            {include file="svg.tpl" svgId="avatar" width="24" height="24"}
                                        {/if}
                                    </div> *}
                                    <div class="flex_col flex_jc">
                                        {* Comment name *}
                                        <span class="comment_author">{$comment->name|escape}</span>
                                        {* Comment date *}
                                        <div class="flex-item">
                                            <span class="comment_date">{$comment->date|date}, {$comment->date|time}</span>
                                            {if !$comment->approved}
                                                <span class="approved"
                                                        data-language="post_comment_status">({$lang->post_comment_status})</span>
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
                    <div class="no_comments product_no_comments">
                        {include file="svg.tpl" svgId="custom_comment"}
                        <span data-language="product_no_comments">{$lang->product_no_comments}</span>
                    </div>
                {/if}
            </div>
        </div>
    </div>

</div>


<div class="hidden">
    <div id="fn_comment_form">
        {* Comment form *}
        <form id="captcha_id" class="comment_form fn_validate_product" method="post">
            <div class="h3">
                <span data-language="product_write_comment">{$lang->product_write_comment}</span>
            </div>
            {* <p><span data-language="product_write_need">{$lang->product_write_need}</span></p> *}
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
            <div class="comment_form_wrapper">
                <div class="form_groups">
                    <div class="form_group">
                        {* <label><span>{$lang->form_name}*</span></label> *}
                        <input class="form_input placeholder_focus" type="text" name="name" value="{$comment_name|escape}" placeholder="{$lang->form_name}*"/>
                    </div>
                    <div class="form_group">
                        {* <label><span>{$lang->form_email}</span></label> *}
                        <input class="form_input placeholder_focus" type="text" name="email" value="{$comment_email|escape}" data-language="form_email" placeholder="{$lang->form_email}"/>
                    </div>
                </div>
                {* User's comment *}
                <div class="form_group">
                    {* <label><span>{$lang->form_enter_comment}*</span></label> *}
                    <textarea class="form_textarea placeholder_focus" rows="3" name="text" placeholder="{$lang->form_enter_comment}*">{$comment_text}</textarea>
                </div>
            </div>
            <input type="hidden" name="user_id" value="{$user_id}">
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
            <input class="form_button button btn_black g-recaptcha" type="submit" name="comment"
                    data-language="form_send_comment"
                    {if $settings->captcha_type == "invisible"}data-sitekey="{$settings->public_recaptcha_invisible}"
                    data-badge='bottomleft' data-callback="onSubmit"{/if}
                    value="{$lang->form_send_comment}"/>
        </form>
    </div>
</div>