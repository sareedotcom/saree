<?php
namespace Magento\Ccavenuepay\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

/**
 * Class DataFactory
 */
class DataFactory {

    const AREA_FRONTEND = 'frontend';
    const AREA_BACKEND = 'adminhtml';

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $helperMap = [
        self::AREA_FRONTEND => 'Magento\Ccavenuepay\Helper\Data',
        self::AREA_BACKEND => 'Magento\Ccavenuepay\Helper\Backend\Data'
    ];

    /**
     * Constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager) {
        $this->objectManager = $objectManager;
    }

    /**
     * Create data helper
     *
     * @param string $area
     * @return \Magento\Ccavenuepay\Helper\Backend\Data|\Magento\Ccavenuepay\Helper\Data
     * @throws LocalizedException
     */
    public function create($area) {
        if (!isset($this->helperMap[$area])) {
            throw new LocalizedException(__(sprintf('For this area <%s> no suitable helper', $area)));
        }

        return $this->objectManager->get($this->helperMap[$area]);
    }

}
