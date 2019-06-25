{include file="header.tpl" headertype="matrix"}

<p>{$description|clean_html|safe}</p>
<p>{str tag="addpages" section="module.framework"}</p>
<table class="fullwidth table tablematrix" id="tablematrix">
  <caption class="sr-only">{str tag="tabledesc" section="module.framework"}</caption>
  <tr class="table-pager">
    <td colspan="{$colspan}">&nbsp;</td>
    <td colspan="{$viewcount}" class="special">
        <button class="btn btn-secondary" id="prev">
            <span class="icon left icon-chevron-left" aria-hidden="true" role="presentation"></span>
            Prev
            <span class="sr-only">{str tag="goprevpages" section="module.framework"}</span>
        </button>
        <button class="btn btn-secondary next" id="next">
            Next
            <span class="icon right icon-chevron-right" aria-hidden="true" role="presentation"></span>
            <span class="sr-only">{str tag="gonextpages" section="module.framework"}</span>
        </button>
    </td>
  </tr>
  <tr class="pages">
    <th>
        <span class="sr-only">{str tag="headerelements" section="module.framework"}</span>
        &nbsp;
    </th>
    {if $enabled->readyforassessment}
        <th class="statusheader text-center">
            <span class="{$statusestodisplay->readyforassessment.classes}" title="{$statusestodisplay->readyforassessment.title}"></span>
            <span class="sr-only">{str tag="headerreadyforassessmentcount" section="module.framework"}</span>
            &nbsp;
        </th>
        <th class="smartevidencedash text-center">&nbsp;</th>
    {/if}
    {if $enabled->dontmatch}
        <th class="statusheader text-center">
            <span class="{$statusestodisplay->dontmatch.classes}" title="{$statusestodisplay->dontmatch.title}"></span>
            <span class="sr-only">{str tag="headernotmatchcount" section="module.framework"}</span>
            &nbsp;
        </th>
        <th class="smartevidencedash text-center">&nbsp;</th>
    {/if}
    {if $enabled->partiallycomplete}
        <th class="statusheader text-center">
            <span class="{$statusestodisplay->partiallycomplete.classes}" title="{$statusestodisplay->partiallycomplete.title}"></span>
            <span class="sr-only">{str tag="headerpartiallycompletecount" section="module.framework"}</span>
            &nbsp;
        </th>
        <th class="smartevidencedash text-center">&nbsp;</th>
    {/if}
    {if $enabled->completed}
        <th class="statusheader text-center">
            <span class="{$statusestodisplay->completed.classes}" title="{$statusestodisplay->completed.title}"></span>
            <span class="sr-only">{str tag="headercompletedcount" section="module.framework"}</span>
            &nbsp;
        </th>
    {/if}

    {foreach from=$views key=vk item=view}
    <th class="viewtab" scope="col">
        <span class="sr-only">{str tag="headerpage" section="module.framework"}</span>
        <a href="{$view->fullurl}">{$view->title}</a>
    </th>
    {/foreach}
  </tr>
  {foreach from=$standards key=sk item=standard}
    <tr class="standard{if $standard->settingstate == 'closed'} collapsed{/if}" data-standard="{$standard->id}" data-collection="{$collectionid}"
        data-toggle="collapse" aria-expanded="{if $standard->settingstate == 'closed'}false{else}true{/if}">
        <td colspan="{$viewcount + $colspan}">
            <div class="shortname-container">
                <span class="sr-only">{str tag="standardbegin" section="module.framework"}</span>
                <span class="icon icon-chevron-down collapse-indicator right float-right"></span>
                <h3>{$standard->shortname}</h3>
                <span class="sr-only status">{if $standard->settingstate == 'closed'}{str tag="collapsedsection" section="module.framework"}{/if}</span>
                <a href="#">
                  <span class="sr-only action">
                      {if $standard->settingstate == 'closed'}
                          {str tag="uncollapsesection" arg1="$standard->name" section="module.framework"}
                      {else}
                          {str tag="collapsesection" arg1="$standard->name" section="module.framework"}
                      {/if}
                  </span>
                </a>
                <div class="matrixtooltip popover d-none">
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
        <tr class="matrixlevel{$option->level} examplefor{$standard->id}{if $standard->settingstate == 'closed'} d-none{/if}">
            <td colspan="{$viewcount + $colspan}" class="code">
                <div class="shortname-container">
                    <span class="sr-only">{str tag="headerrow" section="module.framework"}</span>
                    <span class="matrixindent">{$option->shortname}</span>
                    <span class="sr-only">{str tag="showelementdetails" section="module.framework"}</span>
                    <div class="matrixtooltip popover d-none">
                        <h3 class="popover-title">{$option->name}</h3>
                        <div class="popover-content">
                            {$option->description|clean_html|safe}
                        </div>
                    </div>
                </div>
            </td>
        </tr>
        {else}
        <tr class="matrixlevel{$option->level} examplefor{$standard->id}{if $standard->settingstate == 'closed'} d-none{/if}">
            <td class="code">
                <div class="shortname-container" tabindex="0">
                    <span class="sr-only">{str tag="headerrow" section="module.framework"}</span>
                    <span class="matrixindent">{$option->shortname}</span>
                    <span class="sr-only">{str tag="showelementdetails" section="module.framework"}</span>
                    <div class="matrixtooltip popover d-none">
                        <h3 class="popover-title">{$option->name}</h3>
                        <div class="popover-content">
                            {$option->description|clean_html|safe}
                        </div>
                    </div>
                </div>
            </td>
            {if $enabled->readyforassessment}
                <td class="completedcount readyforassessment text-center">
                    <span class="sr-only">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->readyforassessment.title}</span>
                    <span>
                        {if $statuscounts->readyforassessment[$option->id]}{$count = $statuscounts->readyforassessment[$option->id]}{else}{$count = 0}{/if}
                        {$count}
                    </span>
                </td>
                <td class="smartevidencedash text-center">-</td>
            {/if}
            {if $enabled->dontmatch}
                <td class="completedcount dontmatch text-center">
                    <span class="sr-only">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->dontmatch.title}</span>
                    <span>
                        {if $statuscounts->dontmatch[$option->id]}{$count = $statuscounts->dontmatch[$option->id]}{else}{$count = 0}{/if}
                        {$count}
                    </span>
                </td>
                <td class="smartevidencedash text-center">-</td>
            {/if}
            {if $enabled->partiallycomplete}
                <td class="completedcount partiallycomplete text-center">
                    <span class="sr-only">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->partiallycomplete.title}</span>
                    <span>
                        {if $statuscounts->partiallycomplete[$option->id]}{$count = $statuscounts->partiallycomplete[$option->id]}{else}{$count = 0}{/if}
                        {$count}
                    </span>
                </td>
                <td class="smartevidencedash text-center">-</td>
            {/if}
            {if $enabled->completed}
                <td class="completedcount completed text-center">
                    <span class="sr-only">{str tag="assessmenttypecount" section="module.framework"}: {$statusestodisplay->completed.title}</span>
                    <span>
                        {if $statuscounts->completed[$option->id]}{$count = $statuscounts->completed[$option->id]}{else}{$count = 0}{/if}
                        {$count}
                    </span>
                </td>
            {/if}


            {foreach from=$views key=vk item=view}
            <td class="mid">
              <span data-view="{$view->id}" data-option="{$option->id}"
                {if $evidence[$framework][$option->id][$view->id].state}
                    class="{$evidence[$framework][$option->id][$view->id].classes}" title="{$evidence[$framework][$option->id][$view->id].title}">
                    <a href="#"></a></span>
                    <span class="sr-only">{str tag="statusdetail" arg1=$view->title arg2=$evidence[$framework][$option->id][$view->id].title section="module.framework"}</span>
                {else}
                    class="icon icon-circle dot {if !$canaddannotation}disabled{/if}">
                    {if !$canaddannotation}
                        <a href="#"></a></span><span class="sr-only">
                            {str tag="noannotation" arg1="$view->title" arg2="$option->shortname" section="module.framework"}
                        </span>
                    {else}
                        <a href="#"></a></span><span class="sr-only">
                            {str tag="addannotation" arg1="$option->shortname" arg2="$view->title" section="module.framework"}
                        </span>
                    {/if}
                {/if}
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
                <span aria-hidden="true" role="presentation" class="icon icon-cogs icon-2x float-right"></span>
            </div>
            <div class="modal-body blockinstance-content">
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
