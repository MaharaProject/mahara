{{*

IMPORTANT: do not introduce any new whitespace into the instanceList div.

*}}
<div id="instanceList" class="listrow">
    {{foreach $instancelist instance}}
    <div class="authInstance" id="instanceDiv{{$instance->id}}">{{strip}}
      <span class="authitem{{if $instance->active == 0}} inactive{{/if}}">
        <label class="authLabel">
            <a href="" onclick="PieformAuthlist.edit_auth({{$instance->id}},'{{$instance->authname}}'); return false;">
            {{str tag="title" section="auth.`$instance->authname`"}}</a> {{if $instance->active == 0}} ({{get_string('off', 'mahara')}}){{/if}}
        </label>
        <span class="authIcons" id="arrows{{$instance->id}}">
            {{if $instance->index + 1 < $instance->total}}
            <a class="btn text-default order-sort-control arrow-down text-midtone" href="" onclick="PieformAuthlist.move_down({{$instance->id}}); return false;">
                <span class="icon icon-long-arrow-alt-down" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=moveitemdown}}</span>
            </a>
            {{/if}}
            {{if $instance->index != 0 }}
            <a class="btn text-default order-sort-control arrow-up text-midtone" href="" onclick="PieformAuthlist.move_up({{$instance->id}}); return false;">
                <span class="icon icon-long-arrow-alt-up" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=moveitemup}}</span>
            </a>
            {{/if}}
            <a href="" class="btn btn-sm" onclick="PieformAuthlist.remove_auth({{$instance->id}}); return false;">
                <span class="icon icon-trash-alt text-danger" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">{{str tag=deleteitem}}</span>
            </a>
        </span>
      </span>{{/strip}}
    </div>
    {{/foreach}}
</div>
<div class="select">
    <span class="picker">
        <select class="select form-control" name="dummy" id="authlistDummySelect">
        {{foreach $authtypes authtype}}
            <option data-requires_config="{{$authtype->requires_config}}" value="{{$authtype->name}}"{{if !$authtype->is_usable}} disabled="disabled"{{/if}}>{{$authtype->title}} - {{$authtype->description}}</option>
        {{/foreach}}
        </select>
    </span>
    <button id="authlistDummyButton" class="btn btn-secondary" type="button" onclick="PieformAuthlist.create_auth(); return false;" name="button" value="foo">{{str tag=Add section=admin}}</button>
</div>

<input type="hidden" id="instancePriority" name="instancePriority" value="{{$instancestring}}" />
<input type="hidden" id="deleteList" name="deleteList" value="" />

<div class="modal modal-shown modal-docked-right modal-docked closed authinstance configure" id="configureauthinstance-modal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" data-height=".modal-body">
            <div class="modal-header">
                <button id="authlist_modal_closer" class="deletebutton close" name="close_configuration">
                    <span class="times">&times;</span>
                    <span class="sr-only">{str tag=cancel section=mahara}</span>
                </button>
                <h1 class="modal-title authinstance-header text-inline"></h1>
                <span class="icon icon-cogs icon-2x pull-right" role="presentation" aria-hidden="true"></span>
            </div>
            <div class="modal-body authinstance-content">
            </div>
        </div>
    </div>
</div>
