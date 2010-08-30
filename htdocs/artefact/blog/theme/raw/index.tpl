{include file="header.tpl"}
            <div class="rbuttons">
                <a class="btn" href="{$WWWROOT}artefact/blog/new/">{str section="artefact.blog" tag="addblog"}</a>
            </div>
		<div id="myblogs rel">
{if !$blogs->data}
           <div>{str tag=youhavenoblogs section=artefact.blog}</div>
{else}
           <table id="bloglist" class="tablerenderer fullwidth">
             <thead>
               <tr><th></th><th></th></tr>
             </thead>
             <tbody>
              {$blogs->tablerows|safe}
             </tbody>
           </table>
           {$blogs->pagination|safe}
{/if}
                </div>
{include file="footer.tpl"}
