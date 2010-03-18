    <div class="blockinstance bt-{$blocktype|escape}" id="blockinstance_{$id}">
        {if $title}<div class="blockinstance-header">
            <h4>{if $viewartefacturl}<a href="{$viewartefacturl|escape}" title="{str tag=clickformoreinformation section=view}">{/if}{$title|escape}{if $viewartefacturl}</a>{/if}{if $hasfeed}<a href="{$feedlink}"><img class="feedicon" src="{theme_url filename='images/rss.gif'}"></a>{/if}</h4>
        </div>{/if}
        <div class="blockinstance-content">
            {$content}
        </div>
    </div>
