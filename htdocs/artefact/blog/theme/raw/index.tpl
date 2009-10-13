{include file="header.tpl"}
		<div id="myblogs rel">
            <div class="rbuttons">
                <a class="btn btn-add" href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
            </div>
{if !$blogs->data}
           <div>{str tag=youhavenoblogs section=artefact.blog}</div>
{else}
           <table id="bloglist" class="tablerenderer fullwidth">
             <thead>
               <tr><th></th><th></th></tr>
             </thead>
             <tbody>
              {$blogs->tablerows}
             </tbody>
           </table>
           {$blogs->pagination}
{/if}
{include file="footer.tpl"}
