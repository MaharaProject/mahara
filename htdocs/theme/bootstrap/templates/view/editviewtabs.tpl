
<div class="tabswrap"><ul class="in-page-tabs">
  {if $edittitle}<li {if $selected == 'title'} class="current-tab"{/if}><a{if $selected == 'title'} class="current-tab"{/if} href="{$WWWROOT}view/edit.php?id={$viewid}{if $new}&new=1{/if}">{str tag=edittitleanddescription section=view}<span class="accessible-hidden">({str tag=tab}{if $selected == 'title'} {str tag=selected}{/if})</span></a></li>{/if}
  <li {if $selected == 'layout'} class="current-tab"{/if}><a{if $selected == 'layout'} class="current-tab"{/if} href="{$WWWROOT}view/layout.php?id={$viewid}{if $new}&new=1{/if}">{str tag=editlayout section=view}<span class="accessible-hidden">({str tag=tab}{if $selected == 'layout'} {str tag=selected}{/if})</span></a></li>
  <li {if $selected == 'content'} class="current-tab"{/if}><a{if $selected == 'content'} class="current-tab"{/if} href="{$WWWROOT}view/blocks.php?id={$viewid}{if $new}&new=1{/if}">{str tag=editcontent section=view}<span class="accessible-hidden">({str tag=tab}{if $selected == 'content'} {str tag=selected}{/if})</span></a></li>
  {if !$issitetemplate}
    {if can_use_skins(null, false, $issiteview)}<li {if $selected == 'skin'} class="current-tab"{/if}><a{if $selected == 'skin'} class="current-tab"{/if} href="{$WWWROOT}view/skin.php?id={$viewid}{if $new}&new=1{/if}">{str tag=chooseskin section=skin}<span class="accessible-hidden">({str tag=tab}{if $selected == 'skin'} {str tag=selected}{/if})</span></a></li>{/if}
    <li class="displaypage"><a href="{$displaylink}">{str tag=displayview section=view} &raquo;</a></li>
    {if $edittitle || $viewtype == 'profile'}<li class="sharepage"><a href="{$WWWROOT}view/access.php?id={$viewid}{if $new}&new=1{/if}">{str tag=shareview section=view} &raquo;</a></li>{/if}
  {/if}
</ul></div>
