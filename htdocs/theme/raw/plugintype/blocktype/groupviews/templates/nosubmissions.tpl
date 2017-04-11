{foreach from=$items item=item}
    <li class="list-group-item text-midtone">
        <a href="{$item->url}" class="outer-link">
            <span class="sr-only">{$item->displayname}</span>
        </a>
        <h5 class="text-inline">{$item->displayname}</h5>
    </li>
{/foreach}
