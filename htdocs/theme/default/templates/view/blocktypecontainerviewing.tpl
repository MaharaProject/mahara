    <div class="blockinstance" id="blockinstance_{$id}">
        {if $title}<div class="blockinstance-header">
            <h4>{if $viewartefacturl}<a href="{$viewartefacturl|escape}">{/if}{$title|escape}{if $viewartefacturl}</a>{/if}</h4>
        </div>{/if}
        <div class="blockinstance-content">
            {$content}
        </div>
    </div>
