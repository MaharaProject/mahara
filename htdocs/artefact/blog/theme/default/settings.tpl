{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{str section="artefact.blog" tag="blogsettings"}</h2>

                    <div style="margin-top: 1em;">
                    <span class="viewicon">
                        <a href="{$WWWROOT}artefact/blog/view/?id={$blog->get('id')}">{str section="artefact.blog" tag="viewblog"}</a>
                    </span>
                </div>
            {$editform}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
