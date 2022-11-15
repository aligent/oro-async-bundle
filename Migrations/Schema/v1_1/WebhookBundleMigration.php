<?php

namespace Aligent\AsyncEventsBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\TextType;
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
        $table = $schema->getTable('aligent_failed_job');
        $table->changeColumn(
            'exception',
            [
                'type' => TextType::getType('text')
            ]
        );
    }
}
