const fs = require('fs');
const path = require('path');

// Constroi o caminho absoluto para o arquivo de configuração
const configPath = path.resolve(__dirname, '../public/socket_live_config.json');

let serverConfig;
try {
    serverConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
    console.log("Configuração do servidor carregada com sucesso:", serverConfig);
} catch (error) {
    console.error("Erro ao carregar o arquivo de configuração:", error.message);
}

var http = require('http').Server();
var io = require('socket.io')(http);
var Redis = require('ioredis');
var redis = new Redis();

redis.subscribe('Lives', function(err, count) {
    console.log('subscribe on Lives');
});

redis.on('message', function(channel, message) {

    message = JSON.parse(message);

    console.log(message);

    io.emit(channel + ':' + message.event, message.data);
});

http.listen(serverConfig.port, function() {
    console.log('Listening on Port '+serverConfig.port);
});
