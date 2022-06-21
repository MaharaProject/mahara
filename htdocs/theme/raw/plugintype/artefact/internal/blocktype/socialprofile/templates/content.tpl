{if $noitems && $editing}
    <p class="editor-description">{$noitems}</p>
{else}
<div class="card-body flush">
{foreach from=$profiles item=p}
    {if $p->link}<a href="{$p->link}" title="{$p->link}" class="btn btn-secondary btn-sm socialbtn">
        {if $showicon}
            {if $p->icon}
                <img src="{$p->icon}" alt="{$p->link}" class="valign-top">
            {else}
                {$p->faicon|safe}
            {/if}
        {/if}
        {if $showicon && $showtext}&nbsp;{/if}
        {if $showtext}{$p->description}{/if}
    </a>{/if}
{/foreach}
{if $email}
    <a href="mailto:{$email}" title="{$email}" class="btn btn-secondary btn-sm socialbtn">
        {if $showicon}<span class="icon icon-regular icon-envelope" role="presentation" aria-hidden="true"></span>{/if}
        {if $showicon && $showtext}&nbsp;{/if}
        {if $showtext}{str tag='email'}{/if}
    </a>
{/if}
</div>
{/if}
