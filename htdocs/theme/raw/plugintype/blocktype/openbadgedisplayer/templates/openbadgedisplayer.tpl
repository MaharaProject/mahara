<div id="openbadges{{$id}}" class="openbadgedisplayer">{{$badgehtml|safe}}</div>

<script type="application/javascript">
    (function ($) {
        $(function () {
            $('#openbadges{{$id}}').on('click', 'img', function () {
                showBadgeContent({html: buildBadgeContent($(this).data('assertion'))});
            });
        });
    })(jQuery);
</script>

{{include file="blocktype:openbadgedisplayer:badge.tpl"}}
