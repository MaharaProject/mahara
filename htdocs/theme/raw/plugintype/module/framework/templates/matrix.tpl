{include file="header.tpl"}

{if $collection}
    {include file=collectionnav.tpl}
{/if}

<h1 id="viewh1" class="page-header">
    <span class="section-heading">{$name}</span>
</h1>
<p>{$description}</p>
<p>{str tag="addpages" section="module.framework"}</p>
<table class="fullwidth table tablematrix" id="tablematrix">
  <tr>
    <td colspan="2">&nbsp;</td>
    <td colspan="{$viewcount}" class="special">
        <button class="btn btn-default" id="prev">
            <span class="icon left icon-chevron-left" aria-hidden="true" role="presentation"></span>
            Prev
        </button>
        <button class="btn btn-default next" id="next">
            <span class="icon left icon-chevron-right" aria-hidden="true" role="presentation"></span>
            Next
        </button>
    </td>
  </tr>
  <tr>
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    {foreach from=$views key=vk item=view}
    <th class="viewtab"><a href="{$view->fullurl}">{$view->title}</a></th>
    {/foreach}
  </tr>
  {foreach from=$standards key=sk item=standard}
    <tr class="standard">
        <td colspan="{$viewcount + 2}" title="{$standard->description}">{$standard->shortname} {$standard->name}</td>
    </tr>
    {if $standard->options}
        {foreach from=$standard->options key=ok item=option}
        <tr{if $option->parent} class="sub"{/if}>
            <td class="code"><div>{$option->shortname} <span class="hidden matrixtooltip">{$option->name}<br>{$option->description}</span></div></td>
            <td>{if $completed[$option->id]}{$completed[$option->id]}{else}0{/if}</td>
            {foreach from=$views key=vk item=view}
            <td class="mid">{if $evidence[$framework][$option->id][$view->id].completed}
                <span class="icon icon-circle completed"></span>
                {elseif $evidence[$framework][$option->id][$view->id].partialcomplete}
                <span class="icon icon-adjust partial"></span>
                {elseif $evidence[$framework][$option->id][$view->id].incomplete}
                <span class="icon icon-circle-o incomplete"></span>
                {elseif $evidence[$framework][$option->id][$view->id].begun}
                <span class="icon icon-circle-o begun"></span>
                {else}
                <span>&bull;</span>
                {/if}
            </td>
            {/foreach}
        </tr>
        {/foreach}
    {/if}
  {/foreach}
  <tr>
    <td>{str tag="taskscompleted" section="module.framework"}</td>
    <td>{$totalcompleted}</td>
    <td colspan="{$viewcount}">&nbsp;</td>
  </tr>
</table>
{include file="footer.tpl"}