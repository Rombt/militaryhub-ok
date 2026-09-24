{* The blog page template *}

{* The canonical address of the page *}
{if $type_post == "blog"}
    {$canonical="/blog" scope=parent}
{else}
    {$canonical="/news" scope=parent}
{/if}

{* The page heading *}
<h1 class="main_section_title"><span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span></h1>
{* The list of the blog posts *}
<div class="blog">
    <div class="news_items">
        {foreach $posts as $post}
            <a href="{$lang_link}{$post->type_post}/{$post->url}" data-post="{$post->id}" class="news_item">
                <div class="news_image">
                    {if $post->image}
                        <img class="news_img" src="{$post->image|resize:250:250:false:$config->resized_blog_dir}" alt="{$post->name|escape}" title="{$post->name|escape}"/>
                    {/if}
                </div>
                <div class="news_content">
                    {* News date *}
                    <div class="news_date">{include file="svg.tpl" svgId="date_icon"}<span>{$post->date|date}</span></div>
                    {* News name *}
                    <div class="news_name">
                        <span>{$post->name|escape}</span>
                    </div>
                </div>
            </a>
        {/foreach}
    </div>

    {* Pagination *}
    {include file='pagination.tpl'} 

</div>
