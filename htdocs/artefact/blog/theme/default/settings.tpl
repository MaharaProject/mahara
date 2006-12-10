{* 

  This template displays the settings for a user's blog.

 *}{include file="header.tpl"}
{include file="adminmenu.tpl"}

<div class="content">
    <h2>{str section="artefact.blog" tag="blogsettings"}</h2>

    <div>
        <a href="{$WWWROOT}artefact/blog/view/?id={$blog->get('id')}">{str section="artefact.blog" tag="viewblog"}</a>
    </div>

    {$editform}
</div>
