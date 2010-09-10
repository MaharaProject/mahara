{foreach from=$collection item=chunk name=cchunk}
<div class="{if $dwoo.foreach.cchunk.first}colnav1{else}colnav-extra hidden{/if}">
<ul class="colnav">
  {foreach from=$chunk item=view}
  <li{if $view->view == $viewid} class="selected"{/if}>
      {if $view->view != $viewid}
          <a href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title|str_shorten_text:30:true}</a>
      {else}
          <span>{$view->title|str_shorten_text:30:true}</span>
      {/if}
  </li>
  {/foreach}
  {if $dwoo.foreach.cchunk.first && !$dwoo.foreach.cchunk.last}
  <li id="colnav-more" class="nojs-hidden"><a href="">…</a></li>
  {/if}
</ul>
</div>
{/foreach}

{if $dwoo.foreach.cchunk.index > 1}
<script>{literal}
addLoadEvent(function() {
    connect('colnav-more', 'onclick', function(e) {
        e.stop();
        forEach (getElementsByTagAndClassName('div', 'colnav-extra', null), partial(toggleElementClass, 'hidden'));
    });
});{/literal}
</script>
{/if}

