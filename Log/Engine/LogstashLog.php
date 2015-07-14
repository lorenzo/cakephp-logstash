<?php
namespace App\Log\Engine;

use Cake\Log\Engine\BaseLog;
use Cake\Network\Exception\SocketException;
use Cake\Network\Http\Message;

class LogstashLog extends BaseLog
{

    public function __construct($options = [])
    {
        parent::__construct($options);

        if (empty($this->_config['timeout'])) {
            $this->_config['timeout'] = 5;
        }
    }


    /**
     * Configuration used in this logger engine
     *
     * - `levels` string or array, levels the engine is interested in
     * - `host` string, the connection string for the Logstash server
     * - `port` integer, the port number of the Logstash server
     * - `timeout` integer, number of seconds timeout - default to 5 seconds
     */
    protected $_config = array(
        'host' => null,
        'port' => null,
        'levels' => [],
        'timeout' => null,
    );

    /**
     * The resource for connecting to the logstash server
     *
     * @var resource
     */
    protected $_handle;


    /**
     * Opens a connection to logstash
     *
     * @param $host
     * @param $port
     * @param $timeout
     * @return resource
     */
    protected function _open($host, $port, $timeout)
    {
        $handle = pfsockopen($host, $port, $errNo, $errSt, $timeout);
    }

    /**
     * Writes a message to logstash
     *
     * @param string $level The severity level of the message being written.
     *    See Cake\Log\Log::$_levels for list of possible levels.
     * @param string $message The message you want to log.
     * @param array $context Additional information about the logged message
     * @return bool success of write.
     * @throws SocketException
     */
    public function log($level, $message, array $context = [])
    {
        if (!$this->_handle) {
            $this->_handle = $this->_open($this->_config['host'], $this->_config['port'], $this->_config['timeout']);
            if ($this->_handle === false) {
                throw new SocketException('Could not connect to logstash');
            }
        }

        return @fwrite($this->_handle, $message);
    }

    /**
     * Closes the connection to logstash
     *
     * @return void
     */
    protected function _close()
    {
        @fclose($this->_handle);
        $this->_handle = null;
    }

    /**
     * Flushes the buffer handle before destroying this object
     *
     * @return void
     */
    public function __destruct()
    {
        if ($this->_handle) {
            @fflush($this->_handle);
        }
    }
}