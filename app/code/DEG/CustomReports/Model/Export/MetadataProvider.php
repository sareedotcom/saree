<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Form Base for Magento 2
 */

namespace DEG\CustomReports\Model\Export;

use Amasty\Customform\Model\ResourceModel\Answer\Grid\Collection;
use Magento\Framework\Api\Search\DocumentInterface;

class MetadataProvider extends \Amasty\Customform\Model\Export\MetadataProvider
{
    /**
     * @var array
     */
    protected $columns;

    /**
     * @param Collection $collection
     * @return array|null
     */
    public function getMainTableColumns(Collection $collection)
    {
        if ($this->columns === null) {
            $this->columns = [];
            $schema = $collection->getConnection()->describeTable($collection->getMainTable());
            foreach ($schema as $column) {
                $this->columns[] = $column['COLUMN_NAME'];
            }
        }

        return $this->columns;
    }

    /**
     * @param Collection $collection
     * @return array|null
     */
    public function getMainTableHeaders(Collection $collection)
    {
        $headers = $this->getMainTableColumns($collection);
        foreach ($headers as $key => $header) {
            $headers[$key] = ucwords(str_replace('_', ' ', $header));
        }

        return $headers;
    }

    /**
     * Returns row data
     *
     * @param DocumentInterface $document
     * @param array $fields
     * @param array $options
     * @return array
     */
    public function getRowData(DocumentInterface $document, $fields, $options): array
    {
        $row = \Magento\Ui\Model\Export\MetadataProvider::getRowData($document, $fields, $options);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $serializedData = $objectManager->get('Magento\Framework\Serialize\Serializer\Json');
        $jsonData = $serializedData->unserialize($row[6]);
        foreach($jsonData AS $key => $value){
            if($key == 'name'){
                $row[] = $value['value'];
            }
            else if($key == 'phone-number'){
                $row[] = $value['value'];
            }
            else if($key == 'country'){
                $row[] = $value['value'];
            }
            
        }
        $this->convertJsonData($row, $fields);

        return $row;
    }

    /**
     * @param $row
     * @param $fields
     */
    private function convertJsonData(&$row, $fields)
    {
        $position = array_search('response_json', $fields);
        if ($position && isset($row[$position]) && isset($this->data['serializer']) && $row[$position]) {
            $fields = $this->data['serializer']->unserialize($row[$position]);

            if (!$fields) {
                return;
            }

            $result = [];
            foreach ($fields as $field) {
                if (isset($field['label']) && isset($field['value'])) {
                    $result[] = [$field['label'] => $field['value']];
                }
            }

            // use json_encode for adding JSON_UNESCAPED_UNICODE - fix issue with language convertation
            $row[$position] = json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
}
