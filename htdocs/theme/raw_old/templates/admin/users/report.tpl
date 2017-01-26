{include file="header.tpl"}

<div class="btn-group btn-group-top">
  {if $csv}
    <a class="btn btn-default" href="{$WWWROOT}download.php">
    <span class="icon icon-table left" role="presentation" aria-hidden="true"></span>
    {str tag=exportusersascsv section=admin}
    <span class="accessible-hidden sr-only">{str tag=downloadusersascsv section=admin}</span>
    </a>
  {/if}
</div>

<p class="lead">{str tag=userreportsdescription section=admin}</p>

<div class="panel panel-default">
  <h2 class="panel-heading">{str tag=selectedusers section=admin} ({count($users)})</h2>
  {$userlisthtml|safe}
</div>

{include file="footer.tpl"}
