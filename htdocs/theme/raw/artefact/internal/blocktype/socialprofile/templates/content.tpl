<p>
{foreach from=$profiles item=p}
    {if $p->link}<a href="{$p->link}" title="{$p->link}" class="btn socialbtn" target="_blank">
        {if $showicon}<img src="{$p->icon}" alt="{$p->link}" class="valign-top">{/if}
        {if $showicon && $showtext}&nbsp;{/if}
        {if $showtext}{$p->description}{/if}
    </a>{/if}
{/foreach}
{if $email}
    <a href="mailto:{$email}" title="{$email}" class="btn socialbtn" target="_blank">
        {if $showicon}<img src="{$WWWROOT}artefact/internal/blocktype/socialprofile/theme/raw/static/images/email.png" alt="{$email}" class="valign-top">{/if}
        {if $showicon && $showtext}&nbsp;{/if}
        {if $showtext}{str tag='email'}{/if}
    </a>
{/if}
</p>
