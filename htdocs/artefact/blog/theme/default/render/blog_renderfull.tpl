{**
 * This smarty template renders the blog in full.  This is currently a complete
 * list of complete blog posts.
 *}

<ul>
    {foreach from=$children item=child}
        <li>{$child->render(FORMAT_ARTEFACT_RENDERFULL, $options)}</li>
    {/foreach}
</ul>
