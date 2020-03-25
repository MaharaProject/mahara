{foreach from=$items item=item}
    <li class="list-group-item flush">
        <h5 class="list-group-item-heading"><a href="{$item->url}">{$item->displayname}</a></h5>
    </li>
{/foreach}
