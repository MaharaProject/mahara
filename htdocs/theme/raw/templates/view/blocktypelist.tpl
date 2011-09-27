{if $blocktypes}
<ul>
{foreach from=$blocktypes item=blocktype}
{* TODO at this point we have now $blocktype.singleonly *}
    <li title="{$blocktype.title}">
        <div class="blocktype">
            {* The width and height are required so that the javascript that places the clones knows how
               big it should make itself. Talk to Nigel before changing this *}
            <img src="{$blocktype.thumbnail_path}" alt="{str tag='Preview' section='view'}" width="73" height="61">
            <h4 class="blocktype-title js-hidden">{$blocktype.title}</h4>
            <div class="blocktype-description js-hidden">{$blocktype.description}</div>
            <input type="{if $javascript}hidden{else}radio{/if}" class="blocktype-radio" name="blocktype" value="{$blocktype.name}">
        </div>
    </li>
{/foreach}
</ul>
{* The div below is an IE6 fix *}
<div class="cb" style="line-height: 0;">&nbsp;</div>
{else}
<div id="noblocks">{str tag='noblocks' section='view'}</div>
{/if}
