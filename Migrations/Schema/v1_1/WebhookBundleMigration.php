<?php

namespace Aligent\AsyncEventsBundle\Migrations\Schema\v1_2;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * Class WebhookBundleMigration
 *
 * @category  Aligent
 * @package   Aligent\WebhookBundle\Migrations\Schema\v1_0
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class WebhookBundleMigration implements Migration
{
    /**
     * @inheritDoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');

        if (!$table->hasColumn('wh_entity_class')) {
            $table->addColumn('wh_entity_class', 'string', ['notnull' => false, 'length' => 255]);
        }
        if (!$table->hasColumn('wh_event')) {
            $table->addColumn('wh_event', 'string', ['notnull' => false, 'length' => 16]);
        }
        if (!$table->hasColumn('wh_method')) {
            $table->addColumn('wh_method', 'string', ['notnull' => false, 'length' => 16]);
        }
        if (!$table->hasColumn('wh_headers')) {
            $table->addColumn('wh_headers', 'json', ['notnull' => false]);
        }
        if (!$table->hasColumn('wh_api_url')) {
            $table->addColumn('wh_api_url', 'string', ['notnull' => false]);
        }
        if (!$table->hasColumn('wh_api_key')) {
            $table->addColumn('wh_api_key', 'string', ['notnull' => false]);
        }
        if (!$table->hasColumn('wh_api_user')) {
            $table->addColumn('wh_api_user', 'string', ['notnull' => false]);
        }
    }
}
