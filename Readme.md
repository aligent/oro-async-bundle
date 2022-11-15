Aligent Async Events Bundle
---
This bundle provides:

1. An Abstract Processor class that will catch RetryableExceptions and after 3 (default) retries place
the failed jobs into the database so they can be reviewed, fixed and then requeued.
   
2. Webhook integration

Installation Instructions
-------------------------
1. Install this module via Composer

        composer require aligent/async-bundle

1. Clear cache

        php bin/console cache:clear

1. Run Migrations

        php bin/console oro:migration:load --force

Usage
-------
Make sure your class extends the AbstractRetryableProcessor and perform your normal job processing in the execute function.

```
class TestJobProcessor extends AbstractRetryableProcessor
{
    /**
     * @param MessageInterface $message
     * @return string
     * @throws RetryableException
     */
    public function execute(MessageInterface $message)
    {
        $body = JSON::decode($message->getBody());
        
        try {
            // Process the job here
            $this->importSomeEntities($body);
        } catch (\Exception $e) {
            throw new RetryableException(
                'This job failed and needs a retry',
                0,
                $e
            );
        }
    }
}
```

By default the AbstractRetryableProcessor will fetch the passed exception from the RetryableException and store the trace.
However if you aren't catching an exception you can just use the RetryableException and it will store the trace and message generated at this point.

```
class TestJobProcessor extends AbstractRetryableProcessor
{
    /**
     * @param MessageInterface $message
     * @return string
     * @throws RetryableException
     */
    public function execute(MessageInterface $message)
    {
        $body = JSON::decode($message->getBody());
        
        // Process the job here
        $success = $this->importSomeEntities($body);
        
        if (!$success) {
            throw new RetryableException('This job failed and needs a retry');
        }
    }
}
```

You can also increase or decrease the amount of retries by overriding `const MAX_RETRIES = 3;` in your own class.

Set up Transport for Webhooks
-----------
Go to the "System -> Integrations -> Manage integrations" and click "Create Integration". Select "Webhook" as the integration type and fill all required fields.

To Enable set status as Active.

![Webhook Integration Form](Resources/doc/images/webhook-integration.png?raw=true "Webhook Integration Form")

Once complete you must now receive webhook events to requested URL.

Dispatch webhook events
-----------

1. EntityEventListener listens to any create/update/delete events;
   check if relevant webhook transport exists and active;
   if yes, pushes message to the queue

```
Aligent\AsyncEventsBundle\EventListener\EntityEventListener:
lazy: true
arguments:
- '@Aligent\AsyncEventsBundle\Provider\WebhookConfigProvider'
- '@oro_message_queue.client.message_producer'
- '@logger'
tags:
- { name: doctrine.event_listener, event: onFlush }
- { name: doctrine.event_listener, event: postFlush }
```

2. WebhookEntityProcessor builds a payload and send a webhook event in response to the queued message.

```
Aligent\AsyncEventsBundle\Async\WebhookEntityProcessor:
parent: Aligent\AsyncEventsBundle\Async\AbstractRetryableProcessor
calls:
- [setConfigProvider, ['@Aligent\AsyncEventsBundle\Provider\WebhookConfigProvider']]
- [setTransport, ['@Aligent\AsyncEventsBundle\Integration\WebhookTransport']]
- [setSerializer, ['@oro_importexport.serializer']]
tags:
- { name: 'oro_message_queue.client.message_processor' }
```

3. If you need to enrich payload data (ex., add addreses for the customer user),
 you can use OroImportExportBundle and add some custom normalizers to add/transform payload data.

Support
-------
If you have any issues with this bundle, please create a [GitHub issue](https://github.com/aligent/oro-async-bundle/issues).

Contribution
------------
Any contribution is highly appreciated. The best way to contribute code is to open a [pull request on GitHub](https://help.github.com/articles/using-pull-requests).

Developer
---------
Adam Hall <adam.hall@aligent.com.au>

Licence
-------
[MIT](https://opensource.org/licenses/mit)