{include file='header.tpl'}

{if $missingextensions}
<div class="row">
    <div class="col-md-9">
        <div class="alert alert-warning">{str section=admin tag=networkingextensionsmissing}
            <ul>
            {foreach from=$missingextensions item=extension}
                <li><a href="http://www.php.net/{$extension}">{$extension}</a></li>
            {/foreach}
            </ul>
        </div>
    </div>
</div>
{else}
<div class="row">
    <div class="col-md-9">
        <p class="lead">{str tag=networkingpagedescription section=admin}</p>
    </div>
    <div class="col-md-9">
        <div class="panel panel-default">
            <div class="panel-body">
                {$networkingform|safe}
            </div>
        </div>
    </div>
{/if}

{include file='footer.tpl'}