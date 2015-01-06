{include file="header.tpl"}
{if $institution}
  {$institutionselector|safe}
{/if}
{if $institution && $institution == 'mahara'}
    <p class="intro">{str tag="adminfilespagedescription2" section="admin" args=$descriptionstrargs}</p>
{else}
  {if $institution}
    <p class="intro">{str tag='institutionfilespagedescription1' section='artefact.file'}</p>
  {else}
    {if $group}
    <p class="intro">{str tag='groupfilespagedescription1' section='artefact.file'}</p>
    {else}
    <p class="intro">{str tag='filespagedescription1' section='artefact.file'}</p>
    {/if}
  {/if}
{/if}
            <div>{$form|safe}</div>
{include file="footer.tpl"}

