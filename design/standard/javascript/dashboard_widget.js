(function ($, window, document, undefined) {
    "use strict";
    var pluginName = "openConsiglioWidget",
        defaults = {
            "mainQuery": null,
            "resultTplSelector": null,
            "nextPageSelector": null,
            "prevPageSelector": null,
        };

    function OpenConsiglioWidget(element, options) {

        var container= $(element);
        this.settings = $.extend(true, {}, defaults, options);

        var tools = $.opendataTools;
        $.views.helpers(tools.helpers);

        var currentPage = 0;
        var queryPerPage = [];

        var template = $.templates(this.settings.resultTplSelector);
        var nextPageSelector = this.settings.nextPageSelector;
        var prevPageSelector = this.settings.prevPageSelector;

        var runQuery = function (query) {
            tools.find(query, function (response) {
                queryPerPage[currentPage] = query;
                response.currentPage = currentPage;
                response.prevPageQuery = jQuery.type(queryPerPage[currentPage - 1]) === "undefined" ? null : queryPerPage[currentPage - 1];

                var renderData = $(template.render(response));

                container.html(renderData);

                container.find(nextPageSelector).on('click', function (e) {
                    currentPage++;
                    runQuery($(this).data('query'));
                    e.preventDefault();
                });

                container.find(prevPageSelector).on('click', function (e) {
                    currentPage--;
                    runQuery($(this).data('query'));
                    e.preventDefault();
                });
            });
        };

        runQuery(this.settings.mainQuery);
    }


    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, pluginName)) {
                $.data(this, pluginName, new OpenConsiglioWidget(this, options));
            }
        });
    };

})(jQuery, window, document);