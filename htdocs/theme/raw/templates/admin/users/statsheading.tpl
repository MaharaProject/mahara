{if $heading.selected}
    <th{if $heading.class} class="{$heading.class}"{/if} id="col_{$heading.id}">
        {if $heading.link}<a href="{$heading.link}">{/if}
        <span>{$heading.name}</span>
        {if $heading.link}<span class="accessible-hidden sr-only">({$heading.sr})</span></a>{/if}
    </th>
{/if}
                   