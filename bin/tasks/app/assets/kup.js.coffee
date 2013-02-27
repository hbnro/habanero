(($) ->

  cache = {}
  params = {}

  params.locals = {}

  params.format = on
  params.autoescape = on

  params.tags_used = """
a abbr address article aside audio b bdi bdo blockquote button canvas caption cite code colgroup datalist dd del details dfn div dl dt em
fieldset figcaption figure footer form h1 h2 h3 h4 h5 h6 header hgroup i iframe ins kbd label legend li map mark menu meter nav noscript object
ol optgroup option output p pre progress q rp rt ruby s samp section select small span strong sub summary sup table tbody td textarea tfoot
th thead time tr u ul video area base br col command embed hr img input keygen param source track wbr
""".replace(/\n/g, ' ').split ' '

  params.self_closing = """
area base br col command embed hr img input keygen param source track wbr
""".split(' ')


  dummy = (defaults = {}) ->
    defaults = $.extend defaults.params or {}, defaults
    data = defaults.locals

    __ck =
      tabs: 0
      buffer: []
      defaults: defaults

      repeat: (string, count) -> Array(count + 1).join string

      indent: -> @output @repeat('  ', @tabs) if @defaults.format

      render_idclass: (str) ->
        classes = []

        for i in str.split '.'
          if '#' in i
            id = i.replace '#', ''
          else
            classes.push i unless i is ''

        @output " id=\"#{id}\"" if id

        if classes.length > 0
          @output " class=\""
          for c in classes
            @output ' ' unless c is classes[0]
            @output c
          @output '"'

      render_attrs: (obj, prefix = '') ->
        for k, v of obj
          v = k if typeof v is 'boolean' and v
          v = "(#{v}).call(this);" if typeof v is 'function'

          if typeof v is 'object' and v not instanceof Array
            @render_attrs(v, prefix + k + '-')
          else if v
            @output " #{prefix + k}=\"#{__ck.esc(v)}\""

      render_contents: (contents) ->
        switch typeof contents
          when 'string', 'number', 'boolean'
            @output __ck.esc(contents)
          when 'function'
            @output '\n' if @defaults.format
            @tabs++
            result = contents.call data
            if typeof result is 'string'
              @indent()
              @output __ck.esc(result)
              @output '\n' if @defaults.format
            @tabs--
            @indent()

      render_tag: (name, idclass, attrs, contents) ->
        @indent()

        @output "<#{name}"
        @render_idclass(idclass) if idclass
        @render_attrs(attrs) if attrs

        if name in @defaults.self_closing
          @output ' />'
          @output '\n' if @defaults.format
        else
          @output '>'

          @render_contents(contents)

          @output "</#{name}>"
          @output '\n' if @defaults.format

      output: (txt) ->
        __ck.buffer.push String(txt)

      tag: (name, args) ->
        for a in args
          switch typeof a
            when 'function'
              contents = a
            when 'object'
              attrs = a
            when 'number', 'boolean'
              contents = a
            when 'string'
              if args.length is 1
                contents = a
              else
                if a is args[0]
                  idclass = a
                else
                  contents = a

        __ck.render_tag(name, idclass, attrs, contents)

      esc: (txt) ->
        if @defaults.autoescape
          String(txt)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
        else
          String(txt)

    null


  dummy = String(dummy)
    .replace(/function\s*\(.*\)\s*\{/, '')
    .replace(/return null;\s*\}$/, '')

  helpers = """
  var h, tag;
  h = __ck.esc;
  tag = __ck.tag;
  """.replace(/\n/g, '')

  helpers += "var #{params.tags_used.join ','};"
  helpers += "#{t} = function(){return tag('#{t}', arguments);};" for t in params.tags_used


  kompile = (template) ->
    switch typeof template
      when 'function'
        template = String(template)
          .replace(/^\s*function\s*\(\s*\)\s*\{\s*/, '')
          .replace(/\s*\}\s*$/, '')

    template = dummy + helpers + template
      .replace(/\s\/\/.*/g, '')
      .replace(/^\s*/, '')
      .replace(/\s*$/, '')
      .replace(/^return\s*/, '')

    template += ';return __ck.buffer.join("");'

    (locals, defaults) ->
      defaults ?= {}
      defaults.params = params
      defaults.locals = locals or {}
      (new Function('defaults', template))(defaults)


  $.fn.kup = (locals , defaults) ->
    unless template = cache[$(@).selector]
      cache[$(@).selector] = template = kompile($(@).html())
    template(locals, defaults)

  $.kup = (template, locals, defaults) ->
    template = kompile(template)
    template(locals, defaults)

)(window.jQuery)
