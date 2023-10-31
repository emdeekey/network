<?php

namespace TheFox\Network;

use RuntimeException;
use Socket;

class BsdSocket extends AbstractSocket
{
    /**
     * BsdSocket constructor.
     */
    public function __construct()
    {
        $this->setHandle($this->create());
    }

    /**
     * Creates a new socket resource.
     * https://secure.php.net/manual/en/function.socket-create.php
     *
     * @return \Socket
     */
    public function create(): Socket
    {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            $errno = socket_last_error();
            throw new RuntimeException('socket_create: ' . socket_strerror($errno), $errno);
        }

        //$ret = socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);
        $ret = socket_get_option($socket, SOL_SOCKET, SO_KEEPALIVE);
        if ($ret === false) {
            $errno = socket_last_error($socket);
            throw new RuntimeException('socket_get_option SO_KEEPALIVE: ' . socket_strerror($errno), $errno);
        }

        //$ret = socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        $ret = socket_get_option($socket, SOL_SOCKET, SO_REUSEADDR);
        if ($ret === false) {
            $errno = socket_last_error($socket);
            throw new RuntimeException('socket_get_option SO_REUSEADDR: ' . socket_strerror($errno), $errno);
        }

        //$ret = socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        $ret = socket_get_option($socket, SOL_SOCKET, SO_RCVTIMEO);
        if ($ret === false) {
            $errno = socket_last_error($socket);
            throw new RuntimeException('socket_get_option SO_RCVTIMEO: ' . socket_strerror($errno), $errno);
        }

        //$ret = socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);
        $ret = socket_get_option($socket, SOL_SOCKET, SO_SNDTIMEO);
        if ($ret === false) {
            $errno = socket_last_error($socket);
            throw new RuntimeException('socket_get_option SO_SNDTIMEO: ' . socket_strerror($errno), $errno);
        }

        socket_set_nonblock($socket);

        return $socket;
    }

    /**
     * @param string $ip
     * @param int $port
     * @return bool
     */
    public function bind(string $ip, int $port): bool
    {
        return socket_bind($this->getHandle(), $ip, $port);
    }

    /**
     * @return bool
     */
    public function listen(): bool
    {
        return socket_listen($this->getHandle(), 0);
    }

    public function connect(string $ip, int $port)
    {
        socket_connect($this->getHandle(), $ip, $port);
    }


    public function accept(): ?self
    {
        $handle = socket_accept($this->getHandle());

        return $handle !== false
            ? (new self())->setHandle($handle)
            : null
            ;
    }

    /**
     * @param array $readHandles
     * @param array $writeHandles
     * @param array $exceptHandles
     * @return int
     */
    public function select(array &$readHandles, array &$writeHandles, array &$exceptHandles): int
    {
        return socket_select($readHandles, $writeHandles, $exceptHandles, 0);
    }

    /**
     * @param string $ip
     * @param int $port
     * @return bool
     */
    public function getPeerName(string &$ip, int &$port): bool
    {
        return socket_getpeername($this->getHandle(), $ip, $port);
    }

    /**
     * @return int
     */
    public function lastError(): int
    {
        return socket_last_error($this->getHandle());
    }

    /**
     * @return string
     */
    public function strError(): string
    {
        return socket_strerror(socket_last_error($this->getHandle()));
    }

    public function clearError()
    {
        socket_clear_error($this->getHandle());
    }

    /**
     * @return string
     */
    public function read(): string
    {
        return socket_read($this->getHandle(), 2048, PHP_BINARY_READ);
    }

    /**
     * @param string $data
     * @return int
     */
    public function write(string $data): int
    {
        return socket_write($this->getHandle(), $data, strlen($data));
    }

    public function shutdown()
    {
        socket_shutdown($this->getHandle(), 2);
    }

    public function close()
    {
        socket_close($this->getHandle());
    }
}
