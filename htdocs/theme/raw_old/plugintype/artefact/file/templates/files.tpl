{include file="header.tpl"}
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



    {if $institution && !ADMIN}
      <div class="row">
        <div class='col-md-10'>
    {/if}


   {$form|safe}


    {if $institution  && !ADMIN}
        </div>
      </div>
    {/if}

{include file="footer.tpl"}
