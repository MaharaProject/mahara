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
            <td class="mid"><span data-view="{$view->id}" data-option="{$option->id}"
                {if $evidence[$framework][$option->id][$view->id].completed}
                class="icon icon-circle completed">
                {elseif $evidence[$framework][$option->id][$view->id].partialcomplete}
                class="icon icon-adjust partial">
                {elseif $evidence[$framework][$option->id][$view->id].incomplete}
                class="icon icon-circle-o incomplete">
                {elseif $evidence[$framework][$option->id][$view->id].begun}
                class="icon icon-circle-o begun">
                {else}
                >&bull;
                {/if}
                </span>
            </td>
            {/foreach}
        </tr>
        {/foreach}
    {/if}
  {/foreach}
</table>

<div role="dialog" id="configureblock" class="modal modal-shown modal-docked-right modal-docked closed blockinstance configure">
    <div class="modal-dialog modal-lg">
        <div data-height=".modal-body" class="modal-content">
            <div class="modal-header">
                <button name="close_configuration" class="deletebutton close">
                    <span class="times">×</span>
                    <span class="sr-only">Close configuration</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline"></h4>
                <span aria-hidden="true" role="presentation" class="icon icon-cogs icon-2x pull-right"></span>
            </div>
            <div class="modal-body blockinstance-content">
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}