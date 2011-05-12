<div id="collectionnavwrap">
{foreach from=$collection item=chunk name=cchunk}
<div class="{if $dwoo.foreach.cchunk.first}colnav1{else}colnav-extra{/if}">
<ul class="colnav">
  {foreach from=$chunk item=view}
  <li{if $view->view == $viewid} class="selected"{/if}>
      {if $view->view != $viewid}
          <a class="colnav" href="{$WWWROOT}view/view.php?id={$view->view}">{$view->title|str_shorten_text:30:true}</a>
      {else}
          <span>{$view->title|str_shorten_text:30:true}</span>
      {/if}
  </li>
  {/foreach}
  {if $dwoo.foreach.cchunk.first && !$dwoo.foreach.cchunk.last}{$haslots=true}{/if}
</ul>
</div>
{/foreach}

<div id="colnav-showmore-div" class="colnav-showmore"></div>
{if $dwoo.foreach.cchunk.index > 1}
<script>{literal}
function toggleShowmore() {
    forEach (getElementsByTagAndClassName('div', 'colnav-extra', null), partial(toggleElementClass, 'hidden'));

    var elem = document.getElementById('colnav-more-a');
    if (showmore) {
        document.getElementById('colnav-more-a').innerHTML = '«';
    } else {
        document.getElementById('colnav-more-a').innerHTML = '…';
    }

    var links = getElementsByTagAndClassName('a', 'colnav', null);
    if (showmore) {
        for (var index = 0; index < links.length; index ++) {
            links[index].href = links[index].href + '&showmore=1';
        }
    } else {
        for (var index = 0; index < links.length; index ++) {
            links[index].href = links[index].href.replace('&showmore=1', '');
        }
    }
}

addLoadEvent(function() {
    {/literal}{if $haslots}{literal}
        var a = document.createElement('a');
        a.setAttribute('id', 'colnav-more-a');
        a.setAttribute('href', '');
        a.appendChild(document.createTextNode('«'));
        var li = document.createElement('li');
        li.setAttribute('id', 'colnav-more');
        li.setAttribute('class', 'nojs-hidden');
        li.appendChild(a);
        var ul = document.createElement('ul');
        ul.setAttribute('class', 'colnav');
        ul.appendChild(li);
        var div = document.getElementById('colnav-showmore-div');
        div.appendChild(ul);
    {/literal}{/if}{literal}
    if (!showmore) {
        toggleShowmore();
    } else {
        var links = getElementsByTagAndClassName('a', 'colnav', null);
        for (var index = 0; index < links.length; index ++) {
            links[index].href = links[index].href + '&showmore=1';
        }
    }
    connect('colnav-more', 'onclick', function(e) {
        e.stop();
        showmore = !showmore;
        toggleShowmore();
        return false;
    });
});{/literal}
</script>
{/if}
	<div class="cb"></div>
</div>

