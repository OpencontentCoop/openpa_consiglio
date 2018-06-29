// server.js

var http = require('http').createServer(),
    io = require('socket.io').listen(http),
    fs = require('fs'),
    arguments = process.argv.slice(2);

var currentSA = arguments[0];

if (currentSA == undefined) {
    console.log("Specifica un site identifier");
    process.exit();
}

http.listen(8091, function () {
    console.log('listening events ' + currentSA + ' on *:8091');
});

io.on('connection', function (socket) {
    console.log('-> connected');
    socket.on('broadcast', function (data) {
        console.log(data);
        if(data.sa == currentSA) {
            io.emit(data.identifier, data.data);
            console.log(new Date());
            console.log('emit ' + data.identifier + ': ');
            console.log(data.data);
        }
    });
    socket.on('disconnect', function () {
        console.log('<- disconnected');
    });
});


//
//var baseFile = arguments[1] || '/home/httpd/openpa.opencontent.it/html';
//var file = baseFile + '/var/' + currentSA + '/cache/push_notifications.json';
//
//console.log( "Start for " + currentSA + ' watching file ' + file );
//
//var lastEmitData;
//var emit = function(event, filename){
//    fs.readFile(file, 'utf8', function (err, data) {
//        var obj;
//        if (err) throw err;
//        if ( lastEmitData !== data ) {
//            obj = JSON.parse(data);
//            console.log(obj);
//            io.emit(obj.identifier, obj.data);
//            lastEmitData = data;
//        }
//    });
//};
//
//if ( !fs.exists( file ) )
//    fs.writeFile( file, '' );
//
//var fileWatcher;
//var watcher = {
//    start: function(){
//        fileWatcher = fs.watch( file );
//        fileWatcher.on( 'change', function(event, filename){
//            emit(event, filename);
//            watcher.restart();
//        });
//        return fileWatcher;
//    },
//    stop: function(){
//        fileWatcher.close();
//    },
//    restart: function(){
//        watcher.stop();
//        watcher.start();
//    }
//};
//watcher.start();


