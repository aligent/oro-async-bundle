<?php

namespace Aligent\AsyncEventsBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class AligentAsyncEventsBundleInstaller implements Installation
{
    /**
     * {@inheritdoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_0';
    }

    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createAligentFailedJobTable($schema);
    }

    /**
     * Create aligent_failed_job table
     *
     * @param Schema $schema
     */
    protected function createAligentFailedJobTable(Schema $schema)
    {
        if (!$schema->hasTable('aligent_failed_job')) {
            $table = $schema->createTable('aligent_failed_job');
            $table->addColumn('id', 'integer', ['autoincrement' => true]);
            $table->addColumn('topic', 'string', ['length' => 255]);
            $table->addColumn('body', 'json_array', ['comment' => '(DC2Type:json_array)']);
            $table->addColumn('exception', 'text', ['notnull' => false, 'length' => 255]);
            $table->addColumn('trace', 'text', ['notnull' => false]);
            $table->addColumn('created_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
            $table->addColumn('updated_at', 'datetime', ['comment' => '(DC2Type:datetime)']);
            $table->addColumn('wh_entity_class', 'string', ['notnull' => false, 'length' => 255]);
            $table->addColumn('wh_event', 'string', ['notnull' => false, 'length' => 16]);
            $table->addColumn('wh_method', 'string', ['notnull' => false, 'length' => 16]);
            $table->addColumn('wh_headers', 'json', ['notnull' => false]);
            $table->addColumn('wh_api_url', 'string', ['notnull' => false]);
            $table->addColumn('wh_api_key', 'string', ['notnull' => false]);
            $table->addColumn('wh_api_user', 'string', ['notnull' => false]);
            $table->setPrimaryKey(['id']);
        }
    }
}
