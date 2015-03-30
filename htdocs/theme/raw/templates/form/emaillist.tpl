{{if $disabled}}
  {{foreach from=$validated item=email}}
    <div class="validated">
        <label for="{{$form}}_{{$name}}">
            <span class="accessible-hidden">{{$title}}: </span>
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
<script type="application/javascript">
    var {{$name}}_newrefinput = null;
    var {{$name}}_newref = null;

    function {{$name}}_addedemail() {
        removeElement({{$name}}_newrefinput);
        removeElement({{$name}}_newref);
        var newEmail = {{$name}}_newrefinput.value;
        if (typeof(newEmail) == 'string' && newEmail.length > 0) {
            if (newEmail.length > 255) {
                alert(get_string('emailtoolong'));
            }
            else {
                var email = {{$name}}_newrefinput.value;
                appendChildNodes('{{$name}}_list', DIV({'class': 'unsent'},
                    INPUT({'type': 'hidden', 'name': '{{$name}}_unsent[]'       , 'value': email}),
                    ' ',
                    email,
                    A({'href': '', 'onclick': '{{$name}}_remove(this); return false'}, IMG({'class':'inline-button', 'alt': '{{str tag=delete}}', 'src':'{{theme_image_url filename="btn_deleteremove"}}'})),
                    ' ' + {{$validationemailstr|safe}}
                ));
                if (typeof formchangemanager !== 'undefined') {
                    var form = jQuery(this).closest('form')[0];
                    formchangemanager.setFormState(form, FORM_CHANGED);
                }
            }
        }
        {{$name}}_newrefinput = null;
        {{$name}}_newref = null;
    }

    function {{$name}}_new() {
        if ( {{$name}}_newref ) {
            {{$name}}_newrefinput.focus();
            return false;
        }

        {{$name}}_newrefinput = INPUT({'type': 'text', 'id': 'addnew{{$name}}'});
        {{$name}}_newrefsubmit = INPUT({'type': 'submit', 'class': 'btn', 'value': '{{$addbuttonstr}}'});
        {{$name}}_newref = DIV(null,{{$name}}_newrefinput,' ',{{$name}}_newrefsubmit);

        appendChildNodes('{{$name}}_list', {{$name}}_newref);

        {{$name}}_newrefinput.focus();

        connect({{$name}}_newrefinput, 'onchange', function(k) {
            if (typeof formchangemanager !== 'undefined') {
                var form = jQuery(this).closest('form')[0];
                formchangemanager.setFormState(form, FORM_CHANGED);
            }
        });

        connect({{$name}}_newrefsubmit, 'onclick', function(k) {
            {{$name}}_addedemail();
            k.stop();
        });
    }

    function {{$name}}_remove(x) {
        var div = x.parentNode;

        var radio = filter(
                function(elem) { return getNodeAttribute(elem, 'type') == 'radio'; },
                getElementsByTagAndClassName('input', null, div)
        );

        if (radio[0] && radio[0].checked) {
            alert(get_string('cannotremovedefaultemail'));
            return;
        }

        if (typeof formchangemanager !== 'undefined') {
            var form = jQuery(div).closest('form')[0];
            formchangemanager.setFormState(form, FORM_CHANGED);
        }

        removeElement(x.parentNode);
    }
</script>
<div id="{{$name}}_list">
{{foreach from=$validated key=i item=email}}
    <div class="validated">
        <input{{if $email == $default}} checked{{/if}} type="radio" id="{{$name}}_radio_{{$i}}" name="{{$name}}_selected" value="{{$email}}">
        <input type="hidden" name="{{$name}}_valid[]" value="{{$email}}">
        <label for="{{$name}}_radio_{{$i}}">
            <span class="accessible-hidden">{{$title}}: </span>{{$email}}
        </label>
        <a href="" onclick="{{$name}}_remove(this); return false;"><img class="inline-button"alt="{{str tag=delete}}" src="{{theme_image_url filename="btn_deleteremove"}}" /></a>
    </div>
{{/foreach}}
{{foreach from=$unvalidated item=email}}
    <div class="unvalidated">
        <input type="hidden" name="{{$name}}_invalid[]" value="{{$email}}">
        {{$email}}
        <a href="" onclick="{{$name}}_remove(this); return false;"><img class="inline-button" alt="{{str tag=delete}}" src="{{theme_image_url filename="btn_deleteremove"}}" /></a>
        <span>{{str tag=validationemailsent section=artefact.internal}}</span>
    </div>
{{/foreach}}
</div>
<a href="" onclick="{{$name}}_new(); return false;">{{str tag="addemail"}}</a>
{{/if}}
