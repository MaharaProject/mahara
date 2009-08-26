{include file="header.tpl"}

{if empty($results->data)}
           <div>{str tag=youhavenoblogs section=artefact.blog}</div>
{else}
           <h3>{str tag="itemstaggedwith" arg1=$tag|escape}</h3>
           <table id="results" class="tablerenderer fullwidth">
             <thead>
               <tr><th></th><th></th><th></th></tr>
             </thead>
             <tbody>
              {$results->tablerows}
             </tbody>
           </table>
           {$results->pagination}
{/if}

{include file="footer.tpl"}
