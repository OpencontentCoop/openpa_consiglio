(function($){

    var TimelinePresenze, defaultOptions, __bind, presenzeLoaded = [], userTimelines = [];

    __bind = function(fn, me) {
        return function() {
            return fn.apply(me, arguments);
        };
    };

    // Plugin default options.
    defaultOptions = {
        start: 0,
        end: 0,
        total: 0,
        status: null,
        presenze: []
    };

    var timeConverter = function(timestamp){
        var a = new Date(timestamp * 1000);
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        var year = a.getFullYear();
        var month = months[a.getMonth()];
        var date = a.getDate();
        var hour = a.getHours();
        var min = a.getMinutes();
        var sec = a.getSeconds();
        //date + '/' + month + '/' + year + ' ' +
        return hour + ':' + min + ':' + sec ;
    };

    var singleEventTemplate = '<i class="fa has-tooltip" style="position: absolute;margin-left:-5px;font-size: 1.2em" data-placement="left" data-toggle="tooltip"></i>';
    var startEndEventTemplate = '<i class="fa has-tooltip" style="position: absolute; font-size: 1.5em; top:0;" data-placement="left" data-toggle="tooltip"></i>';
    var singleIntervalTemplate = '<div style="position: absolute;height: 20px;top:0;left:0;min-width: 1px">';
    var timeholderEventTemplate = '<i class="fa fa-long-arrow-down has-tooltip" style="position: absolute;margin-left:-3px;font-size: 1em;top:-13px" data-placement="left" data-toggle="tooltip"></i>';

    var getPropertiesByPresenza = function( presenza ){
        if ( presenza == undefined ) {
            presenza = {
                Type: null,
                IsIn: 0
            };
        }
        var properties = {}, text;
        if (presenza.IsIn == 1) {
            properties.color = '#5cb85c';
            text = 'presente';
        } else {
            properties.color = '#f0ad4e';
            text = 'assente';
        }
        properties.title = presenza.Type + ": " + text + " (" + timeConverter(presenza.CreatedTime) + ")";
        return properties;
    };

    var findUserTimeline = function(userId){
        var result = $.grep( userTimelines, function(e){ return e.id == userId; });
        return result[0];
    };

    var refreshInterval;

    TimelinePresenze = (function(options) {

        function TimelinePresenze(container, options) {

            this.container = container;

            // plugin variables.
            this.resizeTimer = null;

            // Extend default options.
            $.extend(true, this, defaultOptions, options);

            if( this.container.data('url') )
                this.url = this.container.data('url');

            // Bind methods.
            this.add = __bind(this.add, this);
            this.update = __bind(this.update, this);
            this.refresh = __bind(this.refresh, this);
            this.init = __bind(this.init, this);

            // Listen to resize event if requested.
            if (this.autoResize) {
                $(window).bind('resize.TimelinePresenze', this.onResize);
            }

            this.build = function(){
                console.log('build timeline');
                presenzeLoaded = [];
                userTimelines = [];
                $('.timeline', this.container)
                    .each(function(){
                        $(this).empty().css('position','relative');
                        var timeline = buildTimelineContainer( $(this) );
                    })
                    .promise().done( function(){
                        $.each( that.presenze, function(i,v){
                            that.add(v);
                        });
                        if ( that.status == 'in_progress' ) {
                            refreshInterval = setInterval(function () {
                                var now = Math.round(+new Date() / 1000);
                                setTimeHolders(now);
                            }, 1000);
                        }else{
                            clearInterval(refreshInterval);
                        }
                    });
            };

            var that = this;
            var buildTimelineContainer = function( $element ){
                var userId = $element.data('userid');
                var container = $('<div id="timeline-container-'+userId+'" style="border-top: 20px solid #eee; margin-top: 5px;" />')
                $element.parent().css('padding-left','20px').css('padding-right','20px');
                $element.append( container );
                builStartEndInterval(container);
                userTimelines.push({
                    id:userId,
                    items:[],
                    timeline:container
                });
                return container;
            };

            var builStartEndInterval = function($timeline){
                var start = $(startEndEventTemplate).css('left','-20px').attr('title',"Inizio seduta ("+timeConverter(that.start)+")").addClass('fa-clock-o');
                $timeline.append( start );
                var endText = "Fine seduta prevista";
                if ( that.status == 'closed' ) endText = "Fine seduta";
                var end = $(startEndEventTemplate).css('right','-20px').attr('title',endText + " ("+timeConverter(that.end)+")").addClass('fa-clock-o');
                $timeline.append( end );
            };

            var setTimeHolders = function(now){
                $.each(userTimelines, function(){
                    var presenza = $(this.items).get(-1);
                    var interval = $('#timeholder-'+this.id);
                    var event = $('#timeholder-event-'+this.id);
                    if ( interval.length == 0 )
                        this.timeline.append( $(singleIntervalTemplate).attr('id','timeholder-'+this.id) );
                    if ( event.length == 0 )
                        this.timeline.append( $(timeholderEventTemplate).attr('id','timeholder-event-'+this.id) );
                    var percent = ( now - that.start ) * 100 / that.total + '%';
                    var properties = getPropertiesByPresenza( presenza );
                    interval.css('width', percent).css('background', properties.color).css('z-index', 1);
                    var title = "Adesso ("+timeConverter(now)+")";
                    event.css('left', percent).attr('data-original-title',title);
                });
            };
        };

        TimelinePresenze.prototype.update = function(options) {
            $.extend(true, this, options);
            this.build();
        };

        // Method for updating the plugins options.
        TimelinePresenze.prototype.add = function(presenza) {
            var now = Math.round(+new Date()/1000) + 3600;
            if( presenza.CreatedTime > (this.end+60) ){
                this.end = now;
                this.total = this.end - this.start;
                this.build();
            }else {
                if ($.inArray(presenza.ID, presenzeLoaded) == -1) {
                    presenzeLoaded.push(presenza.ID);
                    var userTimeline = findUserTimeline(presenza.UserID);
                    if (userTimeline) {
                        var last = $(userTimeline.items).get(-1);
                        var percent = ( presenza.CreatedTime - this.start ) * 100 / this.total + '%';
                        var properties = getPropertiesByPresenza(presenza);
                        var lastProperties = getPropertiesByPresenza(last);
                        var zIndex = 10000 - presenzeLoaded.length;
                        var interval = $(singleIntervalTemplate).css('width', percent).css('background', lastProperties.color).css('z-index', zIndex);
                        userTimeline.timeline.append(interval);
                        var event = $(singleEventTemplate).css('left', percent).attr('title', properties.title).css('color', properties.color);
                        if (userTimeline.items.length % 2 == 1) {
                            event.css('top', '-11px').addClass('fa-caret-down');
                            presenza.position = 'top';
                        } else {
                            event.css('bottom', '-11px').addClass('fa-caret-up');
                            presenza.position = 'bottom';
                        }
                        userTimeline.timeline.append(event);
                        userTimeline.items.push(presenza);
                    }
                }
            }
        };

        TimelinePresenze.prototype.refresh = function() {
            if( this.url ){
                var that = this;
                $.get( that.url, function(response){
                    that.update( response.data );
                });
            }else{
                this.build();
            }
        };

        // Main method.
        TimelinePresenze.prototype.init = function() {
            if( this.url ){
                var that = this;
                $.get( that.url, function(response){
                    that.update( response.data );
                });
            }else{
                this.build();
            }
        };

        return TimelinePresenze;
    })();

    $.fn.timelinePresenze = function(options) {
        // Create a TimelinePresenze instance if not available.
        if (!this.data( 'timeline_presenze' )) {
            this.data( 'timeline_presenze', new TimelinePresenze(this, options || {}) );
        } else {
            this.data( 'timeline_presenze' ).update(options || {});
        }

        // Init plugin.
        this.data( 'timeline_presenze' ).init();

        // Display items (if hidden) and return jQuery object to maintain chainability.
        return this.show();
    };
})(jQuery);