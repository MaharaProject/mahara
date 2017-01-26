{include file="header.tpl"}

{if $tags}
    <div class="btn-top-right btn-group btn-group-top">
        <a class="btn btn-default" href="{$WWWROOT}tags.php"><span class="icon icon-lg icon-tag left" role="presentation" aria-hidden="true"></span>{str tag=mytags}</a>
    </div>
    <h2>{str tag=selectatagtoedit}:</h2>
    <div class="mytags">
        <ul class="list-unstyled">
        {foreach from=$tags item=t}
            <li class="text-inline"><a id="tag:{$t->tag|urlencode|safe}" class="tag {if $t->tag == $tag} selected{/if}" href="{$WWWROOT}edittags.php?tag={$t->tag|urlencode|safe}">{$t->tag|str_shorten_text:30}&nbsp;<span class="tagfreq badge">{$t->count}</span></a></li>
        {/foreach}
        </ul>
    </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

{if $tag}
<div class="edittag list-group-item">
    <h3 class="list-group-item-heading">
        <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
        {str tag=edittag arg1=$tagsearchurl arg2=$tag}
    </h3>
    <div class="tag-action-description text-small">{str tag=edittagdescription arg1=$tag}</div>
    {$edittagform|safe}
</div>
<div class="deletetag list-group-item">
    <h3 class="list-group-item-heading">
        <span class="icon icon-trash left text-danger" role="presentation" aria-hidden="true"></span>
        {str tag=deletetag arg1=$tagsearchurl arg2=$tag}
    </h3>
    <div class="tag-action-description text-small">{str tag=deletetagdescription}</div>
    {$deletetagform|safe}
</div>
{/if}

{include file="footer.tpl"}
