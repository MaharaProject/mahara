{include file="header.tpl"}
		<div id="myblogs rel">
            <div class="rbuttons">
                <a class="btn-add" href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
            </div>
            <div id="bloglist">
            {$blogs->html}
            </div>
            {if $blogs->data}{$blogs->pagination.html}{/if}
{include file="footer.tpl"}
