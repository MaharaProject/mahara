{foreach from=$items item=item}
    <li class="list-group-item flush">
        <h4 class="list-group-item-heading"><a href="{$item->url}">{$item->displayname}</a></h4>
    </li>
{/foreach}
