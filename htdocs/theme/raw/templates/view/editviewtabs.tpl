<a class="fr" id="btn-displaymyview" href="{$displaylink}">{str tag=displaymyview section=view} &raquo;</a>
<ul class="in-page-tabs edit-view-tabs">
  {if $edittitle}<li><a{if $selected == 'title'} class="current-tab"{/if} href="{$WWWROOT}view/edit.php?id={$viewid}">{str tag=edittitleanddescription section=view}</a></li>{/if}
  <li><a{if $selected == 'content'} class="current-tab"{/if} href="{$WWWROOT}view/blocks.php?id={$viewid}">{str tag=editcontent section=view}</a></li>
  <li><a{if $selected == 'layout'} class="current-tab"{/if} href="{$WWWROOT}view/columns.php?id={$viewid}">{str tag=editlayout section=view}</a></li>
</ul>
