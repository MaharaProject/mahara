{include file='header.tpl'}
<p class="lead">{str tag='pluginexplainaddremove'} {str tag='pluginexplainartefactblocktypes'}</p>

<div class="card-items js-masonry extensions" data-masonry-options='{ "itemSelector": ".card" }'>
{foreach from=$plugins key='plugintype' item='plugins'}
    <div class="card">
        <h2 class="card-header">{$plugintypes.$plugintype}
        {if $plugins.configure}
            <div class="btn-group btn-group-top">
                <button class="btn btn-secondary float-start btn-group-item" title="{str tag='configfor'} {$plugintype}" type="button" data-url="plugintypeconfig.php?plugintype={$plugintype}">
                    <span class="icon icon-cog" role="presentation" aria-hidden="true"></span>
                    <span class="accessible-hidden visually-hidden ">{str tag='configfor'} {$plugintype}</span>
                </button>
            </div>
        {/if}
        </h2>
        {assign var="installed" value=$plugins.installed}
        {assign var="notinstalled" value=$plugins.notinstalled}

        {if $notinstalled}
            <ul class="notinstalled list-group plugins-list-group" id="{$plugintype}.notinstalled">
                <li class="list-group-item list-group-item-heading list-group-item-warning">
                    <h3 class="list-group-item-heading">{str tag='notinstalledplugins'}</h3>
                </li>

                {foreach from=$notinstalled key='plugin' item='data'}
                    <li class="list-group-item list-group-item-danger" id="{$plugintype}.{$plugin}">
                        {if $data.name}{$data.name}{else}{$plugin}{/if}
                        {if $data.notinstallable}
                            {str tag='notinstallable'}: {$data.notinstallable|clean_html|safe}
                        {else}
                            <span id="{$plugintype}.{$plugin}.install">(<a href="" onClick="{$installlink}('{$plugintype}.{$plugin}'); return false;">{str tag='install' section='admin'}<span class="accessible-hidden visually-hidden"> {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span></a>)</span>
                        {/if}
                        {if $data.dependencies}
                            {if $data.dependencies.needs}<div class="notes">{$data.dependencies.needs|safe}</div>{/if}
                        {/if}
                        <span id="{$plugintype}.{$plugin}.message"></span>
                    </li>
                {/foreach}
            </ul>
        {/if}


        <ul class="list-group plugins-list-group" id="{$plugintype}.installed">
            <li class="list-group-item list-group-item-heading">
                <h3 class="list-group-item-heading">{str tag='installedplugins'}</h3>
            </li>
            {foreach from=$installed key='plugin' item='data'}
                <li class="list-group-item{if !$data.active} list-group-item-warning{/if}" id="{$plugintype}.{$plugin}">
                    <div class="list-group-item-heading">
                        {if $data.name}{$data.name}{else}{$plugin}{/if}
                        <div class="btn-group btn-group-top">
                        {if $data.info}
                            <button class="first btn btn-secondary btn-sm float-start btn-group-item info-item" data-bs-toggle="modal-docked" data-bs-target="#infomodal" type="button"
                                title="{str tag='infofor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}" data-plugintype='{$plugintype}' data-pluginname='{$plugin}'>
                                 <span class="icon icon-info" role="presentation" aria-hidden="true"></span>
                                 <span class="accessible-hidden visually-hidden ">{str tag='infofor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span>
                            </button>
                        {/if}
                        {if $data.config}
                            <button class="btn btn-secondary btn-sm float-start btn-group-item" title="{str tag='configfor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}" type="button" data-url="pluginconfig.php?plugintype={$plugintype}&amp;pluginname={$plugin}">
                                 <span class="icon icon-cog" role="presentation" aria-hidden="true"></span>
                                 <span class="accessible-hidden visually-hidden ">{str tag='configfor'} {$plugintype} {if $data.name}{$data.name}{else}{$plugin}{/if}</span>
                            </button>
                        {/if}
                        {if $data.activateform}
                            {$data.activateform|safe}
                        {/if}
                        {if $data.types && $data.typeswithconfig == 1}
                        {* place the config button at top of section *}
                        {include file="admin/extensions/pluginstypes.tpl" typedata=$data plugin=$plugin plugintype=$plugintype}
                        {/if}
                        </div>
                        <div class="text-small">{str tag=plugintype section=admin arg1=$plugin}</div>
                        <div class="text-small">{str tag=versionnumber section=admin arg1=$data.version}</div>
                        {if $data.deprecated}{if gettype($data.deprecated) eq 'string'}<div class="alert alert-warning text-small">{$data.deprecated}</div>{else}{str tag=deprecated section=admin}{/if}{/if}
                        {if $data.dependencies}
                            {if $data.dependencies.needs}<div class="notes">{$data.dependencies.needs|safe}</div>{/if}
                            {if $data.dependencies.requires}<div class="danger">{$data.dependencies.requires|safe}</div>{/if}
                        {/if}
                    </div>
                    {if $data.types && $data.typeswithconfig > 1}
                    {* list the plugin types with their config buttons next to name *}
                    {include file="admin/extensions/pluginstypes.tpl" typedata=$data plugin=$plugin plugintype=$plugintype}
                    {/if}
                </li>
            {/foreach}
        </ul>
    </div>
{/foreach}
</div>

<script>
function show_config_info(info) {
    var opts = {
        'plugintype': $(info).data('plugintype'),
        'pluginname': $(info).data('pluginname'),
        'type': $(info).data('type')
    };
    sendjsonrequest(config['wwwroot'] + 'admin/extensions/plugininfo.json.php', opts, 'POST', function(data) {
        $('#infomodal .modal-title').html(data.data.info_header);
        $('#infomodal .modal-body').html(data.data.info_body);
    });
}
var infochosen;
jQuery(function($) {
    $('.info-item').on("click", function(e) {
        e.preventDefault();
        infochosen = e.target;
        if (!$(infochosen).is('A') && !$(infochosen).is('BUTTON')) {
            infochosen = $(infochosen).closest('[data-plugintype]')[0].outerHTML;
        }
        $("#infomodal").modal("show");
    });
    $("#infomodal").on('show.bs.modal', function (e) {
        show_config_info(infochosen);
    });
    $("#infomodal .btn-close").on('click', function () {
        $("#infomodal").modal("hide");
    });
});
</script>

<div class="modal modal-docked modal-docked-right modal-shown closed" id="infomodal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button class="deletebutton btn-close" data-bs-dismiss="modal-docked" aria-label="{str tag=Close}">
                    <span class="times">×</span>
                    <span class="visually-hidden">{str tag=Close}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline modal-docks-title"></h4>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>
{include file='footer.tpl'}
