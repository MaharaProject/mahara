{include file="header.tpl"}
		<div id="myblogs rel">
            <div class="rbuttons">
                <a class="btn-add" href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
            </div>
            <div id="bloglist">
            {$blogs->html}
            {*include file="artefact:blog:bloglist.tpl" blogs=$blogs*}
            </div>
            {$blogs->pagination.html}
{include file="footer.tpl"}
