{if !$fromindex}
<div class="statinfoblock panel panel-info">
  <h3>{str tag="Overview" section=statistics}</h3>
{/if}
  <table class="table">
    <tr>
        <th>{str tag=institutioncreated$showall section=admin}</th>
        <td>{$institutiondata.installdate}</td>
    </tr>
    {if $institutiondata.users}
    <tr>
        <th>{str tag=users}</th>
        <td>{$institutiondata.users}
            <small>{str tag=activeusers section=admin}: {$institutiondata.usersloggedin}</small>
        </td>
    </tr>
    {/if}
    {if $institutiondata.groups}
    <tr>
        <th>{str tag=groups}</th>
        <td>{$institutiondata.groups}
            <small>{$institutiondata.strgroupmemberaverage}</small>
        </td>
    {/if}
    {if $institutiondata.views}
    <tr>
        <th>{str tag=Views section=view}</th>
        <td>{$institutiondata.views}
            <small>{$institutiondata.strviewsperuser}</small>
        </td>
    {/if}
    {if $institutiondata.dbsize}
    <tr>
        <th>{str tag=databasesize section=admin}</th>
        <td>{$institutiondata.dbsize|display_size}</td>
    </tr>
    {/if}
    {if $institutiondata.diskusage}
    <tr>
        <th>{str tag=diskusage section=admin}</th>
        <td>{$institutiondata.diskusage|display_size}</td>
    {/if}
    {if $showall}
    <tr>
        <th>{str tag=maharaversion section=admin}</th>
        <td>{$institutiondata.release}{if $institutiondata.strlatestversion} ({$institutiondata.strlatestversion|clean_html|safe}){/if}</td>
    </tr>
    <tr>
        <th>{str tag=Cron section=admin}</th>
        <td>{if $institutiondata.cronrunning}{str tag=runningnormally section=admin}{else}
                {if $institutiondata.siteclosedbyadmin}
                    {str tag=cronnotrunningsiteclosed1 section=admin}
                {else}
                    {str tag=cronnotrunning2 section=admin}
                {/if}
            {/if}
        </td>
    </tr>
    {/if}
  </table>
{if !$fromindex}
</div>
{/if}
{if $institutiondata.usersinfo}
<div class="statinfoblock panel panel-info">
    <h3>{str tag="usersinfo" section=statistics}</h3>
    {$institutiondata.usersinfo|safe}
</div>
{/if}
{if $institutiondata.viewsinfo}
<div class="statinfoblock panel panel-info">
    <h3>{str tag="viewsinfo" section=statistics}</h3>
    {$institutiondata.viewsinfo|safe}
</div>
{/if}
{if $institutiondata.groupinfo}
<div class="statinfoblock panel panel-info">
    <h3>{str tag="groupinfo" section=statistics}</h3>
    {$institutiondata.groupinfo|safe}
</div>
{/if}
{if $institutiondata.weekly}
<div class="statinfoblock panel panel-info">
    <h3>{str tag="weeklytrend" section=statistics}</h3>
    <div class="panel-body">
        <canvas class="graphcanvas" id="sitestatsgraph"></canvas>
        <script type="application/javascript">
        {literal}
        jQuery(function() {
            fetch_graph_data({'id':'sitestatsgraph','type':'line','graph':'graph_institution_data_weekly',
                              'extradata': {'institution': '{/literal}{$institutiondata.institution}{literal}'}
                             });
        });
        {/literal}
        </script>
    </div>
</div>
{/if}
