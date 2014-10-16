{include file="header.tpl"}
{if ($group)}
    <p>{str tag=editaccessgrouppagedescription section=view}</p>
{else}
    {if ($institution)}
        {if ($institution == 'mahara')}
            <p>{str tag=editaccesssitepagedescription section=view}</p>
        {else}
            <p>{str tag=editaccessinstitutionpagedescription section=view}</p>
        {/if}
    {else}
        <p>{str tag=editaccesspagedescription5 section=view}</p>
    {/if}
{/if}
<p>{str tag=editsecreturlsintable section=view args=$shareurl}</p>
{$form|safe}
{include file="footer.tpl"}
