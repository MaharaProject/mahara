{{if $disabled}}
  {{foreach from=$validated item=email}}
    <div class="validated">
        <label for="{{$form}}_{{$name}}">
            <span class="accessible-hidden sr-only">{{$title}}: </span>
        </label>
        <input disabled {{if $email == $default}} checked{{/if}}{{if $describedby}} aria-describedby="{{$describedby}}"{{/if}} type="radio" name="{{$name}}_locked" value="{{$email}}" id="{{$form}}_{{$name}}">
        {{$email}}
    </div>
    <input type="hidden" name="{{$name}}_valid[]" value="{{$email}}">
    {{if $email == $default}}<input type="hidden" name="{{$name}}_selected" value="{{$email}}">{{/if}}
  {{/foreach}}
  {{foreach from=$unvalidated item=email}}
    <div class="unvalidated">{{$email}}</div>
    <input type="hidden" name="{{$name}}_invalid[]" value="{{$email}}">
  {{/foreach}}
{{else}}
<script>
    var {{$name}}_newrefinput = null;
    var {{$name}}_newref = null;

    function {{$name}}_addedemail() {
        // Are jQuery objects here so can deal with them directly
        var newEmail = {{$name}}_newrefinput.val();

        if (typeof(newEmail) == 'string' && newEmail.length > 0) {
            if (newEmail.length > 255) {
                alert(get_string('emailtoolong'));
            }
            else if (newEmail.indexOf("@") == -1 || newEmail.indexOf(".") == -1) {
                alert(get_string('emailinvalid'));
            }
            else {
                var email = newEmail;
                jQuery('#{{$name}}_list').append(jQuery('<div>', {'class': 'unsent'}).append(
                    jQuery('<input>', {'type': 'radio', 'class': 'text-inline', 'disabled':'true'}),
                    jQuery('<input>', {'type': 'hidden', 'name': '{{$name}}_unsent[]'       , 'value': email}),
                    ' ',
                    jQuery('<span>', {'class': 'pseudolabel'}).append(email),' ',
                    jQuery('<button>', {'class': 'btn btn-secondary btn-sm float-right', 'onclick': '{{$name}}_remove(this); return false'}).append(
                      jQuery('<span>', {'class': 'icon icon-trash icon-lg text-danger', 'role': 'presentation'})
                    ),
                    jQuery('<div>', {'class': 'clearfix description'}).append({{$validationemailstr|safe}})
                ));
                if (typeof formchangemanager !== 'undefined') {
                    var form = jQuery(this).closest('form')[0];
                    formchangemanager.setFormState(form, FORM_CHANGED);
                }
                {{$name}}_newrefinput.remove();
                {{$name}}_newref.remove();
                {{$name}}_newrefinput = null;
                {{$name}}_newref = null;
            }
        }
    }

    function {{$name}}_new() {
        if ( {{$name}}_newref ) {
            {{$name}}_newrefinput.trigger("focus");
            return false;
        }

        {{$name}}_newrefinput = jQuery('<input>', {'type': 'text', 'id': 'addnew{{$name}}', 'class': 'form-control'});
        {{$name}}_newrefsubmit = jQuery('<input>', {'type': 'submit', 'class': 'btn btn-secondary input-group-append', 'value': '{{$addbuttonstr}}'});
        {{$name}}_newref = jQuery('<div>', {'class': 'input-group'}).append({{$name}}_newrefinput,' ',{{$name}}_newrefsubmit);

        jQuery('#{{$name}}_list').append({{$name}}_newref);

        {{$name}}_newrefinput.trigger("focus");

        {{$name}}_newrefinput.on('change', function(k) {
            if (typeof formchangemanager !== 'undefined') {
                var form = jQuery(this).closest('form')[0];
                formchangemanager.setFormState(form, FORM_CHANGED);
            }
        });

        {{$name}}_newrefsubmit.on('click', function(k) {
            {{$name}}_addedemail();
            k.preventDefault();
        });
    }

    function {{$name}}_remove(x) {
        var delbtn = jQuery(x);
        var div = delbtn.closest('div');
        var radio = div.find('[type=radio]');

        if (radio.length && radio.is(':checked')) {
            alert(get_string('cannotremovedefaultemail'));
            return;
        }

        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery(div).closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }

        div.remove();
    }
</script>
<div id="{{$name}}_list" class="{{$name}}-list email-list">
{{foreach from=$validated key=i item=email}}
    <div class="validated">
        <input{{if $email == $default}} checked{{/if}} type="radio" id="{{$name}}_radio_{{$i}}" name="{{$name}}_selected" value="{{$email}}" class="text-inline">
        <input type="hidden" name="{{$name}}_valid[]" value="{{$email}}">
        <label for="{{$name}}_radio_{{$i}}" class="stacked-label">
            <span class="accessible-hidden sr-only">{{$title}}: </span>{{$email}}
        </label>
        {{if $email != $default}}
        <button class="btn btn-secondary btn-sm float-right" onclick="{{$name}}_remove(this); return false;" title="{{str tag=delete}}">
            <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
        </button>
        {{/if}}
    </div>
{{/foreach}}
{{foreach from=$unvalidated item=email}}
    <div class="unvalidated">
        <input type="radio"  class="text-inline" disabled>
        <input type="hidden" name="{{$name}}_invalid[]" value="{{$email}}">
        <span class="stacked-label">
            {{$email}}
        </span>
        <button class="btn btn-secondary btn-sm float-right" onclick="{{$name}}_remove(this); return false;" title="{{str tag=delete}}">
            <span class="icon icon-trash icon-lg text-danger" role="presentation" aria-hidden="true"></span>
        </button>
        <div class="description">{{str tag=validationemailsent section=artefact.internal}}</div>
    </div>
{{/foreach}}
</div>
<button class="btn btn-secondary btn-sm align-with-input" onclick="{{$name}}_new(); return false;">
    <span class="icon icon-plus left text-success icon-lg" role="presentation" aria-hidden="true"> </span>
    {{str tag="addemail"}}
</button>
{{/if}}
