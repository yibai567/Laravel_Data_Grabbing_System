<?php
namespace App;
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use App\Exceptions\AMQPException;

class AMQP {

    /**
     * Work Queue 模型
     */
    const MODEL_TYPE_WORKQUEUE = 'direct';

    /**
     * Publish/Subscribe 模型
     */
    const MODEL_TYPE_FANOUT = 'fanout';

    /**
     * Route 模型
     */
    const MODEL_TYPE_ROUTE = 'route';

    /**
     * AMQP实例列表
     * @var array
     */
    private static $__clients = array();

    /**
     * AMQPConnection实例
     * @var object
     */
    private $__connection;

    /**
     * AMQPChannel实例
     * @var object
     */
    private $__channel;

    /**
     * AMQPExchange实例
     * @var object
     */
    private $__exchange;

    /**
     * AMQPQueue实例
     * @var object
     */
    private $__queue;

    /**
     * 队列配置
     * @var array
     */
    private $__options;

    /**
     * 模型类型
     * @var integer
     */
    private $__type;

    /**
     * 模型和Exchange类型对应
     * @var array
     */
    private $__ex_type_map = array(
        self::MODEL_TYPE_WORKQUEUE => 'direct',
        self::MODEL_TYPE_FANOUT => 'fanout',
        self::MODEL_TYPE_ROUTE => 'route',
    );

    /**
     * getInstance
     * 获取Client实例
     *
     * @param array $options
     * @return object
     */
    public static function &getInstance($options) {

        $name = $options['name'];
        if (array_key_exists($name, self::$__clients)) {
            return self::$__clients[$name];
        }

        self::$__clients[$name] = new AMQP($options);
        return self::$__clients[$name];

    }

    private function __construct($options) {

        $this->__options = $options;
        $this->__type = $options['type'];
        try {
            $this->is_config_valid($options);

            if (!isset($options['server']['host'])) {
                $options['server']['host'] = config('rabbitmq.host');
            }
            if (!isset($options['server']['port'])) {
                $options['server']['port'] = config('rabbitmq.port');
            }
            if (!isset($options['server']['login'])) {
                $options['server']['login'] = config('rabbitmq.login');
            }
            if (!isset($options['server']['password'])) {
                $options['server']['password'] = config('rabbitmq.password');
            }
            $server = $options['server'];
            $this->__connection = new AMQPConnection($server['host'],
                                                     $server['port'],
                                                     $server['login'],
                                                     $server['password'],
                                                     $server['vhost']);

            $this->__channel = $this->__connection->channel();

            if ($options['qos']['size'] && $options['qos']['count']) {
                throw new AMQPException('qos size and count both bigger than 0');
            } else {
                // TODO:需要升级amqp扩展，已支持qos，当前版本有bug
                //$this->__channel->qos($options['qos']['size'], $options['qos']['count']);
            }
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::CLIENT_INIT_ERROR);
        }
    }

    /**
     * prepareExchange
     * 初始化Exchange
     *
     * @return void
     */
    public function prepareExchange() {

        try {
            $conf = $this->__options['exchange_config'];
            if (!$conf) {
                $conf = array();
            }

            if ($this->__exchange) {
                return true;
            } else {

                if ($conf['type']) {
                    $ex_type = $conf['type'];
                } else {
                    $ex_type = $this->__ex_type_map[$this->__type];
                }

                if (isset($conf['durable'])) {
                    $durable = $conf['durable'];
                } else {
                    $durable = true;
                }

                if (isset($conf['passive'])) {
                    $passive = $conf['passive'];
                } else {
                    $passive = false;
                }

                if (isset($conf['auto_delete'])) {
                    $auto_delete = $conf['auto_delete'];
                } else {
                    $auto_delete = false;
                }

                if (!$conf['arguments']) {
                    $arguments = null;
                } else {
                    $arguments = $conf['arguments'];
                }

                $this->__channel->exchange_declare($this->__options['exchange'],
                                                   $ex_type,
                                                   $passive,
                                                   $durable,
                                                   $auto_delete,
                                                   $internal = false,
                                                   $nowait = false,
                                                   $arguments,
                                                   $ticket = null);
                $this->_exchange_name = $this->__options['exchange'];
            }
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::EXCHANGE_INIT_ERROR);
        }
    }

    /**
     * prepareQueue
     * 初始化队列
     *
     * @return void
     */
    public function prepareQueue() {

        try {
            $conf = $this->__options['queue_config'];
            if (!$conf) {
                $conf = array();
            }

            if ($this->__queue) {
                return true;
            } else {
                if (isset($conf['durable'])) {
                    $durable = $conf['durable'];
                } else {
                    $durable = true;
                }

                if (isset($conf['passive'])) {
                    $passive = $conf['passive'];
                } else {
                    $passive = false;
                }

                if (isset($conf['exclusive'])) {
                    $exclusive = $conf['exclusive'];
                } else {
                    $exclusive = false;
                }

                if (isset($conf['auto_delete'])) {
                    $auto_delete = $conf['auto_delete'];
                } else {
                    $auto_delete = false;
                }

                if (!$conf['arguments']) {
                    $arguments = null;
                } else {
                    $arguments = $conf['arguments'];
                }
                $this->__channel->queue_declare($this->__options['queue'],
                                                $passive,
                                                $durable,
                                                $exclusive,
                                                $auto_delete,
                                                $nowait = false,
                                                $arguments,
                                                $ticket = null);
                $this->_queue_name = $this->__options['queue'];
            }
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::QUEUE_INIT_ERROR);
        }
    }

    /**
     * queueBind
     * 绑定队列和Exchange
     *
     * @return void
     */
    public function queueBind($routing_key = '') {

        try {
            if (!$this->_exchange_name) {
                throw new Exception('exchange is not declared');
            }
            if (!$this->_queue_name) {
                throw new Exception('queue is not declared');
            }

            if ($this->__type == self::MODEL_TYPE_ROUTE && $this->__options['queue_config']['routing_key']) {
                $routing_key = $this->__options['queue_config']['routing_key'];
            }
            if (!$routing_key) {
                $routing_key = $this->_queue_name;
            }
            $this->__channel->queue_bind($this->_queue_name,
                                         $this->_exchange_name,
                                         $routing_key);
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::QUEUE_BIND_ERROR);
        }
    }

    /**
     * publish
     * 向队列发送一条消息
     *
     * @param string $message
     * @param string $routing_key
     * @param array  $attributes
     *                  + content_type
     *                  + content_encoding
     *                  + message_id
     *                  + user_id
     *                  + app_id
     *                  + delivery_mode
     *                  + priority
     *                  + timestamp
     *                  + expiration  消息TTL(毫秒)
     *                  + type
     *                  + reply_to
     * @return boolean
     */
    public function publish($message, $routing_key = '', $attributes = array()) {

        try {
            if ($routing_key) {

            } else if ($this->__type == self::MODEL_TYPE_FANOUT) {
                $routing_key = '';
            } else if ($this->__type == self::MODEL_TYPE_WORKQUEUE) {
                $routing_key = $this->_queue_name;
            } else if ($this->__type == self::MODEL_TYPE_ROUTE) {
                $routing_key = $this->__options['queue_config']['routing_key'];
            } else {
                throw new AMQPException('model type error : ' . $this->__type);
            }

            $publish_attrs = array(
                'delivery_mode' => 2, // 告知RabbitMQ保留消息
            );
            $attributes = array_merge($attributes, $publish_attrs);
            $amqp_message = new AMQPMessage($message, $attributes);

            $this->__channel->basic_publish($amqp_message,
                                            $this->_exchange_name,
                                            $routing_key);
            return true;
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::MESSAGE_PUBLISH_ERROR);
        }
    }

    /**
     * getMessage
     * 从当前队列获取一条消息
     *
     * @param boolean $auto_ack
     * @return string
     */
    public function getMessage($auto_ack = false) {

        try {
            return $this->__channel->basic_get($this->_queue_name, $auto_ack);
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::MESSAGE_GET_ERROR);
        }
    }

    /**
     * ack
     * 通知队列已成功处理一条消息
     *
     * @param integer $delivery_tag
     * @return boolean
     */
    public function ack($delivery_tag) {

        // notify queue:this message has been consumed
        try {
            return $this->__channel->basic_ack($delivery_tag);
        } catch (Exception $e) {
            throw new AMQPException(
                $e->getMessage(), AMQPException::MESSAGE_ACK_ERROR);
        }
    }

    /**
     * nack
     * 通知队列一条消息未被成功处理
     *
     * @param integer $delivery_tag
     * @return boolean
     */
    public function nack($delivery_tag) {

        // return $this->__queue->nack($delivery_tag);
        return true;
    }

    /**
     * consume
     * 阻塞式处理队列消息
     *
     * @param callback $callback
     * @return void
     */
    public function consume($queue, $callback) {

        // $this->__channel->basic_consume($callback);
        $i = 1;
        $this->__channel->basic_qos(null, 1, null);
        $this->__channel->basic_consume($queue, '', false, false, false, false, $callback);
        while(count($this->__channel->callbacks)) {
            if ($i > 100) {
                exit();
            }
            $this->__channel->wait();
            $i++;
        }
    }

    public function close() {

        // destory connection
        try {
            $this->__channel->close();
            $this->__connection->close();
        } catch (Exception $e) {
            // todo 防止php错误 暂时未知产生异常的原因
        }
    }

    /**
     * is_config_valid
     * 检查Config结构是否合法
     *
     * @param array $config
     * @return boolean
     */
    public function is_config_valid($config) {

        $root_required = array('type', 'exchange', 'queue');
        if ($diff = array_diff($root_required, array_keys($config))) {
            throw new AMQPException(
                'config required fields not found: ' . implode(', ', $diff),
                AMQPException::CONFIG_NOT_VALID);
            return false;
        }

        if (!$config['exchange']) {
            throw new AMQPException(
                'exchange name required',
                AMQPException::CONFIG_NOT_VALID);
            return false;
        }

        if (!$config['queue']) {
            throw new AMQPException(
                'queue name required',
                AMQPException::CONFIG_NOT_VALID);
            return false;
        }

        if (!in_array($config['type'], array(self::MODEL_TYPE_WORKQUEUE, self::MODEL_TYPE_FANOUT, self::MODEL_TYPE_ROUTE))) {
            throw new AMQPException(
                'model type error',
                AMQPException::CONFIG_NOT_VALID);
            return false;
        }

        return true;
    }
}
