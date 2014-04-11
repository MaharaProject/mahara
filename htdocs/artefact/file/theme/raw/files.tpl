{include file="header.tpl"}
{if $institution}
  {$institutionselector|safe}
{/if}
{if $institution && $institution == 'mahara'}
    <p class="intro">{str tag="adminfilespagedescription1" section="admin" args=$descriptionstrargs}</p>
{else}
  {if $institution}
    <p class="intro">{str tag='institutionfilespagedescription' section='artefact.file'}</p>
  {else}
    {if $group}
    <p class="intro">{str tag='groupfilespagedescription' section='artefact.file'}</p>
    {else}
    <p class="intro">{str tag='filespagedescription' section='artefact.file'}</p>
    {/if}
  {/if}
{/if}
            <div>{$form|safe}</div>
{include file="footer.tpl"}

