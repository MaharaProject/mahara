{include file="header.tpl"}
{if $GROUP}
    <h2>{$PAGESUBHEADING}</h2>
{/if}

<p>{$helptext|safe}</p>
<div id="copyview" >

 <div id="templatesearch" class="searchlist mtxl">

  <form class="searchquery panel panel-default" action="{$WWWROOT}view/choosetemplate.php" method="post">
    <label for="viewquery" class="panel-heading">{str tag="Search" section="view"}:</label>
    <div id="searchpages" class="panel-body">
      <div class="form-group pts pbs">
      
        <input type="text" name="viewquery" id="viewquery" class="query" value="{$views->query}">
        <button class="query-button btn btn-success" type="submit">{str tag="go"}</button>
      </div>
    </div>

    <input type="hidden" name="viewlimit" value="{$views->limit}">
    <input type="hidden" name="viewoffset" value="0">
    {if $views->group}<input type="hidden" name="group" value="{$views->group}">{/if}
    {if $views->institution}<input type="hidden" name="institution" value="{$views->institution}">{/if}
    {if $views->collection}<input type="hidden" name="searchcollection" value="{$views->collection}">{/if}

  </form>
  <div id="templatesearch_table" class="mtxl panel panel-default">
    {$views->html|safe}
  </div>
  {$views->pagination.html|safe}
 </div>

</div>
{include file="footer.tpl"}
