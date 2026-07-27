{* Password remind page *}

{* The canonical address of the page *}
{$canonical="/user/password_remind" scope=parent}

{* The page title *}
{$meta_title = $lang->password_remind_title scope=parent}

{* The page heading *}
<h1 class="h1 main_section_title"><span data-language="password_remind_header">{$lang->password_remind_header}</span></h1>

<div class="block clearfix">
    {if $email_sent}
        <div>
            <span data-language="password_remind_on">{$lang->password_remind_on}</span> <b>{$email|escape}</b> <span data-language="password_remind_letter_sent">{$lang->password_remind_letter_sent}.</span>
        </div>
    {else}
        <div class="col-sm-8 col-lg-5 no_padding">
            <form method="post">
                {* Form error messages *}
                {if $error}
                    <div class="message_error">
                        {if $error == 'user_not_found'}
                            <span data-language="password_remind_user_not_found">{$lang->password_remind_user_not_found}</span>
                        {else}
                            {$error|escape}
                        {/if}
                    </div>
                {/if}
                <div class="form_group">
                    {* User's e-mail *}
                    <span class="label_block" data-language="password_remind_enter_your_email">{$lang->password_remind_enter_your_email}</span>
                </div>
                <div class="form_group">
                    <span class="form_placeholder">{$lang->form_email}*</span>
                    <input id="password_remind" class="form_input placeholder_focus" type="text" name="email" value="{$email|escape}" data-language="form_email" >
                </div>
        
                {* Submit button *}
                <input type="submit" class="button btn_black" data-language="password_remind_remember" value="{$lang->password_remind_remember}">
            </form>
        </div>
    {/if}
</div>