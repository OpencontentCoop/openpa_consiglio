;(function ( $, window, document, undefined ) {

    jQuery.fn.putCursorAtEnd = function() {
      return this.each(function() {
        $(this).focus()
        if (this.setSelectionRange) {
          var len = $(this).val().length * 2;
          this.setSelectionRange(len, len);
        } else {
          $(this).val($(this).val());
        }
        this.scrollTop = 999999;
      });
    };

    var pluginName = "facetnavigation",
        defaults = {
            useForm: false,
            navigationContainer: ".nav-facets",            
            paginationContainer: ".pagination",
            formContainer: ".form-facets",
            contentContainer: ".facet-content",
            inputId: "#searchfacet",
            template:{
                content: {
                    name: "parts/children-facet.tpl",
                    view: "line",
                    page_limit: 10
                },
                navigation: "nav/nav-section-facet.tpl",
            },
            json: '',
            token: '',
            eZJsCoreCallMethod: 'ocst::facetnavigation',
            chosen: {
                allow_single_deselect:true
            }
        };
        
    //var timeout;
    var tooltip;

    function FacetNavigation ( element, options ) {
        this.element = element;
        this.settings = $.extend( {}, defaults, options );
        this._defaults = defaults;
        this._name = pluginName;        
        this.selectedParameters = {};
        this.useForm = this.settings.useForm;
        var currentParameters = {};        
        if (this.useForm) {
            var form = $( this.settings.formContainer, $(this.element) ).serializeArray();
            $.each(form, function(){            
                if (this.value != '') {
                    currentParameters[this.name] = this.value;
                }            
            });
        }else{
            $( this.settings.navigationContainer + ' a.active', $(this.element) ).each(function(){
                var key = $(this).data( 'key' ),
                    value = $(this).data( 'value' );
                currentParameters[key] = value;
            });
        }
        this.currentParameters = currentParameters;
        this.init();
    }

    FacetNavigation.prototype = {
        init: function () {            
            var self = this;                        
            var input = '#' + $(this.element).attr('id') + ' input' + this.settings.inputId;
            var nav = '#' + $(this.element).attr('id') + ' ' + this.settings.navigationContainer + ' a';
            var pagination = '#' + $(this.element).attr('id') + ' ' + this.settings.paginationContainer + ' a';
            $(input).show();            
            $(document)
                .on( 'keyup', input, self, this.onInput )
                .on( 'keydown', function(event){ if(event.keyCode == 13) event.preventDefault(); });
            $(window).on( 'keydown', function(event){
                if(event.keyCode == 13) {
                    event.preventDefault();
                }
            });
            $(document).on( 'click', this.settings.inputId+'clear', self, this.onClearInput );
            $(document).on( 'click', pagination, self, this.onPaginationClick );
            if (this.useForm) {                
                //$(document).on( 'submit', this.settings.formContainer, self, this.onSubmit );
                $(this.settings.formContainer + ' button[type="submit"]', $(this.element)).hide();
                $(document).on( 'change', this.settings.formContainer + " select", self, this.onSubmit );
            }else{
                $(document).on( 'click', nav, self, this.onClick );
            }                        
            $(this.settings.formContainer + " select", $(this.element)).chosen(this.settings.chosen);            
        },
        fetch: function(){                        
            tooltip = null;
            var self = this;
            var settings = this.settings;
            var  data = {
                json: settings.json,                
                token: settings.token,
                userParameters: $.extend( {}, this.currentParameters, this.selectedParameters ),
                template: settings.template
            }            
            $.ez( this.settings.eZJsCoreCallMethod, data, function( response ){
                if (response.error_text != '') {
                    alert(response.error_text);
                }else{
                    $(settings.navigationContainer).replaceWith( response.content.navigation );
                    $(settings.contentContainer).replaceWith( response.content.content );                    
                    $(settings.navigationContainer + " .facet-select").chosen(self.settings.chosen);
                    $(settings.inputId).putCursorAtEnd();
                    $(settings.formContainer + ' button[type="submit"]', $(self.element)).hide();
                }
            });
        },
        onClick: function (event) {
            var self = event.data;            
            var target = $(event.target).closest('a');
            if ( typeof target.data( 'key' ) !== 'undefined' ) {
                var key = target.data( 'key' ),
                    value = target.data( 'value' );
                if ( target.hasClass( 'active' ) ){                    
                    self.selectedParameters[key] = null;
                }
                else{                    
                    self.selectedParameters[key] = value;                
                }
            }else{                
                var parts = target.closest('a').attr( 'href' ).split('/(offset)/');                
                if (typeof parts[1] !== 'undefined' ) {
                    var splitParts = parts[1].split( '/' );
                    self.selectedParameters.offset = splitParts[0]; 
                }else{
                    self.selectedParameters.offset = null;
                }
            }
            self.fetch();
            event.preventDefault();
        },
        onPaginationClick: function (event) {
            var self = event.data;
            var target = $(event.target).closest('a');
            if ( typeof target.data( 'key' ) !== 'undefined' ) {
                var key = target.data( 'key' ),
                    value = target.data( 'value' );
                if ( target.hasClass( 'active' ) ){                    
                    self.selectedParameters[key] = null;
                }
                else{                    
                    self.selectedParameters[key] = value;                
                }
            }else{                
                var parts = target.closest('a').attr( 'href' ).split('/(offset)/');                
                if (typeof parts[1] !== 'undefined' ) {
                    var splitParts = parts[1].split( '/' );
                    self.selectedParameters.offset = splitParts[0]; 
                }else{
                    self.selectedParameters.offset = null;
                }
            }


            self.fetch();
            event.preventDefault();
        },
        onSubmit: function (event) {
            var self = event.data;
            $(self.settings.formContainer + " select", $(self.element)).trigger("chosen:updated");            
            var form = $( self.settings.formContainer, $(self.element) ).serializeArray();      
            $.each(form, function(){            
                self.currentParameters[this.name] = null;
                self.selectedParameters[this.name] = this.value;
            });
            self.selectedParameters.offset = 0;
            self.fetch();
            event.preventDefault();
        },
        onInput: function (event) {            
            var self = event.data;            
            var queryString = $(event.target).val();                        
            self.selectedParameters.query = queryString;
            self.selectedParameters.sort_by = {score:'desc'};
            if (queryString.length == 0) {
                tooltip = null;
                self.selectedParameters.sort_by = null;
            }
            if (tooltip == null && queryString.length > 0) {
                tooltip = true;
                $(event.target)
                    .popover({placement:'top'})
                    .popover('show')
            }
            //if( timeout ) {
            //    clearTimeout( timeout );
            //    timeout = null;
            //}
            //var delay = function() { self.fetch(); };
            //timeout = setTimeout(delay, 600);            
            if(event.keyCode == 13) {
                self.selectedParameters.offset = 0;
                self.fetch();                
            }            
            event.preventDefault();
        },
        onClearInput: function(event){        
            var self = event.data;  
            var input = '#' + $(self.element).attr('id') + ' input' + self.settings.inputId;
            $(input).val('');
            self.selectedParameters.query = null;
            self.selectedParameters.sort_by = null;
            self.fetch();
            tooltip = null;
        }
    };

    $.fn[ pluginName ] = function ( options ) {                
        this.each(function() {            
            if ( !$.data( this, "plugin_" + pluginName ) ) {
                $.data( this, "plugin_" + pluginName, new FacetNavigation( this, options ) );                
            }
        });
        return this;
    };
})( jQuery, window, document );
