<?php
/**
 * Servidor WebSocket simple usando sockets nativos de PHP
 * No requiere dependencias externas
 */

class WebSocketServer {
    private $clients = [];
    private $socket;
    
    // Respuestas del bot
    private $botResponses = [
        'saludos' => [
            '¡Hola! 👋 ¿Cómo estás?',
            '¡Hola! ¿En qué puedo ayudarte?',
            '¡Hey! ¿Qué tal?'
        ],
        'despedidas' => [
            '¡Hasta luego! 👋',
            '¡Adiós! Que tengas un buen día',
            '¡Nos vemos! 😊'
        ],
        'agradecimientos' => [
            '¡De nada! 😊',
            '¡Un placer ayudarte!',
            '¡Para eso estoy! 🤖'
        ],
        'estados' => [
            '¡Estoy genial! Listo para chatear 🤖',
            '¡Funcionando perfectamente! ¿Y tú?',
            '¡Todo bien por aquí! 😊'
        ],
        'nombres' => [
            'Soy tu bot de práctica WebSocket hecho en PHP 🤖',
            'Me llamo BotSocket PHP, ¡mucho gusto!',
            'Soy un bot PHP para ayudarte a aprender WebSockets'
        ],
        'default' => [
            'Interesante... cuéntame más 🤔',
            'Entiendo, ¿algo más que quieras decir?',
            'Eso es genial! ¿Qué más?',
            'Hmm, no estoy seguro de cómo responder a eso 😅',
            'Estoy aprendiendo, pero no sé mucho sobre eso',
            '¡Gracias por compartir eso! 😊'
        ],
        'websocket' => [
            '¡Los WebSockets son geniales! Permiten comunicación bidireccional en tiempo real 🚀',
            'WebSocket es un protocolo de comunicación que proporciona canales de comunicación full-duplex',
            '¡Me encanta hablar de WebSockets! Es la tecnología que me da vida 🤖'
        ],
        'programacion' => [
            'La programación es fascinante! ¿Qué lenguaje te gusta más?',
            '¡Programar es crear magia con código! ✨',
            'Cada línea de código es una oportunidad para aprender algo nuevo'
        ],
        'php' => [
            '¡PHP es genial! Estoy hecho con PHP puro 🐘',
            'PHP es uno de los lenguajes más usados en la web!',
            '¡Me encanta PHP! Es el lenguaje que me da vida 💜'
        ]
    ];

    public function __construct($host = '0.0.0.0', $port = null) {
        // Usar puerto de Railway si está disponible, sino 8080
        $port = $port ?? (getenv('PORT') ?: 8080);
        
        // Crear socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $host, $port);
        socket_listen($this->socket);
        
        echo "🚀 Servidor WebSocket iniciado en ws://{$host}:{$port}\n";
        echo "💡 Abre index.html en tu navegador para conectarte\n";
        echo "Presiona Ctrl+C para detener el servidor\n\n";
    }

    public function run() {
        while (true) {
            $changed = array_merge([$this->socket], $this->clients);
            $write = null;
            $except = null;
            
            @socket_select($changed, $write, $except, 0, 200000);
            
            // Verificar nuevas conexiones
            if (in_array($this->socket, $changed)) {
                $newSocket = socket_accept($this->socket);
                $this->clients[] = $newSocket;
                
                $header = socket_read($newSocket, 1024);
                $this->performHandshake($header, $newSocket);
                
                socket_getpeername($newSocket, $ip);
                echo "✅ Nuevo cliente conectado desde {$ip}\n";
                
                $this->send($newSocket, '¡Conexión establecida! Estoy listo para chatear 🤖');
                
                unset($changed[array_search($this->socket, $changed)]);
            }
            
            // Verificar mensajes de clientes existentes
            foreach ($changed as $changedSocket) {
                $buf = @socket_read($changedSocket, 2048, PHP_BINARY_READ);
                
                if ($buf === false || $buf === '') {
                    // Cliente desconectado
                    $index = array_search($changedSocket, $this->clients);
                    socket_close($changedSocket);
                    unset($this->clients[$index]);
                    echo "❌ Cliente desconectado\n";
                    continue;
                }
                
                $receivedText = $this->unmask($buf);
                
                if (!empty($receivedText)) {
                    echo "📨 Mensaje recibido: {$receivedText}\n";
                    
                    // Simular tiempo de pensamiento
                    usleep(rand(500000, 1500000));
                    
                    $response = $this->generateBotResponse($receivedText);
                    echo "📤 Respuesta enviada: {$response}\n\n";
                    
                    $this->send($changedSocket, $response);
                }
            }
        }
    }

    private function performHandshake($headers, $socket) {
        $lines = explode("\n", $headers);
        $keyLine = '';
        
        foreach ($lines as $line) {
            if (strpos($line, 'Sec-WebSocket-Key') !== false) {
                $keyLine = trim($line);
                break;
            }
        }
        
        $key = explode(': ', $keyLine)[1];
        $key = trim($key);
        
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
        
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\n" .
                   "Upgrade: websocket\r\n" .
                   "Connection: Upgrade\r\n" .
                   "Sec-WebSocket-Accept: {$acceptKey}\r\n\r\n";
        
        socket_write($socket, $upgrade, strlen($upgrade));
    }

    private function unmask($payload) {
        $length = ord($payload[1]) & 127;
        
        if ($length == 126) {
            $masks = substr($payload, 4, 4);
            $data = substr($payload, 8);
        } elseif ($length == 127) {
            $masks = substr($payload, 10, 4);
            $data = substr($payload, 14);
        } else {
            $masks = substr($payload, 2, 4);
            $data = substr($payload, 6);
        }
        
        $text = '';
        for ($i = 0; $i < strlen($data); $i++) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        
        return $text;
    }

    private function send($client, $message) {
        $message = $this->encode($message);
        @socket_write($client, $message, strlen($message));
    }

    private function encode($message) {
        $length = strlen($message);
        $header = chr(129);
        
        if ($length <= 125) {
            $header .= chr($length);
        } elseif ($length <= 65535) {
            $header .= chr(126) . pack('n', $length);
        } else {
            $header .= chr(127) . pack('NN', 0, $length);
        }
        
        return $header . $message;
    }
    
    private function generateBotResponse($message) {
        $lowerMessage = mb_strtolower(trim($message));
        
        // Saludos
        if (preg_match('/\b(hola|hey|buenos días|buenas tardes|buenas noches|saludos|qué tal|que tal)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['saludos']);
        }
        
        // Despedidas
        if (preg_match('/\b(adiós|adios|hasta luego|chao|bye|nos vemos|me voy)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['despedidas']);
        }
        
        // Agradecimientos
        if (preg_match('/\b(gracias|graciass|thank you|thanks)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['agradecimientos']);
        }
        
        // Estado del bot
        if (preg_match('/\b(cómo estás|como estas|qué tal|que tal|cómo te va|como te va)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['estados']);
        }
        
        // Nombre del bot
        if (preg_match('/\b(cómo te llamas|como te llamas|tu nombre|quién eres|quien eres)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['nombres']);
        }
        
        // WebSockets
        if (preg_match('/\b(websocket|websockets|socket|tiempo real)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['websocket']);
        }
        
        // Programación
        if (preg_match('/\b(programar|programación|programacion|código|codigo|desarrollar|javascript|python|java)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['programacion']);
        }
        
        // PHP
        if (preg_match('/\b(php|elefante)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['php']);
        }
        
        // Hora
        if (preg_match('/\b(qué hora|que hora|hora|horario)\b/u', $lowerMessage)) {
            return 'Son las ' . date('H:i:s') . ' ⏰';
        }
        
        // Fecha
        if (preg_match('/\b(qué día|que dia|fecha|día de hoy|dia de hoy)\b/u', $lowerMessage)) {
            $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            $meses = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
            $dia = $dias[date('w')];
            $mes = $meses[date('n') - 1];
            return "Hoy es {$dia}, " . date('j') . " de {$mes} de " . date('Y') . " 📅";
        }
        
        // Respuesta por defecto
        return $this->getRandomResponse($this->botResponses['default']);
    }
    
    private function getRandomResponse($responses) {
        return $responses[array_rand($responses)];
    }

    public function __destruct() {
        socket_close($this->socket);
        echo "\n🛑 Servidor detenido\n";
    }
}

// Iniciar servidor
$server = new WebSocketServer('0.0.0.0');
$server->run();
