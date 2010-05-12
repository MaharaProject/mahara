{auto_escape off}
{include file="header.tpl"}
{$helptext}
<div id="copyview">

 <div id="templatesearch" class="searchlist">

  <form class="searchquery" action="{$WWWROOT}view/choosetemplate.php" method="post">

    <label>{str tag="searchviews" section="view"}:</label>
    <input type="text" name="viewquery" id="viewquery" class="query" value="{$views->query|escape}">
    <button class="query-button" type="submit">{str tag="go"}</button>

    <input type="hidden" name="viewlimit" value="{$views->limit|escape}">
    <input type="hidden" name="viewoffset" value="0">
    {if $views->group}<input type="hidden" name="group" value="{$views->group|escape}">{/if}
    {if $views->institution}<input type="hidden" name="institution" value="{$views->institution|escape}">{/if}

    <br>
	<label>{str tag="searchowners" section="view"}:</label>
    <input type="text" name="ownerquery" id="ownerquery" class="query" value="{$owners->query|escape}">
    <button class="query-button" type="submit">{str tag="go"}</button>

  </form>
  <div id="templatesearch_table">{$views->html}</div>
  {$views->pagination.html}
 </div>

</div>
{include file="footer.tpl"}
{/auto_escape}
