    <div class="blockinstance" id="blockinstance_{$id}">
        {if $title}<div class="blockinstance-header">
            <h3>{if $viewartefacturl}<a href="{$viewartefacturl|escape}" title="{str tag=clickformoreinformation section=view}">{/if}{$title|escape}{if $viewartefacturl}</a>{/if}</h3>
        </div>{/if}
        <div class="blockinstance-content">
            {$content}
        </div>
    </div>
