<div class="modal fade in" id="groupboxwrap" style="display:block;">
    <div class="modal-dialog" >
        <div class="modal-content">
            <div class="modal-header">
                <a href="" onclick="addElementClass('groupbox', 'hidden');return false;" class="close"><span aria-hidden="true">×</span></a>
                <h4 class="modal-title">{str tag=editmembershipforuser section=group arg1=display_name($userid)}</h4>
            </div>

            <div class="modal-body">
            {if !$data}
                <p>{str tag=nogroups section=group}</p>
            {else}

                {foreach from=$data key=addtype item=groups}
                {if $groups}

                    <h5 class="lead mbs">{if $addtype == 'add'}{str tag=addmembers section=group}{else}{str tag=invite section=group}{/if}</h5>
                    <div class="form-group checkbox">
                        {foreach from=$groups item=group}
                            <div>
                                <input id="{$addtype}{$group->id}" type="checkbox" class="checkbox" name="{$addtype}group_{$userid}" value="{$group->id}"{if $group->checked} checked{/if}{if $group->disabled} disabled{/if}>
                                <label for="{$addtype}{$group->id}" class="mll">{$group->name}</label>
                            </div>
                        {/foreach}

                       
                        <a class="btn btn-primary mtl" href="" onclick="changemembership(event, {$userid}, '{$addtype}');">{str tag=applychanges}</a>

                    </div>
                {/if}
                {/foreach}
            {/if}

            </div>

        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->