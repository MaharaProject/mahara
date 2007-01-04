{* 

  This template displays the settings for a user's blog.

 *}{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>
{include file="columnleftstart.tpl"}

<div class="content">
    <h2>{str section="artefact.blog" tag="blogsettings"}</h2>

    <div>
        <a href="{$WWWROOT}artefact/blog/view/?id={$blog->get('id')}">{str section="artefact.blog" tag="viewblog"}</a>
    </div>

    {$editform}
</div>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
