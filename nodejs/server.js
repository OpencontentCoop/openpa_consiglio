// server.js

var http = require('http').createServer(),
    io = require('socket.io').listen(http),
    fs = require('fs'),
    arguments = process.argv.slice(2);

http.listen(8000);

var currentSA = arguments[0];

if ( currentSA == undefined )
{
    console.log( "Specifica un site identifier" );
    process.exit();
}

var baseFile = '/Users/luca/www/openpa.opencontent.it';
var file = baseFile + '/var/' + currentSA + '/cache/push_notifications.json';

console.log( "Start for " + currentSA + ' watching file ' + file );

var lastEmitData;
var emit = function(event, filename){
    fs.readFile(file, 'utf8', function (err, data) {
        var obj;
        if (err) throw err;
        if ( lastEmitData !== data ) {
            obj = JSON.parse(data);
            console.log(obj);
            io.emit(obj.identifier, obj.data);
            lastEmitData = data;
        }
    });
};

if ( !fs.exists( file ) )
    fs.writeFile( file, '' );

var fileWatcher;
var watcher = {
    start: function(){
        fileWatcher = fs.watch( file );
        fileWatcher.on( 'change', function(event, filename){
            emit(event, filename);
            watcher.restart();
        });
        return fileWatcher;
    },
    stop: function(){
        fileWatcher.close();
    },
    restart: function(){
        watcher.stop();
        watcher.start();
    }
};
watcher.start();


