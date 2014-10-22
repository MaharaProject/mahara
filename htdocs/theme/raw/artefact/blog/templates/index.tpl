{include file="header.tpl"}
            <div class="rbuttons">
                <a class="btn" href="{$WWWROOT}artefact/blog/new/index.php">{str section="artefact.blog" tag="addblog"}</a>
            </div>
         <div id="myblogs" class="rel">
{if !$blogs->data}
           <div>{str tag=youhavenoblogs section=artefact.blog}</div>
{else}
           <div id="bloglist" class="fullwidth listing">
              {$blogs->tablerows|safe}
           </div>
           {$blogs->pagination|safe}
{/if}
         </div>
{include file="footer.tpl"}
