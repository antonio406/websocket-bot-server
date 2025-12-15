<?php
/**
 * Servidor WebSocket simple usando sockets nativos de PHP
 * No requiere dependencias externas
 */

class WebSocketServer {
    private $clients = [];
    private $socket;
    
    // Respuestas del bot personalizadas sobre Antonio
    private $botResponses = [
        'saludos' => [
            '¡Hola! 👋 Soy el asistente virtual de Antonio. ¿En qué puedo ayudarte?',
            '¡Bienvenido! ¿Quieres saber más sobre Antonio Dimas?',
            '¡Hola! Pregúntame sobre la experiencia y proyectos de Antonio 😊'
        ],
        'quien_es' => [
            '🎓 Antonio Dimas Fernández es un Ingeniero en Desarrollo y Gestión de Software, Full Stack Developer especializado en React, Node.js, PHP, Python y Java.',
            'Antonio es de León, Guanajuato, México. Tiene experiencia en desarrollo frontend con React/Angular y backend con Node.js, PHP, Python y Spring Boot.',
            'Es un desarrollador apasionado que busca oportunidades de crecimiento profesional y está dispuesto a aprender cualquier tecnología necesaria.'
        ],
        'educacion' => [
            '🎓 Antonio tiene dos títulos:\n- Ingeniería en Desarrollo y Gestión de Software (2021-2023)\n- TSU en Tecnologías de la Información (2019-2021)\nAmbos de la Universidad Tecnológica Fidel Velázquez',
            'Se graduó de Ingeniero en Desarrollo y Gestión de Software en 2023, con especialización en desarrollo web full stack.'
        ],
        'habilidades_frontend' => [
            '💻 Frontend: Antonio domina HTML5, CSS3, JavaScript, React (intermedio-avanzado), Angular (intermedio), con experiencia en SPAs y diseños responsivos.',
            'Es experto en React y ha creado múltiples aplicaciones dinámicas. También trabaja con Angular para dashboards empresariales.',
            'Sus habilidades frontend incluyen diseño responsivo, animaciones CSS, y frameworks modernos como React y Angular.'
        ],
        'habilidades_backend' => [
            '⚙️ Backend: Domina Node.js, PHP, Python, Java y Spring Boot. Ha desarrollado APIs RESTful, microservicios y sistemas de gestión.',
            'Tiene experiencia avanzada en PHP para sistemas de gestión y CMS, Node.js para APIs y microservicios, y Python para automatización.',
            'Backend stack: Node.js + Express, PHP nativo, Python + Flask, Java + Spring Boot. Experiencia en arquitectura de microservicios.'
        ],
        'bases_datos' => [
            '🗄️ Bases de datos: Experto en MongoDB, PostgreSQL, MySQL y SQL Server. Experiencia en diseño de esquemas y optimización.',
            'Maneja tanto bases de datos SQL (PostgreSQL, MySQL, SQL Server) como NoSQL (MongoDB) para diferentes tipos de aplicaciones.',
            'Especializado en PostgreSQL y MySQL para sistemas transaccionales, y MongoDB para aplicaciones NoSQL.'
        ],
        'proyectos' => [
            '🚀 Proyectos destacados:\n1. Sistema de Citas (React + Node.js + MySQL)\n2. GJIMAR - Sitio corporativo (React + Vite)\n3. Baez Ópticos (HTML/CSS/JS)\n4. Este portafolio con WebSocket Chat!',
            'Ha desarrollado sistemas completos desde cero, incluyendo gestión de citas médicas con backend RESTful y frontend en React.',
            '¿Quieres ver sus proyectos? Visita la sección de proyectos o pregúntame por alguno específico.'
        ],
        'tecnologias' => [
            '🛠️ Stack completo: React, Angular, Node.js, PHP, Python, Java, Spring Boot, MongoDB, PostgreSQL, MySQL, Git, Docker, Postman, VS Code, Odoo.',
            'Domina 16+ tecnologías: desde HTML/CSS/JS hasta frameworks avanzados como Spring Boot y herramientas como Docker.',
            'Frontend: React, Angular, TypeScript\nBackend: Node.js, PHP, Python, Java\nBD: MongoDB, PostgreSQL, MySQL\nTools: Git, Docker, Postman'
        ],
        'ubicacion' => [
            '📍 Antonio está ubicado en León de los Aldama, Guanajuato, México.',
            'Vive en León, Guanajuato, una ciudad industrial importante en el Bajío mexicano.'
        ],
        'objetivo' => [
            '🎯 Antonio busca unirse a una empresa que ofrezca desarrollo profesional, donde pueda aprender continuamente y contribuir al crecimiento de la compañía.',
            'Su objetivo es crecer profesionalmente en un ambiente que valore el aprendizaje continuo y la innovación tecnológica.'
        ],
        'habilidades_blandas' => [
            '🌟 Habilidades blandas: Aprendizaje rápido, trabajo en equipo, resolución de problemas, comunicación efectiva, gestión del tiempo y adaptabilidad.',
            'Se destaca por su capacidad de aprender rápidamente nuevas tecnologías y adaptarse a cambios en los requerimientos.'
        ],
        'contacto' => [
            '📧 ¿Quieres contactar a Antonio? Ve a la sección de Contacto en su portafolio o envíale un mensaje.',
            'Puedes contactarlo a través del formulario de contacto en este sitio web.'
        ],
        'despedidas' => [
            '¡Hasta luego! 👋 No dudes en volver si tienes más preguntas sobre Antonio.',
            '¡Nos vemos! Espero haber ayudado a conocer mejor a Antonio 😊',
            '¡Adiós! Si quieres saber más, revisa el portafolio completo.'
        ],
        'agradecimientos' => [
            '¡De nada! 😊 Cualquier pregunta sobre Antonio, estoy aquí.',
            '¡Un placer ayudarte a conocer más sobre Antonio!',
            '¡Para eso estoy! 🤖 Pregunta lo que quieras sobre su experiencia.'
        ],
        'default' => [
            'Interesante pregunta. ¿Quieres saber sobre las habilidades, proyectos o experiencia de Antonio?',
            'Puedo contarte sobre la educación, tecnologías, proyectos o habilidades de Antonio. ¿Qué te interesa?',
            'Pregúntame sobre: educación, habilidades técnicas, proyectos, tecnologías que domina, o su objetivo profesional.'
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
        $key = '';
        
        foreach ($lines as $line) {
            if (stripos($line, 'Sec-WebSocket-Key') !== false) {
                $parts = explode(':', $line, 2);
                if (isset($parts[1])) {
                    $key = trim($parts[1]);
                    break;
                }
            }
        }
        
        if (empty($key)) {
            echo "⚠️ Error: No se encontró Sec-WebSocket-Key\n";
            return;
        }
        
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
        
        // Quién es Antonio / Información personal
        if (preg_match('/\b(quién es antonio|quien es antonio|antonio|dueño|portafolio|desarrollador|sobre ti|acerca de|about)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['quien_es']);
        }
        
        // Educación
        if (preg_match('/\b(educación|educacion|estudios|universidad|carrera|título|titulo|graduó|graduado)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['educacion']);
        }
        
        // Habilidades Frontend
        if (preg_match('/\b(frontend|front-end|react|angular|html|css|javascript|diseño)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['habilidades_frontend']);
        }
        
        // Habilidades Backend
        if (preg_match('/\b(backend|back-end|node|nodejs|php|python|java|spring|api|servidor)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['habilidades_backend']);
        }
        
        // Bases de datos
        if (preg_match('/\b(base de datos|bases de datos|mongodb|postgresql|mysql|sql|database)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['bases_datos']);
        }
        
        // Proyectos
        if (preg_match('/\b(proyecto|proyectos|trabajo|trabajos|portfolio|gjimar|baez|citas)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['proyectos']);
        }
        
        // Tecnologías / Stack
        if (preg_match('/\b(tecnologías|tecnologias|stack|herramientas|framework|lenguaje|domina|sabe)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['tecnologias']);
        }
        
        // Ubicación
        if (preg_match('/\b(ubicación|ubicacion|dónde|donde|ciudad|vive|león|guanajuato)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['ubicacion']);
        }
        
        // Objetivo profesional
        if (preg_match('/\b(objetivo|busca|quiere|meta|aspiración|aspiracion)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['objetivo']);
        }
        
        // Habilidades blandas
        if (preg_match('/\b(habilidades blandas|soft skills|trabajo en equipo|comunicación|comunicacion|adaptabilidad)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['habilidades_blandas']);
        }
        
        // Contacto
        if (preg_match('/\b(contacto|contactar|email|correo|mensaje|escribir)\b/u', $lowerMessage)) {
            return $this->getRandomResponse($this->botResponses['contacto']);
        }
        
        // Experiencia general
        if (preg_match('/\b(experiencia|años|tiempo|trabajado)\b/u', $lowerMessage)) {
            return 'Antonio tiene experiencia en desarrollo full stack desde 2021, con proyectos en React, Node.js, PHP, Python y Java. Ha trabajado tanto en frontend como backend.';
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
