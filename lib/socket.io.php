<?php

/**
 * Class SocketIO
 * developed by psinetron (slybeaver)
 * Original Git: https://github.com/psinetron
 * Original dev web-site: http://slybeaver.ru
 *
 * Changes by: Rui Fernandes < bitkill @ github >
 * This Git: https://github.com/bitkill/php-socketio-broadcast
 */
class SocketIO
{
    /**
     * @todo : re-use connection
     *
     * @param null $host - host of socket server
     * @param null $port - port of socket server
     * @param string $address - addres of socket.io on socket server
     * @param string $transport - transport type
     *
     * @return bool
     */

    const TYPE_DISCONNECT = 0;
    const TYPE_CONNECT = 1;
    const TYPE_HEARTBEAT = 2;
    const TYPE_MESSAGE = 3;
    const TYPE_JSON_MESSAGE = 4;
    const TYPE_EVENT = 5;
    const TYPE_ACK = 6;
    const TYPE_ERROR = 7;
    const TYPE_NOOP = 8;


    protected
        $host = null,
        $port = null,
        $address = null,
        $transport = null,
        $socket = null;

    const TIMEOUT_SOCKET = 5;

    public function __construct(
        $host = '127.0.0.1',
        $port = 8080,
        $address = "/socket.io/?EIO=2",
        $transport = 'websocket'
    )
    {
        $this->host = $host;
        $this->port = $port;
        $this->address = $address;
        $this->transport = $transport;
    }

    private function connect()
    {
        $errno = '';
        $errstr = '';
        $this->socket = fsockopen(
            $this->host,
            $this->port,
            $errno,
            $errstr,
            self::TIMEOUT_SOCKET
        );
        if($this->socket === false){
            eZLog::write( "Fail socket connection: $errstr ($errno)", 'openpa_consiglio_push_emit.log', eZSys::varDirectory() . '/log' );
        }

        return $this->handshake();
    }

    public function close()
    {
        if (is_resource($this->socket)) {
            $this->send(self::TYPE_DISCONNECT);
            fclose($this->socket);

            return true;
        }

        return false;
    }

    private function handshake()
    {
        $key = $this->generateKey();

        $out = "GET $this->address&transport=$this->transport HTTP/1.1\r\n";
        $out .= "Host: http://$this->host:$this->port\r\n";
        $out .= "Upgrade: WebSocket\r\n";
        $out .= "Connection: Upgrade\r\n";
        $out .= "Sec-WebSocket-Key: $key\r\n";
        $out .= "Sec-WebSocket-Version: 13\r\n";
        $out .= "Origin: *\r\n\r\n";

        if ( !fwrite( $this->socket, $out ) )
        {
            $this->close();
            $this->socket = null;

            eZLog::write( 'Fail socket handshake', 'openpa_consiglio_push_emit.log', eZSys::varDirectory() . '/log' );

            return false;
        }
        // 101 switching protocols, see if echoes key
        $result = fread( $this->socket, 1000 );
        //var_dump($result);

        preg_match( '#Sec-WebSocket-Accept:\s(.*)$#mU', $result, $matches );
        $keyAccept = trim( $matches[1] );
        $expectedResonse = base64_encode(
            pack( 'H*', sha1( $key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11' ) )
        );

        return ( $keyAccept === $expectedResonse ) ? true : false;
    }

    public function emit($event, $args, $endpoint = null, $callback = null) {
        return $this->send(self::TYPE_JSON_MESSAGE, 2, $endpoint, json_encode(array( $event, $args, ) ));
    }

    public function send($type, $id, $endpoint = null, $message = null)
    {
        if ( $this->connect() )
        {
            if ( !is_int( $type ) || $type > 8 )
            {
                throw new \InvalidArgumentException( 'type parameter must inferior to 9.' );
            }

            $raw_message = $type.$id.$message;
            $payload = new Payload();
            $payload->setOpcode(Payload::OPCODE_TEXT)
                    ->setMask(true)
                    ->setPayload($raw_message);
            $encoded = $payload->encodePayload();


            fwrite( $this->socket, $encoded );

            // wait 100ms before closing connexion
            usleep( 100 * 1000 );

            eZLog::write( 'Sent ' . $raw_message, 'openpa_consiglio_push_emit.log', eZSys::varDirectory() . '/log' );

            //            fwrite($this->socket, $this->hybi10Encode('42["' . $action . '", "' . addslashes($data) . '"]'));
            //            fread($this->socket, 10);
            //            fclose($this->socket);
            return true;
        }
        else
        {
            return false;
        }
    }


    private function generateKey( $length = 16 )
    {
        $c = 0;
        $tmp = '';
        while ( $c++ * 16 < $length )
        {
            $tmp .= md5( mt_rand(), true );
        }

        return base64_encode( substr( $tmp, 0, $length ) );
    }


//    private function hybi10Encode( $payload, $type = 'text', $masked = true )
//    {
//        $frameHead = array();
//
//        $payloadLength = strlen( $payload );
//        switch ( $type )
//        {
//            case 'text':
//                $frameHead[0] = 129;
//                break;
//            case 'close':
//                $frameHead[0] = 136;
//                break;
//            case 'ping':
//                $frameHead[0] = 137;
//                break;
//            case 'pong':
//                $frameHead[0] = 138;
//                break;
//        }
//        if ( $payloadLength > 65535 )
//        {
//            $payloadLengthBin = str_split( sprintf( '%064b', $payloadLength ), 8 );
//            $frameHead[1] = ( $masked === true ) ? 255 : 127;
//            for ( $i = 0; $i < 8; $i++ )
//            {
//                $frameHead[$i + 2] = bindec( $payloadLengthBin[$i] );
//            }
//            if ( $frameHead[2] > 127 )
//            {
//                $this->close( 1004 );
//
//                return false;
//            }
//        }
//        elseif ( $payloadLength > 125 )
//        {
//            $payloadLengthBin = str_split( sprintf( '%016b', $payloadLength ), 8 );
//            $frameHead[1] = ( $masked === true ) ? 254 : 126;
//            $frameHead[2] = bindec( $payloadLengthBin[0] );
//            $frameHead[3] = bindec( $payloadLengthBin[1] );
//        }
//        else
//        {
//            $frameHead[1] = ( $masked === true ) ? $payloadLength + 128 : $payloadLength;
//        }
//        foreach ( array_keys( $frameHead ) as $i )
//        {
//            $frameHead[$i] = chr( $frameHead[$i] );
//        }
//        if ( $masked === true )
//        {
//            $mask = array();
//            for ( $i = 0; $i < 4; $i++ )
//            {
//                $mask[$i] = chr( rand( 0, 255 ) );
//            }
//
//            $frameHead = array_merge( $frameHead, $mask );
//        }
//        $frame = implode( '', $frameHead );
//
//        for ( $i = 0; $i < $payloadLength; $i++ )
//        {
//            $frame .= ( $masked === true ) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
//        }
//
//        return $frame;
//    }
}

class Payload
{
    const OPCODE_CONTINUE = 0x0;
    const OPCODE_TEXT = 0x1;
    const OPCODE_BINARY = 0x2;
    const OPCODE_NON_CONTROL_RESERVED_1 = 0x3;
    const OPCODE_NON_CONTROL_RESERVED_2 = 0x4;
    const OPCODE_NON_CONTROL_RESERVED_3 = 0x5;
    const OPCODE_NON_CONTROL_RESERVED_4 = 0x6;
    const OPCODE_NON_CONTROL_RESERVED_5 = 0x7;
    const OPCODE_CLOSE = 0x8;
    const OPCODE_PING = 0x9;
    const OPCODE_PONG = 0xA;
    const OPCODE_CONTROL_RESERVED_1 = 0xB;
    const OPCODE_CONTROL_RESERVED_2 = 0xC;
    const OPCODE_CONTROL_RESERVED_3 = 0xD;
    const OPCODE_CONTROL_RESERVED_4 = 0xE;
    const OPCODE_CONTROL_RESERVED_5 = 0xF;

    private $fin = 0x1;
    private $rsv1 = 0x0;
    private $rsv2 = 0x0;
    private $rsv3 = 0x0;
    private $opcode;
    private $mask = 0x0;
    private $maskKey;
    private $payload;

    public function setFin($fin) {
        $this->fin = $fin;

        return $this;
    }

    public function getFin() {
        return $this->fin;
    }

    public function setRsv1($rsv1) {
        $this->rsv1 = $rsv1;

        return $this;
    }

    public function getRsv1() {
        return $this->rsv1;
    }

    public function setRsv2($rsv2) {
        $this->rsv2 = $rsv2;

        return $this;
    }

    public function getRsv2() {
        return $this->rsv2;
    }

    public function setRsv3($rsv3) {
        $this->rsv3 = $rsv3;

        return $this;
    }

    public function getRsv3() {
        return $this->rsv3;
    }

    public function setOpcode($opcode) {
        $this->opcode = $opcode;

        return $this;
    }

    public function getOpcode() {
        return $this->opcode;
    }

    public function setMask($mask) {
        $this->mask = $mask;

        if ($this->mask == true) {
            $this->generateMaskKey();
        }

        return $this;
    }

    public function getMask() {
        return $this->mask;
    }

    public function getLength() {
        return strlen($this->getPayload());
    }

    public function setMaskKey($maskKey) {
        $this->maskKey = $maskKey;

        return $this;
    }

    public function getMaskKey() {
        return $this->maskKey;
    }

    public function setPayload($payload) {
        $this->payload = $payload;

        return $this;
    }

    public function getPayload() {
        return $this->payload;
    }

    public function generateMaskKey() {
        $this->setMaskKey($key = openssl_random_pseudo_bytes(4));

        return $key;
    }

    public function encodePayload()
    {
        $payload = (($this->getFin()) << 1) | ($this->getRsv1());
        $payload = (($payload) << 1) | ($this->getRsv2());
        $payload = (($payload) << 1) | ($this->getRsv3());
        $payload = (($payload) << 4) | ($this->getOpcode());
        $payload = (($payload) << 1) | ($this->getMask());

        if ($this->getLength() <= 125) {
            $payload = (($payload) << 7) | ($this->getLength());
            $payload = pack('n', $payload);
        } elseif ($this->getLength() <= 0xffff) {
            $payload = (($payload) << 7) | 126;
            $payload = pack('n', $payload).pack('n*', $this->getLength());
        } else {
            $payload = (($payload) << 7) | 127;
            $left = 0xffffffff00000000;
            $right = 0x00000000ffffffff;
            $l = ($this->getLength() & $left) >> 32;
            $r = $this->getLength() & $right;
            $payload = pack('n', $payload).pack('NN', $l, $r);
        }

        if ($this->getMask() == 0x1) {
            $payload .= $this->getMaskKey();
            $data = $this->maskData($this->getPayload(), $this->getMaskKey());
        } else {
            $data = $this->getPayload();
        }

        $payload = $payload.$data;

        return $payload;
    }

    public function maskData($data, $key) {
        $masked = '';

        for ($i = 0; $i < strlen($data); $i++) {
            $masked .= $data[$i] ^ $key[$i % 4];
        }

        return $masked;
    }
}
