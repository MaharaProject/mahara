{include file="header.tpl"}
<div id="column-right">
{include file="adminmenu.tpl"}
</div>

{include file="columnleftstart.tpl"}
            <div class="maincontent">
                <h2>{str section="artefact.blog" tag="blogsettings"}</h2>

                <div class="viewblogbtn">
                    <a href="{$WWWROOT}artefact/blog/view/?id={$blog->get('id')}">{str section="artefact.blog" tag="viewblog"}</a>
                </div>
            {$editform}
</div>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
