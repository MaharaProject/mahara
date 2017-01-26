{if $r.logo.url}
<a href="{$WWWROOT}auth/saml/index.php?idpentityid={$r.idpentityid}" title="{$r.description}">
    <img src="{$r.logo.url}" width="{$r.logo.width}" height="{$r.logo.height}" alt="{$r.description}">
</a>
{/if}