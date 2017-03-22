{include file="header.tpl"}

{if $collection}
    {include file=collectionnav.tpl}
{/if}

<h1 id="viewh1" class="page-header">
    <span class="section-heading">{$name}</span>
</h1>
<div class="with-heading text-small">
    {include file=author.tpl}
</div>

<p>{$description|clean_html|safe}</p>
<p>{str tag="addpages" section="module.framework"}</p>
<table class="fullwidth table tablematrix" id="tablematrix">
  <tr class="table-pager">
    <td colspan="2">&nbsp;</td>
    <td colspan="{$viewcount}" class="special">
        <button class="btn btn-default" id="prev">
            <span class="icon left icon-chevron-left" aria-hidden="true" role="presentation"></span>
            Prev
        </button>
        <button class="btn btn-default next" id="next">
            Next
            <span class="icon right icon-chevron-right" aria-hidden="true" role="presentation"></span>
        </button>
    </td>
  </tr>
  <tr class="pages">
    <th>&nbsp;</th>
    <th>&nbsp;</th>
    {foreach from=$views key=vk item=view}
    <th class="viewtab"><a href="{$view->fullurl}">{$view->title}</a></th>
    {/foreach}
  </tr>
  {foreach from=$standards key=sk item=standard}
    <tr class="standard">
        <td colspan="{$viewcount + 2}">
            <div class="shortname-container">
                <h3>{$standard->name}</h3>
                <div class="matrixtooltip popover hidden">
                    <h3 class="popover-title">{$standard->name}</h3>
                    <div class="popover-content">
                        {$standard->description|clean_html|safe}
                    </div>
                </div>
            </div>
        </td>
    </tr>
    {if $standard->options}
        {foreach from=$standard->options key=ok item=option}
        {if $option->children}
        <tr class="matrixlevel{$option->level}">
            <td colspan="{$viewcount + 2}" class="code">
                <div class="shortname-container">
                    {for name=foo from=0 to=$option->level step=1}
                        {if $dwoo.for.foo.index != $option->level}
                        <span class="matrixindent"></span>
                        {/if}
                    {/for} {$option->name}
                    <div class="matrixtooltip popover hidden">
                        <h3 class="popover-title">{$option->name}</h3>
                        <div class="popover-content">
                            {$option->description|clean_html|safe}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        {else}
        <tr class="matrixlevel{$option->level}">
            <td class="code">
                <div class="shortname-container">
                    {for name=foo2 from=0 to=$option->level step=1}
                        {if $dwoo.for.foo2.index != $option->level}
                        <span class="matrixindent"></span>
                        {/if}
                    {/for}{$option->shortname}
                    <div class="matrixtooltip popover hidden">
                        <h3 class="popover-title">{$option->name}</h3>
                        <div class="popover-content">
                            {$option->description|clean_html|safe}
                        </div>
                    </div>
                </div>
            </td>
            <td class="completedcount">{if $completed[$option->id]}{$completed[$option->id]}{else}0{/if}</td>
            {foreach from=$views key=vk item=view}
            <td class="mid"><span data-view="{$view->id}" data-option="{$option->id}"
                {if $evidence[$framework][$option->id][$view->id].state}
                class="{$evidence[$framework][$option->id][$view->id].classes}" title="{$evidence[$framework][$option->id][$view->id].title}">
                {else}
                class="icon icon-circle dot {if !$canaddannotation}disabled{/if}">
                {/if}
                </span>
            </td>
            {/foreach}
        </tr>
        {/if}
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