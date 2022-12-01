<?php

namespace PayMaya\Payment\Gateway;

class Webhooks
{
    const PAYMENT_SUCCESS = 'paymaya_payment_success_webhook';
    const PAYMENT_FAILED = 'paymaya_payment_failed_webhook';

    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->logger = $logger;
        $this->request = $request;
        $this->eventManager = $eventManager;
    }

    public function dispatchEvent($eventType)
    {
        try
        {
            if ($this->request->getMethod() == 'GET')
                throw new \Exception("Webhooks are working correctly!", 200);

            // Retrieve the request's body and parse it as JSON
            $body = $this->request->getContent();

            $payload = json_decode($body, true);

            $this->eventManager->dispatch(
                $eventType,
                array(
                    'data' => $payload
                )
            );

            $this->logger->info("200 OK");
        }
        catch (\Exception $e)
        {
            $this->logger->error($e->getMessage());
        }
    }

    // When multiple events arrive at the same time, lock the current process so that we don't get DB deadlocks
    // Works similar to a queuing system, but is real time rather than cron-based
    public function lock()
    {
        $wait = 70; // seconds to wait for lock
        $sleep = 2; // poll every X seconds
        do
        {
            $lock = $this->cache->load("paymaya_payment_webhooks_lock");
            if ($lock)
            {
                sleep($sleep);
                $wait -= $sleep;
            }

        } while ($lock && $wait > 0);

        $this->cache->save(1, "paymaya_payment_webhooks_lock", array(), $lifetime = 60);
    }

    public function unlock()
    {
        $this->cache->remove("paymaya_payment_webhooks_lock");
    }
}
