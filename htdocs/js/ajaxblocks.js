/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */
jQuery(window).on('blocksloaded', {}, function() {
"use strict";

    function ajaxBlocks() {
        // Load content into ajax blocks

        var baseurl = $('head').attr('data-basehref'),
            blocks,
            i,
            id;
        // keep count of the blocks loaded by ajax
        window.ajaxBlocksCount = $('div.block[data-blocktype-ajax]').length;

        if($('.block').is('[data-blocktype-ajax]')){
            blocks = $('.block[data-blocktype-ajax]');

            for(i = 0; i < blocks.length; i = i + 1) {
                id = $(blocks[i]).attr('data-blocktype-ajax');

               $(blocks[i]).load(baseurl + "blocktype/blocktype.ajax.php?blockid="+ id, function(){
                    if ($(this).is(':empty')){
                        $(this).closest('.card').addClass('d-none');
                    }
                    if (config.mathjax && MathJax !== undefined) {
                        MathJax.Hub.Queue(["Typeset", MathJax.Hub, blocks.get(i)]);
                    }
                    window.ajaxBlocksCount--;
                    // trigger block resize after the last ajax block has been loaded
                    if (window.ajaxBlocksCount == 0) {
                        $(window).trigger('colresize');
                    }
               });

            }
        }
    }

    // hack to fix issue with mochi kit js in inbox blocks
    $('.mochi-collapse').on('click', function(){
        $(window).trigger('colresize');
    });

    $('.has-attachment .collapse').on('shown.bs.collapse', function () {
        $(window).trigger('colresize');
    });

    ajaxBlocks();

});
