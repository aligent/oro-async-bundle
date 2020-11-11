<?php

namespace Aligent\AsyncBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\TextType;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Migration;

/**
 * Class UpdateToTextField
 *
 * @category  Aligent
 * @package   Aligent\AsyncBundle\Migrations\Schema\v1_1
 * @author    Adam Hall <adam.hall@aligent.com.au>
 * @copyright 2020 Aligent Consulting.
 * @link      http://www.aligent.com.au/
 */
class UpdateToTextField implements Migration
{
    /**
     * Modifies the given schema to apply necessary changes of a database
     * The given query bag can be used to apply additional SQL queries before and after schema changes
     *
     * @param Schema $schema
     * @param QueryBag $queries
     * @return void
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