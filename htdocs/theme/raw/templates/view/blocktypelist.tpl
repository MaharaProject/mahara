{if $blocktypes}
<div class="blocktype-list">
{foreach from=$blocktypes item=blocktype}
{* TODO at this point we have now $blocktype.singleonly *}
    <div class="blocktype">
        <input type="radio" class="blocktype-radio" name="blocktype" value="{$blocktype.name}">
        <img src="{$blocktype.thumbnail_path}" title="{$blocktype.description}" alt="{$blocktype.description}" width="24" height="24"><span class="blocktypetitle">{$blocktype.title}</span>
    </div>
{/foreach}
</div>
{* The div below is an IE6 fix *}
<div class="cb" style="line-height: 0;">&nbsp;</div>
{else}
<div id="noblocks">{str tag='noblocks' section='view'}</div>
{/if}
