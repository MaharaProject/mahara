/**
 * 'Speeds up' the user search if the user has javascript enabled in
 * their browser
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

 // TODO: very similar to user search, potentially could be reused
var ExportQueue = (function($) {
  return function(pager) {
      var self = this;

      this.init = function() {
          self.selectusersdelete = {};
          self.selectusersexport = {};

          self.rewriteHeaders();
          self.rewriteQueryButton();
          self.rewriteCheckboxes();

          paginatorProxy.addObserver(self);
          var oldparams = $.extend({}, pager.params);
          $(self).on('pagechanged', function(e, data) {
            if (!data.changedPage) {
                self.selectusersdelete = {};
                self.selectusersexport = {};
            }
            oldparams = $.extend({}, pager.params);
            $.each(['archive', 'exportdelete'], self.connectSelectedUsersForm);
            self.rewriteCheckboxes();
          });

          $.each(['archive', 'exportdelete'], self.connectSelectedUsersForm);
      };

      this.rewriteHeaders = function() {
          $('#searchresults th.search-results-sort-column a').each(function() {
              var href = $(this).attr('href');
              var sortby = getUrlParameter('sortby', href);
              var sortdir = getUrlParameter('sortdir', href);

              $(this).on("click", function() {
                  var header = $(this).parent();
                  if (!(header.hasClass('asc') || header.hasClass('desc'))) {
                      sortdir = 'asc';
                  }

                  pager.params.sortby = sortby;
                  pager.params.sortdir = sortdir;

                  $(this).closest('thead').find('th').removeClass('asc').removeClass('desc');
                  $(this).parent().addClass(sortdir);
                  var re1 = new RegExp(strings.descending);
                  var re2 = new RegExp(strings.ascending);
                  $(this).closest('tr').find('span').each(function(i, el) {
                      el.innerHTML = el.innerHTML.replace(re1, strings.ascending);
                  });
                  if (sortdir == 'asc') {
                      $(this).find('span').html($(this).find('span').html().replace(re2, strings.descending));
                  }
                  sortdir = (sortdir == 'desc') ? 'asc' : 'desc';

                  pager.sendQuery();
                  return false;
              });
          });
      };

      this.rewriteQueryButton = function() {
          $('#query-button').on("click", function() {
              pager.params.query = $('#query').val();
              var institution = $('#institution');
              if (institution.length) {
                  pager.params.institution = institution.val();
              }
              var institution_requested = $('#institution_requested');
              if (institution_requested.length) {
                  pager.params.institution_requested = institution_requested.val();
              }
              pager.sendQuery();
              return false;
          });
      };

      this.rewriteCheckboxes = function() {
          // For the export checkboxes
          $('#searchresults input.selectusersexport').each(function() {
              var value = $(this).val();
              $(this).on('change', function() {
                  if ($(this).prop('checked')) {
                      self.selectusersexport[value] = 1;
                  }
                  else {
                      delete self.selectusersexport[value];
                  }
              });
              if (self.selectusersexport[value]) {
                  $(this).prop('checked', true);
              }
          });
          if ($('#selectallexport').length) {
              $('#selectallexport').on("click", function() {
                  $('#searchresults input.selectusersexport').each(function() {
                      if (!$(this).is(':disabled')) {
                          self.selectusersexport[$(this).val()] = 1;
                          $(this).prop('checked', true);
                      }
                  });
                  return false;
              });
              $('#selectnoneexport').on("click", function() {
                  $('#searchresults input.selectusersexport').each(function() {
                      delete self.selectusersexport[$(this).val()];
                      $(this).prop('checked', false);
                  });
                  return false;
              });
          }

          // For the delete checkboxes
          $('#searchresults input.selectusersexportdelete').each(function() {
              var value = $(this).val();
              $(this).on('change', function() {
                  if ($(this).prop('checked')) {
                      self.selectusersdelete[value] = 1;
                  }
                  else {
                      delete self.selectusersdelete[value];
                  }
              });
              if (self.selectusersdelete[value]) {
                  $(this).prop('checked', true);
              }
          });
          if ($('#selectalldelete').length) {
              $('#selectalldelete').on("click", function() {
                  $('#searchresults input.selectusersexportdelete').each(function() {
                      self.selectusersdelete[$(this).val()] = 1;
                      $(this).prop('checked', true);
                  });
                  return false;
              });
              $('#selectnonedelete').on("click", function() {
                  $('#searchresults input.selectusersexportdelete').each(function() {
                      delete self.selectusersdelete[$(this).val()];
                      $(this).prop('checked', false);
                  });
                  return false;
              });
          }
      };

      this.connectSelectedUsersForm = function(i, formid) {
          $('#' + formid + ' input.button').on("click", function() {
              // Some of the selected users aren't on the page, so just add them all to the
              // form now.
              var countdelete = 0;
              if (self.selectusersdelete) {
                  for (var j in self.selectusersdelete) {
                      $('#' + formid).append($('<input>', {
                          'type': 'checkbox',
                          'name': 'deleterows[' + j + ']',
                          'value': j,
                          'class': 'd-none',
                          'checked': 'checked'
                      }));
                      countdelete++;
                  }
              }

              var countarchive = 0;
              if (self.selectusersexport) {
                  for (var j in self.selectusersexport) {
                      $('#' + formid).append($('<input>', {
                          'type': 'checkbox',
                          'name': 'exportrows[' + j + ']',
                          'value': j,
                          'class': 'd-none',
                          'checked': 'checked'
                      }));
                      countarchive++;
                  }
              }

              if ((countdelete && $(this).attr('name') == 'delete') || (countarchive && $(this).attr('name') == 'export')) {
                  $('#nousersselected').addClass('d-none');
                  $('#' + formid).append($('<input>', {
                      'type': 'hidden',
                      'name': 'action',
                      'value': $(this).attr('name')
                  }));
                  $('#' + formid).trigger('submit');
                  return false;
              }
              $('#nousersselected').removeClass('d-none');
              return false;
          });
      };

      this.init();
  };
}(jQuery));
