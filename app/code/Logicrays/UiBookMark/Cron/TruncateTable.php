<?php
namespace Logicrays\UiBookMark\Cron;
use Magento\Framework\App\ResourceConnection;
class TruncateTable
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;
    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }
    public function execute()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('ui_bookmark'); // Replace 'your_table_name' with the actual table name
        // Truncate the table
        $connection->truncateTable($tableName);
    }
}