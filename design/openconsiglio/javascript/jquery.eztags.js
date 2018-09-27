/*global $*/

(function() {
  'use strict';

  // Borrowed from backbonejs with few tweeks
  var klass_extend = function(protoProps, staticProps) {
    var parent = this;
    var child;

    // The constructor function for the new subclass is either defined by you
    // (the "constructor" property in your `extend` definition), or defaulted
    // by us to simply call the parent's constructor.
    if (protoProps && (protoProps.hasOwnProperty('constructor'))) {
      child = protoProps.constructor;
    } else {
      child = function(){ return parent.apply(this, arguments); };
    }

    // Add static properties to the constructor function, if supplied.
    $.extend(child, parent, staticProps);

    // Set the prototype chain to inherit from `parent`, without calling
    // `parent`'s constructor function.
    var Surrogate = function(){ this.constructor = child; };
    Surrogate.prototype = parent.prototype;
    child.prototype = new Surrogate();

    // Add prototype properties (instance properties) to the subclass,
    // if supplied.
    if (protoProps){ $.extend(child.prototype, protoProps);}

    // Set a convenience property in case the parent's prototype is needed
    // later.
    child.__super__ = parent.prototype;

    return child;
  };


  //Declare namspace

  var EzTags = {
    debouncer: function(fn, delay, context){
      var to;
      return function(){
        to && clearTimeout(to);
        to = setTimeout($.proxy.apply($, [fn, context].concat(Array.prototype.slice.apply(arguments))), delay);
      };
    },

    key: {
      ESC: 27,
      TAB: 9,
      RETURN: 13,
      LEFT: 37,
      UP: 38,
      RIGHT: 39,
      DOWN: 40,
      SPACE: 32
    },

    is_key: function(e, keys){
      var self = this;
      !$.isArray(keys) && (keys = [keys]);
      keys = $.map(keys, function(name){ return self.key[name]; });
      return $.inArray(e.which, keys) > -1;
    }
  };


  // Tag ========================================================
  var Tag = function(attributes){
    if(attributes instanceof Tag){return attributes;}
    $.extend(this, {
      id: 0,
      parent_name: null,
      cid: this.constructor.uid(),
      flagSrc: attributes.locale ? this.constructor.iconPath + attributes.locale + '.gif' : null
    }, attributes);
  };

  Tag.id = 0;
  Tag.uid = function(){ return 'c'+(++this.id); };
  Tag.prototype.remove = function() { return this.tags_suggest.remove(this.cid); };
  Tag.prototype.parent = function() {
    return this.collection.find_by('id', this.parent_id);
  };

  Tag.prototype.parents = function() {
    if(this._parents){return this._parents;}
    var tag = this;
    this._parents = [];

    while(tag){
      tag = tag.parent();
      tag && this._parents.unshift(tag);
    }
    console.log('parents', this._parents);
    return this._parents;
  };

  Tag.prototype.self_and_parents = function() {
    return this.parents().concat([this]);
  };





  //Simple collection class ========================================================
  var Collection = function(){
    this.items = [];
    this.indexed = {};
  };


  Collection.prototype.find_by_with_index = function(attr, value) {
    var tag = null;
    for (var i = this.items.length - 1; i >= 0; i--) {
      if(this.items[i][attr] === value){
        tag = this.items[i];
        break;
      }
    }
    return tag ? {item: tag, index: i} : null;
  };

  Collection.prototype.filter = function(iterator) {
    return $.grep(this.items, iterator);
  };

  Collection.prototype.find_by = function() {
    var result = this.find_by_with_index.apply(this, arguments);
    return result ? result.item : null;
  };

  Collection.prototype.add = function(item){
    if(!item){throw new Error('Item is not provided');}
    if(item.id && this.indexed[item.id]){return null;} //if exists
    item.collection = this;
    this.items.push(item);
    this.index_add(item);
    return item;
  };

  Collection.prototype.remove = function(item) {
    this.items.splice(this.find_by_with_index('cid', item.cid).index, 1);
    this.index_remove(item);
    item.collection = null;
    return item;
  };


  Collection.prototype.index_add = function(item) {
    this.indexed[item.cid] = item;
    this.indexed[item.id] = item;
  };

  Collection.prototype.index_remove = function(item) {
    delete this.indexed[item.cid];
    delete this.indexed[item.id];
  };

  Collection.prototype.find = function(id_or_cid) {
    return this.indexed[id_or_cid] || null;
  };

  Collection.prototype.length = function() {
    return this.items.length;
  };

  Collection.prototype.push = function(item) { return this.add(item); };

  Collection.prototype.sort_by = function(ids, atomic){
    var self = this, items = $.map(ids, function(id){ return self.find(id); });
    atomic && (this.items = items);
    return items;
  };


  Collection.prototype.clear = function() {
    this.indexed = {};
    this.items = [];
  };





  // TagSuggest ========================================================
  var Base = function(el, opts) {
    opts || (opts = {});
    this.$el = $(el);
    this.opts = $.extend(true, {}, this.constructor.defaults, opts, this.$el.data());
    //console.log(this.opts);
    this.opts.templates = $.extend({}, this.constructor.defaults.templates, this.templates, opts.templates);
    this.group_id = this.$el.attr('id').replace(this.opts.main_id_prefix, '');
    this.TagKlass = this.opts.TagKlass || Tag;
    this.TagKlass.iconPath = this.opts.iconPath || '';


    this.CollectionKlass = this.opts.CollectionKlass || Collection;

    this.tags = new this.CollectionKlass();
    this.autocomplete_tags = new this.CollectionKlass();

    this.setup_ui();
    this.unserialize();
    this.setup_tree_picker();
    this.render_tags();

    this.initialize && this.initialize();
    this.setup_events();
  };



  Base.defaults = {
    main_id_prefix: 'eztags',

    minCharacters: 1, //++
    maxResults: 24, //++
    maxHeight: 150, //++
    suggestTimeout: 500, //++
    subtreeLimit: 0, //++
    hideRootTag: 0, //++
    maxTags: 0, // ++
    isFilter: false,
    hasAddAccess: false, //++
    ezjscAutocomplete: 'ezjsctags::autocomplete', //++
    ezjscSuggest: 'ezjsctags::suggest', //++
    locale: null, //++
    iconPath: null, //++

    translations: {
      selectedTags: 'SELECTEDTAGS',
      loading: 'LOADING',
      noSelectedTags: 'NOSELECTEDTAGS',
      suggestedTags: 'SUGGESTEDTAGS',
      noSuggestedTags: 'NOSUGGESTEDTAGS',
      addNew: 'ADDNEW',
      clickAddThisTag: 'CLICKADDTHISTAG',
      removeTag: 'REMOVETAG',
      translateTag: 'TRANSLATETAG',
      existingTranslations: 'EXISTINGTRANSLATIONS',
      noExistingTranslations: 'NOEXISTINGTRANSLATIONS',
      addTranslation: 'ADDTRANSLATION',
      cancel: 'CANCEL',
      ok: 'OK'
    },

    templates: {
      skeleton: [
          '<div class="tagssuggest-ui">',
              '<div class="tags-output">',
                  '<label><%=tr.selectedTags%>:</label>'
,                  '<div class="tags-list tags-listed no-results">',
                      '<p class="loading"><%=tr.loading%></p>',
                      '<p class="no-results"><%=tr.noSelectedTags%>.</p>',
                      '<ul class="float-break clearfix js-tags-selected"></ul>',
                  '</div>',
              '</div>',
              '<div class="tags-input">',
                  '<label><%=tr.suggestedTags%>:</label>',
                  '<div class="tags-list tags-suggested no-results">',
                      '<p class="loading"><%=tr.loading%></p>',
                      '<p class="no-results"><%=tr.noSuggestedTags%>.</p>',
                      '<ul class="float-break clearfix js-tags-suggested">',
                  '</div>',
                  '<div class="tagssuggestfieldwrap">',
                      '<input class="tagssuggestfield tags-input-field" type="text" size="70" value="" autocomplete="off" />',
                      '<div class="tagssuggestresults jsonSuggestResults"><div class="results-wrap" /></div>',
                  '</div>',
                  '<input type="button" value="<%=tr.addNew%>" class="button-add-tag button-disabled" disabled="disabled" />',
              '</div>',
          '</div>'
      ],
      suggestedItem: ['<li class="js-suggested-item" data-cid="<%= tag.cid %>" title="<%=tr.clickAddThisTag%>"><img src="<%=tag.flagSrc %>"/><%=tag.name%></li>'],
      selectedItem: ['<li data-cid="<%= tag.cid %>"><img src="<%=tag.flagSrc %>" /><%=tag.name%><a href="#" class="js-tags-remove" title="<%=tr.removeTag%>">&times;</a></li>'],
      autocompleteItem: ['<div data-cid="<%= tag.cid %>" class="js-autocomplete-item resultItem"><a href="#"><img src="<%=tag.flagSrc %>"/><%=tag.name%><span><%= tag.parent_name %></span></a></div>'],
    }
  };

  //TODO: tplCache ????
  Base.prototype.tpl = function(str, data){
    var fn = !/\W/.test(str) ? this.tplCache[str] = this.tplCache[str] || this.tpl($(str, scope).html()) : new Function("obj", "var p=[];with(obj){p.push('" + str.replace(/[\r\t\n]/g, " ").split("<%").join("\t").replace(/((^|%>)[^\t]*)'/g, "$1\r").replace(/\t=(.*?)%>/g, "',$1,'").split("\t").join("');").split("%>").join("p.push('").split("\r").join("\\'") + "');}return p.join('');"); /*jshint ignore:line*/
    return data ? fn(data) : fn;
  };

  /**
  * Renders template
  * @param  {string} name  Name of template to render.
  * @param  {object} data  Optional data to pass into view.
  * @return {string} String containing markup to render.
  */
  Base.prototype.render_template = function(name, data) {
    var t = (this.opts.templates[name] = this.opts.templates[name] || $('.'+name, this.$el).html());
    return this.tpl(t.join ? t.join('') : t, $.extend({}, data, {tr: this.opts.translations}));
  };

  Base.prototype.render_skeleton = function() {
    var $markup = $(this.render_template('skeleton'));
    !this.opts.hasAddAccess && $markup.find('.button-add-tag').remove();
    $markup.find('.tags-listed').removeClass('no-results');
    this.$el.append($markup);
  };

  /**
   * Initializes ui elements
   */
  Base.prototype.setup_ui = function() {
    this.render_skeleton();

    this.$input = this.$('.tags-input-field');
    this.$add_button = this.$('.button-add-tag');

    this.$hidden_inputs = {};
    this.$hidden_inputs.tagids     = this.$('.tagids');
    this.$hidden_inputs.tagnames   = this.$('.tagnames');
    this.$hidden_inputs.tagpids    = this.$('.tagpids');
    this.$hidden_inputs.taglocales = this.$('.taglocales');

    this.$tree_picker_element = $('#parent-selector-tree-'+this.group_id);
    this.$selected_tags = this.$('.js-tags-selected');
    this.$autocomplete_tags = this.$('.results-wrap');

    this.$suggested_tags = this.$('.js-tags-suggested');

  };


  //Suggest ======================================================================================
  /**
   * Fetch suggestion tags based on current tag(s) user has set. If there are no
   * tags set, function exists without doing anything. Else, ajax call is made.
   */
  Base.prototype.fetch_suggestions = function() {
    if(!this.tags.length){return;}


     $.ez(this.opts.ezjscSuggest, {
         tag_ids: this.serialize().tagids,
         subtree_limit: this.opts.subtreeLimit,
         hide_root_tag: this.hideRootTag,
         locale: this.opts.locale
       }, $.proxy(this.after_fetch_suggestions, this));

    //$.get('suggest.json', $.proxy(this.after_fetch_suggestions, this));
  };

  /**
   * Fetch autocomplete. Makes new ajax call only if search string changes.
   * No return value. Makes call to after_fech_autocomplete on successful ajax call.
   * @param  {object} e Event object.
   */
  Base.prototype.fetch_autocomplete = function(e) {
    if(EzTags.is_key(e, ['UP', 'DOWN', 'LEFT', 'RIGHT', 'ESC', 'RETURN'])){return;}
    var search_string = this.get_tag_name_from_input();

    if(search_string.length < this.opts.minCharacters){return;}
    if(search_string === this.last_search_string){
      this.render_autocomplete_tags();
      this.show_autocomplete();
      return;
    }
    this.last_search_string = search_string;

    $.ez(this.opts.ezjscAutocomplete, {
      search_string: search_string,
      subtree_limit: this.opts.subtreeLimit,
      hide_root_tag: this.opts.hideRootTag,
      locale: this.opts.locale
    }, $.proxy(this.after_fetch_autocomplete, this));


    //$.get('autocomplete.json', $.proxy(this.after_fetch_autocomplete, this));
  };

  /**
   * Fills autocomplete_tags property object with fetch tags. And than
   * calls functions to render autocomplete tags and display autocomplete.
   * @param  {object} data Object containing fetch tags.
   */
  Base.prototype.after_fetch_autocomplete = function(data) {
    var tags = data.content.tags, self = this, tag;
    tags = tags.slice(0, this.opts.maxResults); //TODO: shouldn't administration take care of this?
    this.$autocomplete_tags.empty().parent().hide();

    this.autocomplete_tags.clear();
    $.each(tags, function(i, raw){
      self.autocomplete_tags.add(self.parse_remote_tag(raw));
    });

    this.render_autocomplete_tags();
    this.show_autocomplete();

  };

  /**
   * Renders autocomplete tags.
   * No return value.
   */
  Base.prototype.render_autocomplete_tags = function() {
    var self = this;
    var items = $.map(this.available_autocomplete_tags(), function(tag){
      return self.render_template('autocompleteItem', {tag: tag});
    });
    this.$autocomplete_tags.html(items);
  };

  /**
   * Shows autocomplete if there are any available autocomplete_tags or tree picker
   * is NOT open.
   */
  Base.prototype.show_autocomplete = function() {
    var available_autocomplete_tags = this.available_autocomplete_tags();
    if(!available_autocomplete_tags.length || this.tree_picker_open){return;}
    this.$autocomplete_tags.height('auto').parent().show();
    if (this.$autocomplete_tags.height() > this.opts.maxHeight){ this.$autocomplete_tags.height(this.opts.maxHeight);}
  };

  /**
   * Returns available autocomplete tags.
   * @return {object} Callback function that returns only tags that are not already
   * in defined tags object.
   */
  Base.prototype.available_autocomplete_tags = function() {
    var self = this;
    return $.map(this.autocomplete_tags.items, function(tag){
      if(!self.tags.find(tag.id)){ return tag; }
    });
  };

  /**
   * Hides autocomplete element in view
   */
  Base.prototype.close_autocomplete = function() {
    this.$('.results-wrap').html('').parent().hide();
  };

  /**
   * Callback function called after successful ajax call that fetches suggestion tags.
   * Function creates Collection object and adds fetch suggestion tags to it.
   * Returns no value. Calls render function.
   * @param  {object} data Data object containing fetched tags from ajax call.
   */
  Base.prototype.after_fetch_suggestions = function(data) {
    var tag, self = this;
    this.suggested_tags = new this.CollectionKlass();
    $.each(data.content.tags, function(i, raw){
      self.suggested_tags.add(self.parse_remote_tag(raw));
    });
    this.render_suggested_tags();
    //TODO: show_hide_loader
  };


  /**
   * Parses multiple raw_tags and creates new CollectionKlass object if collection
   * is not passed as parameter. Calls parse function on each raw tag and adds them as
   * TagKlass objects to CollectionKlass object. As a result returns tags collection.
   * @param  {object} raw_tags   Object containing objects with raw tag data.
   * @param  {CollectionKlass object} collection CollectionKlass object containing already defined tags.
   * @return {CollectionKlass object} Newly created or edit tags collection.
   */
  Base.prototype.parse_remote_tags = function(raw_tags, collection) {
    var tags = collection || new this.CollectionKlass(),
        self = this;

    $.each(raw_tags, function(i, raw){
      tags.add(self.parse_remote_tag(raw));
    });

    return tags;
  };

  /**
   * Parse recieved tag and returns new TagKlass object
   * @param  {object} raw Object with raw tag data. Not derived from TagKlass.
   * @return {object}     TagKlass object, mapped with data from raw param.
   */
  Base.prototype.parse_remote_tag = function(raw) {
    return new this.TagKlass(raw);
  };

  /**
   * Dislabe add button when input for tag name is empty. Enable it otherwise.
   */
  Base.prototype.enable_or_disable_add_button = function(){
    this.get_tag_name_from_input() ? this.enable_add_button() : this.disable_add_button();
  };


  Base.prototype.enable_add_button = function() {
    this.$add_button.removeClass('button-disabled').addClass('button').removeAttr('disabled');
  };

  Base.prototype.disable_add_button = function() {
    this.$add_button.addClass('button-disabled').removeClass('button').attr('disabled', true);
  };

  /**
   * Map events on event listeners.
   */
  Base.prototype.setup_events = function() {
    this.$add_button.on('click', $.proxy(this.handler_add_buton, this));
    this.$el.on('click', '.js-tags-remove', $.proxy(this.handler_remove_buton, this));
    this.$el.on('click', '.js-suggested-item', $.proxy(this.handler_suggested_tag, this));
    this.$el.on('click', '.js-autocomplete-item', $.proxy(this.handler_autocomplete_tag, this));
    this.$input.on('keyup', $.proxy(this.enable_or_disable_add_button, this));
    this.$input.on('keyup', EzTags.debouncer(this.fetch_autocomplete, this.opts.suggestTimeout, this));
    this.$input.on('keydown', $.proxy(this.navigate_autocomplete_dropdown, this));
    this.$autocomplete_tags.on('keydown', $.proxy(this.navigate_autocomplete_dropdown, this));
    this.on('add:after', $.proxy(this.close_autocomplete, this) );

    this.setup_tree_picker_events();
    this.setup_sortable();
  };

  /**
   * Adds tag user clicks on autocomplete list.
   * @param  {object} e jQuery click event object.
   */
  Base.prototype.handler_autocomplete_tag = function(e){
    e.preventDefault();
    var tag = this.autocomplete_tags.find($(e.target).closest('[data-cid]').data('cid'));
    this.add(tag);
  };

  /**
   * Enables navigation through autocomplete dropdown with following keys:
   * ESC => closes dropdown
   * UP, DOWN => move through items of dropdown
   * SPACE => select item, equivalent to click
   * @param  {object} e jQuery event object on keydown event.
   */
  Base.prototype.navigate_autocomplete_dropdown = function(e) {
    if(EzTags.is_key(e, 'ESC')){ this.close_autocomplete();}

    if(!EzTags.is_key(e, ['UP', 'DOWN', 'SPACE'])){return;}
    var $items = this.$autocomplete_tags.find('a');

    if (!$items.length){ return;}

    //Prevent page from moving
    e.preventDefault();
    e.stopPropagation();

    var index = $items.index(e.target);
    if(e.which === 'SPACE' && index >= 0){
      $items.eq(index).trigger('click');
    }

    EzTags.is_key(e, 'UP') && index >= 0                   && index--;
    EzTags.is_key(e, 'DOWN') && index < $items.length - 1  && index++;


    if(index > -1){
      $items.eq(index).trigger('focus');
    }else{
      this.$input.trigger('focus');
    }
  };

  /**
   * Handles click event on suggested tags. Removes selected tag from suggested tags list
   * and adds it to tags related to this object.
   * @param  {object} e jQuery event object on click event
   */
  Base.prototype.handler_suggested_tag = function(e){
    e.preventDefault();
    if(this.max_tags_limit_reached()){return;}
    var tag = this.suggested_tags.find($(e.target).closest('[data-cid]').data('cid'));
    this.suggested_tags.remove(tag);
    this.add(tag);
    this.render_suggested_tags();
  };

  /**
   * Handles adding a new tag on add button click.
   * Fetches tag name from input field and selected locale, calls validation
   * on these attributes and if subtreeLimit is > 0, adds tag to this tags property.
   * Else, calls function that shows parent tree picker.
   * At the end, closes autocomplete field.
   */
  Base.prototype.handler_add_buton = function() {

    this.new_tag_attributes = {
      name: this.get_tag_name_from_input(),
      locale: this.opts.locale
    };

    if(!this._validate(this.new_tag_attributes)){ return; }
    if(this.opts.subtreeLimit){
      this.new_tag_attributes.parent_id = this.opts.subtreeLimit;
      this.add(this.new_tag_attributes);
    }else{
      this.show_tree_picker();
    }
    this.close_autocomplete();

  };

  /**
   * Handles tag removal on button click.
   * @param  {object} e jQuery event object on click event.
   * @return {object}   Tag that was removed.
   */
  Base.prototype.handler_remove_buton = function(e){
    e.preventDefault();
    return this.remove($(e.target).closest('[data-cid]').data('cid'));
  };


  // Parent picker =================================================================================
  Base.prototype.show_tree_picker = function() {
    this.$tree_picker_element.jqmShow();
  };

  Base.prototype.hide_tree_picker = function() {
    this.$tree_picker_element.jqmHide();
  };


  Base.prototype.setup_tree_picker = function() {
    var self = this;
    this.$tree_picker_element.jqm({
      modal:true,
      overlay:60,
      overlayClass: 'whiteOverlay',
      onShow: function(){
        self.tree_picker_open = true;
        $.jqm.params.onShow.apply(this, arguments);
      },
      onHide: function(){
        self.tree_picker_open = false;
        $.jqm.params.onHide.apply(this, arguments);
      }
    });

    this.setup_tree_picker_dragging();

  };

  Base.prototype.setup_tree_picker_dragging = function() {
    $.fn.draggable && this.$tree_picker_element.draggable({ handle: '.jqDrag' });
  };


  Base.prototype.setup_tree_picker_events = function() {
    var self = this;
    this.$tree_picker_element.on('click', 'a[rel]', function(e){
      e.preventDefault();
      self.select_parent_id_from_tree_picker($(this).attr('rel')); //parent_id is on rel attribute
    });
  };

  /**
   * Renders tags in this.tags property object in list of selected tags.
   */
  Base.prototype.render_tags = function() {
    var self = this;
    var tags = $.map(this.tags.items, function(tag){
      return self.render_template('selectedItem', {tag: tag});
    });
    this.$selected_tags.html(tags);
  };

  /**
   * Renders tags located in this.suggested_tags.items object in list of suggested tags.
   */
  Base.prototype.render_suggested_tags = function() {
    var self = this;
    var tags = $.map(this.suggested_tags.items, function(tag){
      return self.render_template('suggestedItem', {tag: tag});
    });
    this.$suggested_tags.html(tags);
  };

  /**
   * Sets parent_id of tag that required manually selecting parent through tree picker
   * while being created. Check handler_add_buton function for more information.
   * Also adds this newly created tag to this.tags object.
   * @param  {string} parent_id Id of selected parent.
   */
  Base.prototype.select_parent_id_from_tree_picker = function(parent_id){
    this.new_tag_attributes.parent_id = parent_id;
    this.add(this.new_tag_attributes);
  };


  /**
   * Helper method that creates new tag object from TagKlass and adds it to
   * this.tags collection.
   * @param  {object} attributes Contains attributes that describe a tag.
   * @param  {object} opts       Optional parameters.
   * @return {TagKlass object}   Object that was added to this.tags collection.
   */
  Base.prototype.add = function(attributes, opts) {
    opts || (opts = {});
    if(this.max_tags_limit_reached()){return;}
    var tag = new this.TagKlass(attributes);
    this.trigger('add:before', {tag: tag}, opts);
    this.tags.add(tag);
    tag.tags_suggest = this;
    this.max_tags_handler();

    this.trigger('add:after', {tag: tag}, opts);
    this.after_add(opts);
    return tag;
  };

  /**
   * Check if maximum tags limit is reached and disable/enable input field accordingly.
   */
  Base.prototype.max_tags_handler = function() {
    this.$input.attr('disabled', this.max_tags_limit_reached());
  };

  /**
   * Checks if limit for maximum tags per object is reached.
   * @return {boolean} Whether limit is reached or not.
   */
  Base.prototype.max_tags_limit_reached = function() {
    return this.opts.maxTags ? this.tags.length() >= this.opts.maxTags : false;
  };

  /**
   * Helper method that takes care of removing tag from this.tags colletion. If
   * tag isn't in this.tags collection, exists with null value. Else returns removed
   * tag.
   * @param  {string} id Id of a tag we want to remove.
   * @return {object}    Tag that was removed from this.tags collection.
   */
  Base.prototype.remove = function(id) {
    var tag = this.tags.find(id);

    if(tag === null){return null;}

    this.trigger('remove:before', {tag: tag});
    this.tags.remove(tag);
    this.max_tags_handler();
    this.trigger('remove:after', {tag: tag});
    this.after_remove();
    return tag;
  };

  /**
   * Clears entire this.tags collection and adds only tag that was forwarded to
   * function as a parameter.
   * @param  {object} tag Tag that will be only one remaining in this.tags collection.
   */
  Base.prototype.add_only_one = function(tag) {
    this.tags.clear();
    this.add(tag);
  };


  /**
   * Checks whether tag with certain name exists in this.tags collection.
   * @param  {string} name Name of a tag.
   * @return {boolean}     Whether tag exists or not.
   */
  Base.prototype.exists = function(name) {
    return this.tags.find_by('name', name) !== null;
  };

  /**
   * Checks if tag with the same name already exists.
   * @param  {object} attributes Attributes of a tag thats being validated.
   * @return {boolean}           Whether tag already doesn't exists.
   */
  Base.prototype.valid = function(attributes){
    return !this.exists(attributes.name);
  };

  /**
   * Validates a tag attributes and triggers a custom event accordingly.
   * @param  {object} attributes Attributes of a tag thats being validated.
   * @return {boolean}           Whether tag is valid.
   */
  Base.prototype._validate = function(attributes) {
    var result = this.valid(attributes);
    var event = 'tag:' + [result ? 'valid' : 'invalid'];
    this.trigger(event, {attributes: attributes});
    return result;
  };

  /**
   * Helper method called after adding a tag, that updates view if silent option
   * is NOT defined or set to TRUE.
   * @param  {object} opts Optional parameters.
   */
  Base.prototype.after_add = function(opts) {
    opts || (opts = {});
    if(opts.silent){return;}
    this.update_inputs();
    this.render_tags();
  };

  /**
   * Helper method called after removing a tag, that updates view.
   */
  Base.prototype.after_remove = function() {
    this.update_inputs();
    this.render_tags();
  };

  /**
   * Serializes objects into one object containing arrays of tag ids, tag locales,
   * tg pids and tag names.
   * @return {object} Serialized data - object containing 4 arrays.
   */
  Base.prototype.serialize = function() {
    var data = {
      tagids:     [],
      taglocales: [],
      tagpids:    [],
      tagnames:   []
    };

    $.each(this.tags.items, function(index, tag){
      data.tagids.push(tag.id);
      data.taglocales.push(tag.locale);
      data.tagpids.push(tag.parent_id);
      data.tagnames.push(tag.name);
    });

    return data;
  };

  /**
   * Fetches data from hidden inputs and adds tags as Tag objects
   * to this.tags colletion. Does it silently so that update view functions in
   * this.after_add() are NOT called.
   * No return value, tags can be referenced via this.tags
   */
  Base.prototype.unserialize = function() {
    var self = this;
     var ids  =   this.parse_hidden_input('tagids');
     var names =  this.parse_hidden_input('tagnames');
     var locales =  this.parse_hidden_input('taglocales');
     var pids =  this.parse_hidden_input('tagpids');

    $.each(ids, function(i, id){
      self.add({
        id: id,
        name: names[i],
        locale: locales[i],
        parent_id: pids[i]
      }, {silent: true});
    });

  };

  /**
   * Parses hidden input field and returns its value.
   * @param  {string} name Name of hidden input that we want to parse.
   * @return {array}      Values from hidden input or empty array if there was none.
   */
  Base.prototype.parse_hidden_input = function(name) {
    var val = $.trim(this.$hidden_inputs[name].val());
    return val ? val.split('|#') : [];
  };

  /**
   * Updates hidden inputs with values from this.tags colletion.
   */
  Base.prototype.update_inputs = function() {
    var self = this;
    $.each(this.serialize(), function(k, v){
      self.$hidden_inputs[k].val(v.join('|#'));
    });
  };





  Base.prototype.get_tag_name_from_input = function() {
    return $.trim(this.$input.val());
  };

  Base.prototype.clear_input = function() {
    return this.$input.val('');
  };



  Base.prototype.setup_sortable = function() {
    var self = this;
    $.fn.sortable && this.$selected_tags.sortable({
      update: function(/*event, ui*/){
        var new_order = $(this).sortable('toArray', {attribute: 'data-cid'});
        self.on_sortable_update(new_order);
      }
    });
  };


  Base.prototype.on_sortable_update = function(new_order){
    this.tags.sort_by(new_order, true);
    this.update_inputs();
  };


  Base.prototype.destroy = function() {
    //TODO: implement destroy
  };




  Tag.extend = Collection.extend = Base.extend = klass_extend;



  /*Proxy jQuery methods:  trigger, on, off, $ */
  Base.prototype.trigger = function(event, data, opts) {
    if(opts && opts.silent){return;}
    this.$el.trigger(event, $.extend({instance: this}, data));
  };

  Base.prototype.on = function() {
    this.$el.on.apply(this.$el, arguments);
  };

  Base.prototype.off = function() {
    this.$el.off.apply(this.$el, arguments);
  };

  Base.prototype.$ = function(selector){
    return this.$el.find(selector);
  };


  //Exports
  EzTags.Tag = Tag;
  EzTags.Collection = Collection;
  EzTags.Base = Base;



  EzTags.Normal = EzTags.Base.extend({

    initialize: function(){
      this.fetch_suggestions_debounced = EzTags.debouncer(this.fetch_suggestions, this.opts.suggestTimeout, this);
      this.fetch_suggestions_debounced();
    },

    after_add: function(opts) {
      opts || (opts = {});
      if(opts.silent){return;}
      this.update_inputs();
      this.$input.val('');
      this.hide_tree_picker();
      this.render_tags();
      this.new_tag_attributes = {};
      this.fetch_suggestions_debounced();
    },

    after_remove: function() {
      this.update_inputs();
      this.render_tags();
      console.log('after remove');
      this.fetch_suggestions_debounced();
    }

  });




  /**
   * Used when editing class in administration for attribute property "Limit by tags subtree"
   */

  //TODO: should we move this to separate file as it used only in administration???
  EzTags.AdminClassInterface = function(){
    var buttons_base_selector = "eztags-parent-selector-button-",
        $buttons = $('[id^="'+buttons_base_selector+'"]'),
        $tree = $('.parent-selector-tree'),
        parent_id,
        parent_keyword;

    if(!$buttons.length || !$tree.length){return};


    //Helper function
    function getParentTagHierarchy(tag, i) {
      if (tag.attr('rel') === '0'){ return i === 0 ? '(no parent)' : ''; }
      var parent = getParentTagHierarchy(tag.parents('div:first').prev('a'), ++i);
      return (parent ? parent + '/' : '') + tag.parent().find('span').html();
    }

    //Setup jqModal
    $tree.jqm({modal:true, overlay:60, overlayClass:'whiteOverlay'});
    $.fn.draggable && $tree.draggable({ handle: '.jqDrag' });

    $buttons.on('click', function(e) {
      e.preventDefault();
      parent_id      = $('#' + this.id.replace(buttons_base_selector, 'eztags_parent_id_'));
      parent_keyword = $('#' + this.id.replace(buttons_base_selector, 'eztags_parent_keyword_'));
      $tree.jqmShow();
    });

    $(document).on('click', '.contentstructure a:not([class^="openclose"])', function(e) {
      e.preventDefault();
      var tag = $(this);
      if (tag.parents('li.disabled').length){ return false; }
      parent_keyword.html(getParentTagHierarchy(tag, 0));
      parent_id.val(tag.attr('rel'));
      $tree.jqmHide();
    });

  }


  //Setup jquery plugin
  var plugin = 'EzTags';

  //Expose as jquery plugin
  $.fn[plugin] = function(options) {
    var method = typeof options === 'string' && options;
    $(this).each(function() {
      var $this = $(this);
      var data = $this.data();
      var instance = data.instance;
      var builder = data.builder || (options && options.builder) || 'Normal';
      if (instance) {
        method && instance[method]();
        return;
      }
      instance = new EzTags[builder](this, options);
      $this.data(plugin, instance);
    });
    return this;
  };

  //Expose class
  $[plugin] = EzTags;


  //Auto initialize
  $(function(){
    $('[data-eztags]').EzTags();
    EzTags.AdminClassInterface();
  });


})();
