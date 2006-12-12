{**
 * This smarty template renders a list of a blog's children.
 *}

<ul>
    {foreach from=$children item=child}
        <li>{$child->render(FORMAT_ARTEFACT_LISTSELF, $options)}</li>
    {/foreach}
</ul>
