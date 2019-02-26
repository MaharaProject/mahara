{include file="header.tpl"}
<p>{str tag=behatvariablesdesc section=admin}</p>
{if !$hascore}
<div class="alert alert-warning">{str tag=behatnocore section=admin}</div>
{/if}
{if $data}
    {assign var="prevkey" value=''}
    {foreach from=$data key=k item=v name=data}
        {if $prevkey !== $k}
            <fieldset id="fs_{$dwoo.foreach.data.index}" class="pieform-fieldset collapsible {if $dwoo.foreach.data.last} last{/if}">
                <legend>
                    <h4>
                        <a id="link_{$dwoo.foreach.data.index}" class="collapsed" href="#behatfield-{$dwoo.foreach.data.index}" data-toggle="collapse" aria-expanded="false" aria-controls="#behatfield-{$dwoo.foreach.data.index}">
                            {$k}
                            <span class="icon icon-chevron-down collapse-indicator right float-right"></span>
                        </a>
                    </h4>
                </legend>
                <div id="behatfield-{$dwoo.foreach.data.index}" class="fieldset-body collapse list-group">
        {/if}
        {if $v == 'notused'}
            {str tag="behatstepnotused" section="admin"}
        {else}
            {foreach from=$v key=sk item=sv name=subdata}
                <div id="fs_{$dwoo.foreach.data.index}_{$dwoo.foreach.subdata.index}" class="pieform-fieldset collapsible {if $dwoo.foreach.v.last} last{/if}">
                    <div><a id="link_{$dwoo.foreach.data.index}_{$dwoo.foreach.subdata.index}" class="collapsed" href="#behatfield-{$dwoo.foreach.data.index}-{$dwoo.foreach.subdata.index}" data-toggle="collapse" aria-expanded="false" aria-controls="#behatfield-{$dwoo.foreach.data.index}-{$dwoo.foreach.subdata.index}">{str tag=behatmatchingrows section=admin arg1=count($sv)} {$sk}.feature</a></div>
                    <div id="behatfield-{$dwoo.foreach.data.index}-{$dwoo.foreach.subdata.index}" class="fieldset-body collapse list-group">
                    {foreach $sv key=row item=value}
                        <div>line {$row}: {$value} </div>
                    {/foreach}
                    </div>
                </div>
            {/foreach}
        {/if}
        {if $prevkey !== $k}
                </div>
            </fieldset>
            {$prevkey = $k}
        {/if}

    {/foreach}
{else}
    {str tag=nobehatfeaturefiles section=admin}
{/if}
{include file="footer.tpl"}