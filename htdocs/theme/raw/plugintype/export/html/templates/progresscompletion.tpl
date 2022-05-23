{include file="export:html:header.tpl"}

{if $collectionmenu}
<div class="breadcrumbs collection">
   <ul>
     <li class="collectionname">{$collectionname}</li>
{foreach from=$collectionmenu item=item}
     | <li{if $item.id == 'progresscompletion'} class="selected"{/if}><a href="{$rootpath}{$htmldir}/views/{$item.url}">{$item.text}</a></li>
{/foreach}
   </ul>
</div>
<div class="cb"></div>
{/if}

<div class="card progresscompletion">
    <div class="card-body">
        <p id="quota_message">
            {$quotamessage|safe}
        </p>
        <div id="quotawrap" class="progress">
            <div id="quota_fill" class="progress-bar {if $completedactionspercentage < 11}small-progress{/if}" role="progressbar" aria-valuenow="{if $completedactionspercentage }{$completedactionspercentage}{else}0{/if}" aria-valuemin="0" aria-valuemax="100" style="width: {$completedactionspercentage}%;">
                <span>{$completedactionspercentage}%</span>
            </div>
        </div>
    </div>
</div>

<table class="fullwidth table tablematrix progresscompletion" id="tablematrix">
    <caption class="visually-hidden">{str tag="tabledesc" section="module.framework"}</caption>
    <tr class="table-pager">
        <th>{str tag="view"}</th>
        <th class="userrole">{str tag="signoff" section="blocktype.peerassessment/signoff"}</th>
        <th class="userrole">{str tag="verification" section="collection"}</th>
    </tr>
    {foreach from=$views item=view}
    <tr data-view="{$view->id}">
        <td><div><a href="{$view->fullurl}">{$view->displaytitle}</a></div></td>
        <td><span title="{$view->ownertitle}" class="{$view->ownericonclass}"></span></td>
        <td><span title="{$view->managertitle}" class="{$view->managericonclass}"></span></td>
    </tr>
    {/foreach}
</table>

{include file="export:html:footer.tpl"}
