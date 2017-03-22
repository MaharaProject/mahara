<div id="openbadges{{$id}}" class="openbadgedisplayer">{{$badgehtml|safe}}</div>

<script type="application/javascript">
    (function ($) {
        $(function () {
            $('#openbadges{{$id}}').on('click', 'img', function () {
                showBadgeContent({html: buildBadgeContent($(this).data('assertion'))});
            });
            $('#openbadges{{$id}}').on('keypress', 'img', function (event) {
                if (event.keyCode == 13) {
                    showBadgeContent({html: buildBadgeContent($(this).data('assertion'))});
                    $('#badge-content-dialog').on("hidden.bs.modal", function () {
                        $('#' + $(event.target).attr('id')).focus();
                    });
                }
            });
        });
    })(jQuery);
</script>

{{include file="blocktype:openbadgedisplayer:badge.tpl"}}
