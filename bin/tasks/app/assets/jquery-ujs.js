(function($, undefined) {

  $ujs = {
    token: $('meta[name=csrf-token]').attr('content'),
    fire: function(obj, name, data) {
      var evt = $.Event(name);

      obj.trigger(evt, data);

      return evt.result !== false;
    },
    setup: function(options) {
      $.ajaxSetup($.extend({
        headers: { 'X-CSRF-Token': $ujs.token }
      }, options || {}));
    },
    handle: function(el) {
      var that = $(el),
          the_confirm = that.data('confirm'),
          is_remote = that.data('remote'),
          disable = that.data('disable-with');

      if (the_confirm && $ujs.fire(that, 'confirm')) {
        var answer = $ujs.confirm(the_confirm),
            callback = $ujs.fire(that, 'confirm:complete', [answer]);

        if ( ! (answer && callback)) return false;
      }

      if (that.is('form')) {
        if (is_remote) {
          var blank = $ujs.all_fields(that, true);

          if (blank && that.attr('novalidate') == undefined && $ujs.fire(that, 'ajax:aborted:required', [blank])) {
            return false;
          }

          setTimeout(function() {
            $ujs.remote_to(that);
          }, 20);
          return false;
        }
        $ujs.disable(that);
        return true;
      } else {
        if (disable) {
          var method = that.is('input') ? 'val' : 'html',
              old_text = that[method]();

          that[method](disable).data('enable-with', old_text);
          that.is('input') ? that.attr('disabled', 'disabled') : that.addClass('disabled');
        }

        if (is_remote) {
          $ujs.remote_to(that);
        } else {
          $ujs.link_to(that);
        }
      }
      return false;
    },
    remote_to: function(el) {
      var url,
          data,
          method,
          options;

      if ($ujs.fire(el, 'ajax:prepare')) {
        method = el.attr('method') || el.data('method');
        url = el.attr('action') || el.attr('href') || el.data('url');

        if (el.is('form')) {
          data = el.serializeArray();

          var button = el.data('submit-button');
          button ? data.push(button) && el.data('submit-button', null) : null;
        } else if (el.is('a')) {
          data = el.data('params') || null;
        } else {
          data = el.serialize();
          el.data('params') ? data = data + "&" + el.data('params') : null;
        }

        options = {
          type: method || 'GET', data: data, dataType: el.data('type') || null,
          beforeSend: function(xhr, settings) {
            return $ujs.fire(el, 'ajax:before', [xhr, settings]);
          },
          success: function(data, status, xhr) {
            el.trigger('ajax:success', [data, status, xhr]);
          },
          complete: function(xhr, status) {
            el.trigger('ajax:complete', [xhr, status]);
          },
          error: function(xhr, status, error) {
            el.trigger('ajax:error', [xhr, status, error]);
          }
        };

        url ? options.url = url : null;

        $.ajax(options);
      }
    },
    link_to: function(el) {
      if (el.data('method')) {
        var the_html = $('<form method="post"/>').attr({
              action: el.attr('href') || document.location.href
            }).hide();

        the_html.append('<input type="hidden" name="_method" value="' + el.data('method') + '">');
        the_html.append('<input type="hidden" name="_token" value="' + $ujs.token + '">');
        the_html.appendTo(document.body);
        the_html.submit();

        return false;
      }
      return true;
    },
    confirm: function(message) {
      return confirm(message);
    },
    enable: function(el) {
      el.find('input[data-disable-with]').each(function() {
        var that = $(this),
            method = that.is('input') ? 'val' : 'html',
            old_text = that.data('enable-with');

        old_text && that[method](old_text);
        that.is('input') ? that.removeAttr('disabled') : that.removeClass('disabled');
      });
    },
    disable: function(el) {
      el.find('input[data-disable-with]').each(function() {
        var that = $(this),
            method = that.is('input') ? 'val' : 'html',
            new_text = that.data('disable-with');

        that.is('input') ? that.attr('disabled', 'disabled') : that.addClass('disabled');
        ! that.data('enable-with') && that.data('enable-with', that[method]());
        new_text && that[method](new_text);
      });
    },
    all_fields: function(el, blank) {
      var out = [];

      el.find('input:not([type=submit][type=hidden]),textarea').each(function() {
        var input = $(this),
            value = input.val(),
            required = input.attr('required');

        if (required && (blank ? ! value : value)) {
          out.push(input);
        }
      });

      return out.length ? out : false;
    }
  };

  $('form').live('ajax:before.ujs', function(evt) {
    this == evt.target && $ujs.disable($(this), evt);
  });

  $('form').live('ajax:complete.ujs', function(evt) {
    this == evt.target && $ujs.enable($(this), evt);
  });

  $('a[data-disable-with]').live('ajax:complete', function() {
      var el = $(this),
          method = el.is('input') ? 'val' : 'html',
          old_text = el.data('enable-with');
          old_text && el[method](old_text);
  });

  $('form input[type=submit],form input[type=image],form button[type=submit],form button:not([type])').live('click.ujs', function() {
    var button = $(this),
        name = button.attr('name'),
        data = name ? { name: name, value: button.val() } : null;

    button.closest('form').data('submit-button', data);
  });

  $('a[data-disable-with],a[data-confirm],a[data-remote],a[data-method]').live('click.ujs', function() {
    return $ujs.handle(this);
  });

  $('textarea[data-remote],select[data-remote],input[data-remote]').live('change.ujs', function() {
    return $ujs.handle(this);
  });

  $('form').live('submit.ujs', function() {
    return $ujs.handle(this);
  });

  $ujs.setup();

})(window.jQuery);
