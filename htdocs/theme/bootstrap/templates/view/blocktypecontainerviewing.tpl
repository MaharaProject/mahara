<div class="blockinstance bt-{$blocktype}{if $retractable} retractable{/if} panel panel-default panel-half" id="blockinstance_{$id}">
    <!-- {if $title}<div class="blockinstance-header{if $retractable && $retractedonload} retracted{/if}"> -->
    {if $retractable}
    <span class="arrow retractor"></span>
    {/if}
    <h2 class="title panel-heading"><!-- (Adding some newlines within HTML comments to improve formatting without adding rendered whitespace)
        -->{if $retractable}<span class="retractor">{/if}<!--
        -->{$title}<!--
        -->{if $retractable}</span>{/if}<!--
        -->{if $viewartefacturl} <a href="{$viewartefacturl}" title="{str tag=clickformoreinformation section=view}" class="detail-link"><!--
    --><span class="fa fa-list-alt"></span><span class="{str tag=detailslinkalt section=view}"></span>
            <!--
        --></a>{/if}<!--
        -->{if $feedlink} <a href="{$feedlink}"><!--
            --><img class="feedicon" src="{theme_url filename='images/feed.png'}"><!--
        --></a>{/if}<!--
    --></h2>
    <!-- </div>{/if} -->
    {if $retractable && $retractedonload}
    <div class="blockinstance-content js-hidden">
        {$content|safe}
    </div>
    {else}
    {$content|safe}
    {/if}
</div>
{if $retractable}
<script>
{include file="view/retractablejs.tpl" id=$id}
</script>
{/if}
