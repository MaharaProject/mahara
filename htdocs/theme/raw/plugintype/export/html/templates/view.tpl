{include file="export:html:header.tpl"}

{if $collectionmenu}
<div class="breadcrumbs collection">
   <ul>
     <li class="collectionname">{$collectionname}</li>
{foreach from=$collectionmenu item=item}
     | <li{if $item.id == $viewid} class="selected"{/if}><a href="{$rootpath}views/{$item.url}">{$item.text}</a></li>
{/foreach}
   </ul>
</div>
<div class="cb"></div>
{/if}

<p id="view-description">{$viewdescription|clean_html|safe}</p>
{if $viewinstructions}
    <div>{str tag='instructions' section='view'}</div>
    <div class="viewinstruction-export">
        {$viewinstructions|clean_html|safe}
    </div>
{/if}

{$view|safe}

{if $feedback && $feedback->count}
<div class="viewfooter">
    <table id="feedbacktable" class="feedbacktable fullwidth table">
      <thead><tr><th>{str tag="feedback" section="artefact.comment"}</th></tr></thead>
      <tbody>
        {$feedback->tablerows|safe}
      </tbody>
    </table>
    {$feedback->pagination|safe}
</div>
{/if}

{include file="export:html:footer.tpl"}
