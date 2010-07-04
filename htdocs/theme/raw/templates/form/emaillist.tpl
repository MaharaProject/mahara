<script type="text/javascript">
    var {{$name}}_newrefinput = null;
    var {{$name}}_newref = null;

    addLoadEvent(function() {
        connect('{{$name}}_list', 'onkeypress', function (k) {
            if (k.key().code == 13 && {{$name}}_newref) {
                {{$name}}_addedemail();
                k.stop();
            }

            // cancel (esc)
            if (k.key().code == 27 && {{$name}}_newref ) {
                removeElement({{$name}}_newrefinput);
                removeElement({{$name}}_newref);
                {{$name}}_newrefinput = null;
                {{$name}}_newref = null;
                k.stop();
            }
        });
    });

    function {{$name}}_addedemail() {
        removeElement({{$name}}_newrefinput);
        removeElement({{$name}}_newref);
        var newEmail = {{$name}}_newrefinput.value;
        if (typeof(newEmail) == 'string' && newEmail.length > 0) {
            if (newEmail.length > 255) {
                alert(get_string('emailtoolong'));
            }
            else {
                appendChildNodes('{{$name}}_list', DIV({'class': 'unsent'},
                    INPUT({'type': 'hidden', 'name': '{{$name}}_unsent[]'       , 'value': {{$name}}_newrefinput.value}),
                    ' ',
                    {{$name}}_newrefinput.value,
                    A({'href': '', 'onclick': '{{$name}}_remove(this); return false'}, '[x]'),
                    ' ' + {{$validationemailstr|safe}}
                ));
            }
        }
        {{$name}}_newrefinput = null;
        {{$name}}_newref = null;
    }

    function {{$name}}_new() {
        if ( {{$name}}_newref ) {
            return false;
        }

        {{$name}}_newrefinput = INPUT({'type': 'text'});
        {{$name}}_newref = DIV(null,{{$name}}_newrefinput);

        appendChildNodes('{{$name}}_list', {{$name}}_newref);

        {{$name}}_newrefinput.focus();

        connect({{$name}}_newrefinput, 'onblur', function(k) {
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

        removeElement(x.parentNode);
    }
</script>
<div id="{{$name}}_list">
{{foreach from=$validated item=email}}
    <div class="validated">
        <label><input{{if $email == $default}} checked{{/if}} type="radio" name="{{$name}}_selected" value="{{$email}}">
        <input type="hidden" name="{{$name}}_valid[]" value="{{$email}}">
        {{$email}}</label>
        <a href="" onclick="{{$name}}_remove(this); return false;">[x]</a>
    </div>
{{/foreach}}
{{foreach from=$unvalidated item=email}}
    <div class="unvalidated">
        <input type="hidden" name="{{$name}}_invalid[]" value="{{$email}}">
        {{$email}}
        <a href="" onclick="{{$name}}_remove(this); return false;">[x]</a>
        <span>{{str tag=validationemailsent section=artefact.internal}}</span>
    </div>
{{/foreach}}
</div>
<a href="" onclick="{{$name}}_new(); return false;">{{str tag="addemail"}}</a>
