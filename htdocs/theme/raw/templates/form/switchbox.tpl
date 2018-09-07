<div class="form-switch {{$wrapper}}">
    <div class="switch {{$type}}">
        {{$checkbox|safe}}
        <label class="switch-label" for="{{$elementid}}" aria-hidden="true">
            <span class="switch-inner"></span>
            <span class="switch-indicator"></span>
            <span class="state-label on">{{$onlabel}}</span>
            <span class="state-label off">{{$offlabel}}</span>
        </label>
    </div>
    <script>
        if (!window.Switchbox) {
            jQuery.getScript("{{$libfile}}").done(function () { Switchbox.computeWidth("{{$elementid}}"); });
        }
        else {
            Switchbox.computeWidth("{{$elementid}}");
        }
    </script>
</div>
