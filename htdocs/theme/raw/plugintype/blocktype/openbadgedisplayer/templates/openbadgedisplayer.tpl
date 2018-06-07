<div id="openbadges{{$id}}" class="openbadgedisplayer">{{$badgehtml|safe}}</div>

<script>
    (function ($) {
        $(function () {
            $('#openbadges{{$id}}').on('click', 'img', function () {
                showBadgeContent({html: buildBadgeContent($(this).data('assertion'))});
            });
            $('#openbadges{{$id}}').on('keypress', 'img', function (event) {
                if (event.keyCode == 13) {
                    showBadgeContent({html: buildBadgeContent($(this).data('assertion'))});
                    $('#badge-content-dialog').on("d-none.bs.modal", function () {
                        $('#' + $(event.target).attr('id')).trigger("focus");
                    });
                }
            });
        });
    })(jQuery);
</script>

{{include file="blocktype:openbadgedisplayer:badge.tpl"}}
