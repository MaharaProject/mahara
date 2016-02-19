{include file="header.tpl"}
{if $idps}
<p class="lead">{str tag=selectidp section=auth.saml}</p>
{/if}
<div id="idps" class="section panel panel-default">
    <h2 class="panel-heading" id="idpsheading">{str tag="IdPSelection" section=auth.saml}</h2>
    {if $idps}
    <div class="table-responsive">
    <table id="searchidps" class="table table-striped listing">
        <thead>
            <tr>
                {foreach from=$columns key=f item=c}
                <th>
                    {$c.name}
                    {if $c.help}
                        {$c.helplink|safe}
                    {/if}
                    {if $c.headhtml}<div style="font-weight: normal;">{$c.headhtml|safe}</div>{/if}
                </th>
                {/foreach}
            </tr>
        </thead>
        <tbody>
            {$idps|safe}
        </tbody>
    </table>
    </div>
    {else}
        <div class="panel-body">
            <p class="no-idps">{str tag="noidpsfound" section=auth.saml}</p>
        </div>
    {/if}
</div>
{include file="footer.tpl"}
