<ul class="in-page-tabs edit-view-tabs">
  <li><a{if $selected == 'title'} class="current-tab"{/if} href="{$WWWROOT}view/edit.php?id={$viewid}">{str tag=edittitleanddescription section=view}</a></li>
  <li><a{if $selected == 'content'} class="current-tab"{/if} href="{$WWWROOT}view/blocks.php?id={$viewid}">{str tag=editcontent section=view}</a></li>
</ul>
