{include file="header.tpl"}

{include file="columnfullstart.tpl"}
                        <h2>{$heading}</h2>
<div id="copyview">
 <div class="owners">
  <h3>{str tag="searchviewsbyowner" section="view"}</h3>
  <form action="{$WWWROOT}view/choosetemplate.php" method="post">
    <div class="search">
      <label>{str tag="searchowners" section="view"}:
        <input type="text" name="ownerquery" id="ownerquery" value="{$owners->query|escape}">
      </label>
      <button id="query-button" type="submit">{str tag="go"}</button>
    </div>
  </form>
  <div>
    {$owners->html}
    <div id="viewowner_pagination">{$owners->pagination.html}</div>
    <!--script type="text/javascript">{$owners->pagination.javascript}</script-->
  </div>
 </div>
 <div class="views">
  <h3>{str tag="selectaviewtocopy" section="view"}</h3>
  <form action="{$WWWROOT}view/choosetemplate.php" method="post">
    <div class="search">
      <label>{str tag="searchviews" section="view"}:
        <input type="text" name="viewquery" id="viewquery" value="{$views->query|escape}">
      </label>
      <button id="query-button" type="submit">{str tag="go"}</button>
    </div>
  </form>
  <div>
    {$views->html}
    <div id="viewowner_pagination">{$views->pagination.html}</div>
    <!--script type="text/javascript">{$views->pagination.javascript}</script-->
  </div>
 </div>
</div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
