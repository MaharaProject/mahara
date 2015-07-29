<div id="collectionnavwrap">
{foreach from=$collection item=chunk name=cchunk}
<div class="{if $dwoo.foreach.cchunk.first}colnav1{else}colnav-extra{/if}">
<ul class="colnav">
  {foreach from=$chunk item=view}
  <li{if $view->view == $viewid} class="selected"{/if}>
      {if $view->view != $viewid}
          <a class="colnav" onclick="{literal}
          var params = {};
          params.id = {/literal}{$view->view}{literal};
          sendjsonrequest('../collection/viewcontent.json.php', params, 'POST', partial(showPreview, 'big'));
          {/literal}" href="{$view->fullurl}">{$view->title|str_shorten_text:30:true}</a>
      {else}
          <span>{$view->title|str_shorten_text:30:true}</span>
      {/if}
  </li>
  {/foreach}
</ul>
</div>
{/foreach}
</div>
