{* nav and beginning of page container for group info pages *}


{if $sectiontabs}
<form id="report" method="post">
    <select id="users" class="d-none" multiple="multiple" name="users[]">
    {foreach from=$users key=id item=item}
    <option selected="selected" value="{$id}">{$id}</option>
    {/foreach}
    </select>
    <ul class="nav nav-pills nav-inpage">
        {foreach from=$sectiontabs item=item}
          <li {if $item.selected} class="active"{/if}>
            <button type="submit" class="btn-link btn{if $item.selected} active{/if}" name="report:{$item.id}" value="{$item.name}" />
              <span class="text">{$item.name}</span>
               <span class="accessible-hidden sr-only">({if $item.selected} {str tag=selected}{/if})</span>
            </button>
          </li>
        {/foreach}
    </ul>
</form>
{else}
<ul class="nav nav-pills nav-inpage">
    {foreach from=$SUBPAGENAV item=item}
        {if $item.url}
        <li class="{if $item.class}{$item.class} {/if}{if $item.selected} current-tab active{/if}">
            <a {if $item.tooltip}title="{$item.tooltip}"{/if} class="{if $item.selected} current-tab{/if}" href="{$WWWROOT}{$item.url}">
                {if $item.iconclass}<span class="{$item.iconclass} left"></span>{/if}
                {$item.title}
                <span class="accessible-hidden sr-only">({str tag=tab}{if $item.selected} {str tag=selected}{/if})</span>
            </a>
        </li>
        {/if}
    {/foreach}
</ul>
{/if}
