{if $institutiondata.weekly}
    <div id="site-stats-graph">
      <img src="{$institutiondata.weekly}" alt="" />
    </div>
{/if}
  <div class="site-stats-table">
    <div><strong>{str tag=institutioncreated section=admin}:</strong> {$institutiondata.installdate}</div>
    {if $institutiondata.users}
    <div><strong>{str tag=users}:</strong> {$institutiondata.users}<br />
    {str tag=activeusers section=admin}: {$institutiondata.usersloggedin}</div>
    {/if}
    {if $institutiondata.groups}
    <div><strong>{str tag=groups}:</strong> {$institutiondata.groups}</br />
    {$institutiondata.strgroupmemberaverage}</div>
    {/if}
    {if $institutiondata.views}
    <div><strong>{str tag=Views section=view}:</strong> {$institutiondata.views}<br />
    {$institutiondata.strviewsperuser}</div>
    {/if}
    {if $institutiondata.diskusage}
    <div><strong>{str tag=diskusage section=admin}:</strong> {$institutiondata.diskusage|display_size}</div>
    {/if}
  </div>
