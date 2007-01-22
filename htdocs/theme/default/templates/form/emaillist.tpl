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
            appendChildNodes('{{$name}}_list', DIV({'class': 'unvalidated'},
                INPUT({'type': 'hidden', 'name': '{{$name}}_invalid[]'       , 'value': {{$name}}_newrefinput.value}),
                ' ',
                {{$name}}_newrefinput.value,
                A({'href': '', 'onclick': '{{$name}}_remove(this); return false'}, '[x]'),
                ' a validation email will be sent when you save your profile'
            ));
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
            alert(get_string('cantremovedefaultemail'));
            return;
        }

        removeElement(x.parentNode);
    }

    function {{$name}}_validated(email) {
        var email = filter(
                function(elem) { return getNodeAttribute(elem, 'type') == 'hidden' && elem.value == email ; },
                getElementsByTagAndClassName('input', null, '{{$name}}_list')
        )[0];

        if (!email) {
            return;
        }

        var div = email.parentNode;
        email = email.value;

        swapDOM(
            div,
            DIV(
                {'class': 'validated'},
                LABEL(null,
                    INPUT({'type': 'radio',  'name': '{{$name}}_selected', 'value': email}),
                    INPUT({'type': 'hidden', 'name': '{{$name}}_valid[]' , 'value': email}),
                    ' ' + email
                ),
                ' ',
                A({'href': '', 'onclick': '{{$name}}_remove(this); return false'}, '[x]')
            )
        );
    }

    function {{$name}}_cookie_check() {
        var cookie = getCookie('validated_email');

        if (cookie) {
            {{$name}}_validated(cookie);
            clearCookie('validated_email');
        }

        callLater(1, {{$name}}_cookie_check);
    }

    addLoadEvent({{$name}}_cookie_check);
</script>
<!-- TODO: shouldn't have css inline -->
<style type="text/css">
    .unvalidated { color: gray; }
</style>
<div id="{{$name}}_list">
{{foreach from=$validated item=email}}
    <div class="validated">
        <label><input{{if $email == $default}} checked{{/if}} type="radio" name="{{$name}}_selected" value="{{$email|escape}}">
        <input type="hidden" name="{{$name}}_valid[]" value="{{$email|escape}}">
        {{$email|escape}}</label>
        <a href="" onclick="{{$name}}_remove(this); return false;">[x]</a>
    </div>
{{/foreach}}
{{foreach from=$unvalidated item=email}}
    <div class="unvalidated">
        <input type="hidden" name="{{$name}}_invalid[]" value="{{$email|escape}}">
        {{$email|escape}}
        <a href="" onclick="{{$name}}_remove(this); return false;">[x]</a>
    </div>
{{/foreach}}
</div>
<a href="" onclick="{{$name}}_new(); return false;">{{str tag="addemail"}}</a>
