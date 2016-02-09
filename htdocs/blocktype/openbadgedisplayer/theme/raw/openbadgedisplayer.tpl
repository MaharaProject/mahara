<div id="openbadges{$id}" class="openbadgedisplayer">{$badgehtml|safe}</div>

<script type="text/javascript">

    (function ($) {
        var blockid = {$id};
        var has_pagemodal = {$has_pagemodal};

        {literal}

        function shorten(str) {
            var n = 40;
            return str.substr(0, n - 1) + (str.length > n ? '...' : '');
        }

        function formatDate(date) {
            if (!date) {
                return '-';
            }
            if (date.toString().match(/^[0-9]+$/)) {
                var d = new Date(0);
                d.setUTCSeconds(date);
                return d.toLocaleDateString();
            }
            return date;
        }

        function urlElement(url) {
            if (!url) {
                return '-';
            }
            return $('<a/>').attr({ href: url, title: url, target: '_blank' }).text(shorten(url));
        }

        function buildBadgeContent(assertion) {
            var el = $('.badge-template').clone().removeClass('badge-template');

            el.find('img.badge-image').attr('src', assertion.badge.image);
            el.find('tr.issuer-name td.value').text(assertion.badge.issuer.name);
            el.find('tr.issuer-url td.value').html(urlElement(assertion.badge.issuer.origin));
            el.find('tr.issuer-organization td.value').text(assertion.badge.issuer.org || '-');

            el.find('tr.badge-name td.value').text(assertion.badge.name);
            el.find('tr.badge-description td.value').text(assertion.badge.description);
            el.find('tr.badge-criteria td.value').html(urlElement(assertion.badge.criteria));

            el.find('tr.issuance-evidence td.value').html(urlElement(assertion.evidence));
            el.find('tr.issuance-issuedon td.value').text(formatDate(assertion.issued_on));
            el.find('tr.issuance-expires td.value').text(formatDate(assertion.expires));

            return el.prop('outerHTML');
        }

        $(function () {
            $('#openbadges' + blockid).on('click', 'img', function () {
                showPreview('small', {html: buildBadgeContent($(this).data('assertion'))});

                // We don't have that shiny new pagemodal used in 15.10. Let's
                // do this the old way.
                if (!has_pagemodal) {
                    $('#viewpreviewinner').width('480px');
                    $("#viewpreview").removeClass('hidden');
                    $("#viewpreview").width('500px');
                    $("#viewpreview").show();

                    disconnectAll('viewpreviewcontent');
                }
            });
        });
    })(jQuery);
</script>
{/literal}

{* Include the template only if it exists. *}
{if $has_pagemodal}
    {include file="pagemodal.tpl"}
{/if}

{include file="blocktype:openbadgedisplayer:badge.tpl"}