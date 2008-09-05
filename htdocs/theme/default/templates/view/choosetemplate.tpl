{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                        <h2>{$heading}</h2>
<div id="copyview">

 <div id="viewownersearch" class="searchlist">
  <h3>{str tag="searchviewsbyowner" section="view"}</h3>
  <form class="searchquery" action="{$WWWROOT}view/choosetemplate.php" method="post">
    <label>{str tag="searchowners" section="view"}:
      <input type="text" name="ownerquery" id="ownerquery" class="query" value="{$owners->query|escape}">
    </label>
    <button class="query-button" type="submit">{str tag="go"}</button>
    <input type="hidden" name="ownerlimit" value="{$owners->limit|escape}">
    <input type="hidden" name="owneroffset" value="0">
  </form>
  <div id="viewownersearch_table">{$owners->html}</div>
  <div id="viewownersearch_pagination">{$owners->pagination.html}</div>
 </div>

 <div id="templatesearch" class="searchlist">
  <h3>{str tag="selectaviewtocopy" section="view"}</h3>
  <form class="searchquery" action="{$WWWROOT}view/choosetemplate.php" method="post">
    <label>{str tag="searchviews" section="view"}:
      <input type="text" name="viewquery" id="viewquery" class="query" value="{$views->query|escape}">
    </label>
    <button class="query-button" type="submit">{str tag="go"}</button>
    <input type="hidden" name="viewlimit" value="{$views->limit|escape}">
    <input type="hidden" name="viewoffset" value="0">
  </form>
  <div id="templatesearch_table">{$views->html}</div>
  <div id="templatesearch_pagination">{$views->pagination.html}</div>
 </div>

</div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
