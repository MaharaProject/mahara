    <div class="blockinstance cb bt-{$blocktype}" id="blockinstance_{$id}">
        {if $title}<div class="blockinstance-header">
            <h4>{if $viewartefacturl}<a href="{$viewartefacturl}" title="{str tag=clickformoreinformation section=view}">{/if}{$title}{if $viewartefacturl}</a>{/if}{if $feedlink}&nbsp;<a href="{$feedlink}"><img class="feedicon" src="{theme_url filename='images/rss.gif'}"></a>{/if}</h4>
        </div>{/if}
        <div class="blockinstance-content">
            {$content|safe}
        </div>
    </div>
