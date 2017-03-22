{if $sitedata.weekly}
    <div id="site-stats-graph" class="panel-body site-stats-graph">
    <canvas class="graphcanvas" id="sitestatsgraph"></canvas>
    <script type="application/javascript">
    {literal}
    jQuery(function() {
        fetch_graph_data({'id':'sitestatsgraph','type':'line','graph':'graph_site_data_weekly'});
    });
    {/literal}
    </script>
    </div>
{/if}
  <table class="table">
    <tr>
        <th>{str tag=siteinstalled section=admin}</th>
        <td>{$sitedata.installdate}</td>
    </tr>

    {if $sitedata.users}
    <tr>
        <th>{str tag=users}</th>
        <td>
            {$sitedata.users}
            <small>{str tag=activeusers section=admin}: {$sitedata.usersloggedin}</small>
        </td>
    </tr>
    {/if}
    {if $sitedata.groups}
    <tr>
        <th>{str tag=groups}</th>
        <td>{$sitedata.groups}
            <small>{$sitedata.strgroupmemberaverage}</small>
        </td>
    </tr>
    {/if}
    {if $sitedata.views}
    <tr>
        <th>{str tag=Views section=view}</th>
        <td>
            {$sitedata.views}
            <small>{$sitedata.strviewsperuser}</small>
        </td>
    </tr>
    {/if}
    <tr>
        <th>{str tag=databasesize section=admin}</th>
        <td>{$sitedata.dbsize|display_size}</td>
    </tr>
    {if $sitedata.diskusage}
    <tr>
        <th>{str tag=diskusage section=admin}</th>
        <td>{$sitedata.diskusage|display_size}</td>
    </tr>
    {/if}
    <tr>
        <th>{str tag=maharaversion section=admin}</th>
        <td>{$sitedata.release}{if $sitedata.strlatestversion} ({$sitedata.strlatestversion|clean_html|safe}){/if}</td>
    </tr>
    <tr>
        <th>{str tag=Cron section=admin}</th>
        <td>{if $sitedata.cronrunning}{str tag=runningnormally section=admin}{else}
                {if $sitedata.siteclosedbyadmin}
                    {str tag=cronnotrunningsiteclosed1 section=admin}
                {else}
                    {str tag=cronnotrunning2 section=admin}
                {/if}
            {/if}</td>
    </tr>
  </table>
