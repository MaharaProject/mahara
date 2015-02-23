{include file="header.tpl"}
{if $institution}
  {$institutionselector|safe}
{/if}

    {if $institution && $institution == 'mahara'}
        <p class="lead">{str tag="adminfilespagedescription2" section="admin" args=$descriptionstrargs}</p>
    {else}
      {if $institution}
        <p class="lead">{str tag='institutionfilespagedescription1' section='artefact.file'}</p>
      {else}
        {if $group}
        <p class="lead">{str tag='groupfilespagedescription1' section='artefact.file'}</p>
        {else}
        <p class="lead">{str tag='filespagedescription1' section='artefact.file'}</p>
        {/if}
      {/if}
    {/if}


    
    
      {$form|safe}

{include file="footer.tpl"}

