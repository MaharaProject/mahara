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

var UserSearch = (function($) {
  return function(pager) {
      var self = this;

      this.init = function() {
          self.selectusers = {};

          self.rewriteHeaders();
          self.rewriteInitials();
          self.rewriteQueryButton();
          self.rewriteQueryField();
          self.rewriteCheckboxes();
          self.rewriteLoggedInFilter();
          self.rewriteDuplicateEmailFilter();
          self.rewriteObjectionableContentFilter();

          paginatorProxy.addObserver(self);
          var oldparams = $.extend({}, pager.params);
          $(self).on('pagechanged', function(e, data) {
              if (!data.changedPage) {
                  self.selectusers = {};
              }
              oldparams = $.extend({}, pager.params);
              self.rewriteCheckboxes();
          });

          $.each(['bulkactions', 'report'], self.connectSelectedUsersForm);
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

      this.rewriteInitials = function() {
          $('#firstnamelist span.first-initial').each(function() {
              $(this).on("click", function() {
                  self.searchInitial('f', $(this));
                  return false;
              });
          });
          $('#lastnamelist span.last-initial').each(function() {
              $(this).on("click", function() {
                  self.searchInitial('l', $(this));
                  return false;
              });
          });
      };

      this.searchInitial = function(initialtype, el) {
          // Clear all search params except for the other initial
          if (initialtype == 'f') {
              $('#firstnamelist span.selected').removeClass('selected');
          }
          else if (initialtype == 'l') {
              $('#lastnamelist span.selected').removeClass('selected');
          }
          el.addClass('selected');
          if (el.hasClass('all')) {
              delete pager.params[initialtype];
          }
          else {
              pager.params[initialtype] = el.text().replace(/\s+/g, '');
          }
          pager.params.offset = 0;
          pager.sendQuery();
      };

      this.rewriteQueryButton = function() {
          $('#query-button').on("click", function() {
              self.submitUserQuery();
          });
      };

      this.submitUserQuery = function() {
          pager.params.offset = 0;
          pager.params.query = $('#query').val();
          var institution = $('#institution');
          if (institution) {
              pager.params.institution = institution.val();
          }
          var institution_requested = $('#institution_requested');
          if (institution_requested) {
              pager.params.institution_requested = institution_requested.val();
          }
          pager.sendQuery();
          return false;
      };

      this.rewriteQueryField = function() {
          $('#query').on('keypress', function(event) {
              if (event.keyCode == 13) {
                  self.submitUserQuery();
              }
          });
      };

      this.rewriteCheckboxes = function() {
          $('#searchresults input.selectusers').each(function() {
              var value = $(this).val();
              $(this).on('change', function() {
                  if ($(this).prop('checked')) {
                      $(this).closest('tr').addClass('warning'); // visual selected indicator
                      self.selectusers[value] = 1;
                  }
                  else {
                      $(this).closest('tr').removeClass('warning'); // visual selected indicator
                      delete self.selectusers[value];
                  }
                  //update button state
                  if ($('#searchresults input.selectusers:checked').length > 0) {
                      $('.withselectedusers button').removeClass('disabled');
                  }
                  else {
                      $('.withselectedusers button').addClass('disabled');
                  }
              });
              if (self.selectusers[value]) {
                  $(this).prop('checked', true);
              }
          });
          if ($('#selectall').length) {
              $('#selectall').on("click", function() {
                  $(this).addClass('active');
                  $(this).siblings().removeClass('active');
                  $('.withselectedusers button').removeClass('disabled');
                  $('#searchresults input.selectusers').each(function() {
                      self.selectusers[$(this).val()] = 1;
                      $(this).prop('checked', true);
                      $(this).closest('tr').addClass('warning'); // visual selected indicator
                  });
                  return false;
              });
              $('#selectnone').on("click", function() {
                  $(this).addClass('active');
                  $(this).siblings().removeClass('active');
                  $('.withselectedusers button').addClass('disabled');
                  $('#searchresults input.selectusers').each(function() {
                      delete self.selectusers[$(this).val()];
                      $(this).prop('checked', false);
                      $(this).closest('tr').removeClass('warning'); // visual selected indicator
                  });
                  return false;
              });
          }
      };

      this.rewriteLoggedInFilter = function() {
          $('#loggedin').on('change', function() {
              var type = $(this).val();
              pager.params.offset = 0;
              pager.params.loggedin = type;
              if (type === 'since' || type === 'notsince') {
                  $('#loggedindate_container').removeClass('js-hidden');
              }
              else {
                  $('#loggedindate_container').addClass('js-hidden');
              }
              pager.sendQuery();
              return false;
          });
          input_loggedinform_loggedindate.off("change.datetimepicker");
          input_loggedinform_loggedindate.on("change.datetimepicker", function(e) {
              // Set handler directly so that calendar works
              pager.params.offset = 0;
              pager.params.loggedindate = $(this).val();
              pager.sendQuery();
          });
      };

      this.rewriteDuplicateEmailFilter = function() {
          $('#duplicateemail').on("click", function() {
              pager.params.offset = 0;
              pager.params.duplicateemail = $(this).prop('checked');
              pager.sendQuery();
          });
      };

      this.rewriteObjectionableContentFilter = function() {
          $('#objectionable').on("click", function() {
              pager.params.offset = 0;
              pager.params.objectionable = $(this).prop('checked');
              pager.sendQuery();
          });
      };

      this.connectSelectedUsersForm = function(i, formid) {
          $('#' + formid + ' button').on("click", function() {
              // Some of the selected users aren't on the page, so just add them all to the
              // form now.
              var count = 0;
              if (self.selectusers) {
                  for (var j in self.selectusers) {
                      $('#' + formid).append($('<input>', {
                          'type': 'checkbox',
                          'name': 'users[' + j + ']',
                          'value': j,
                          'class': 'd-none',
                          'checked': 'checked'
                      }));
                      count++;
                  }
              }
              if (count) {
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
