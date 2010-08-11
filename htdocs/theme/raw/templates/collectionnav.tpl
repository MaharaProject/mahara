<ul class="colnav">
{foreach from=$collection item=view name=cviews}
  <li{if $view->view == $viewid} class="selected"{/if}><a href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title}</a></li>
  {if !$.foreach.cviews.last}| {/if}
{/foreach}
</ul>

