{if $blocktypes}
<ul>
{foreach from=$blocktypes item=blocktype}
    <li>
        <div class="blocktype">
            <img src="{$blocktype.thumbnail_path | escape}" alt="{str tag='preview'}">
            <h3>{$blocktype.title | escape}</h3>
            <p>{$blocktype.description | escape}</p>
            <input type="{if $javascript}hidden{else}radio{/if}" class="blocktype-radio" name="blocktype" value="{$blocktype.name | escape}">
        </div>
    </li>
{/foreach}
<ul>
{else}
<div id="noblocks">{str tag='noblocks' section='view'}</div>
{/if}
