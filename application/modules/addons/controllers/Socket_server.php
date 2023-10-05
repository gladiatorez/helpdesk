<?php (defined('BASEPATH')) OR exit('No direct script access allowed');

/**
 * Class Socket_server
 *
 */
class Socket_server extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $io = new \PHPSocketIO\SocketIO(20401, [
//            'ssl' => array(
//                'local_cert'  => APPPATH . '/../ssl/STAR_kallagroup_co_id.crt',
//                'local_pk'    => APPPATH . '/../ssl/commercial.key',
//                'verify_peer' => false,
////                'crypto_method' => STREAM_CRYPTO_METHOD_TLS_SERVER
//            )
        ]);
        $io->on('workerStart', function() use ($io) {
            $inner_worker = new \Workerman\Worker('text://0.0.0.0:20410');
            $inner_worker->onMessage = function($connection, $data) use ($io)  {
                $event_package = json_decode($data, true);
                $eventChannel = $event_package['channel'];
                $eventName = $event_package['event'];
                $eventData = $event_package['data'];
                
                $io->of('/'.$eventChannel)->emit($eventName, $eventData);
            };

            $inner_worker->listen();
        });

        $io->on('connection', function ($socket) use ($io)
        {
            
        });

        \Workerman\Worker::runAll();
    }
}