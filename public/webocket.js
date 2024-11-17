const fs = require('fs');
const path = require('path');

// Constroi o caminho absoluto para o arquivo de configuração
const configPath = path.resolve(__dirname, './socket_config2.json');

let serverConfig;
try {
    serverConfig = JSON.parse(fs.readFileSync(configPath, 'utf8'));
    console.log("Configuração do servidor carregada com sucesso:", serverConfig);
} catch (error) {
    console.error("Erro ao carregar o arquivo de configuração:", error.message);
}

// Exporta a configuração para outros módulos
module.exports = serverConfig;

// app.js - executa os outros arquivos
require('../PTWebSocket/ServerMN');
require('../PTWebSocket/Arcade');
require('../PTWebSocket/Slots');
