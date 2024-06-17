<?php
/**
 * Logicrays
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Logicrays
 * @package     Logicrays_Base
 * @copyright   Copyright (c) Logicrays (https://www.logicrays.com/)
 */

declare(strict_types=1);

namespace Logicrays\Base\Helper;

use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\HTTP\Client\Curl;
use Logicrays\Base\Helper\Media as HelperMedia;

class Module
{
    const LR_STORE_MODULE_INFO_FILE = "https://store.logicrays.com/media/modules/AllModuleInfo.json";
    
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var File
     */
    private $filesystem;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Curl
     */
    protected $curl;

    /**
     * @var HelperMedia
     */
    protected $helperMedia;

    /**
     * Module constructor.
     * @param ModuleListInterface $moduleList
     * @param DataObjectFactory $dataObjectFactory
     * @param Reader $moduleReader
     * @param File $filesystem
     * @param Json $json
     * @param Curl $curl
     * @param HelperMedia $helperMedia
     */
    public function __construct(
        ModuleListInterface $moduleList,
        DataObjectFactory $dataObjectFactory,
        Reader $moduleReader,
        File $filesystem,
        Json $json,
        Curl $curl,
        HelperMedia $helperMedia
    ) {
        $this->moduleList = $moduleList;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->moduleReader = $moduleReader;
        $this->filesystem = $filesystem;
        $this->json = $json;
        $this->curl = $curl;
        $this->helperMedia = $helperMedia;
    }

    /**
     * Get all modules of Logicrays
     *
     * @return array
     */
    public function getAllModules()
    {
        $result = [];
        $modules = $this->moduleList->getNames();
        $dispatchResult = $this->dataObjectFactory->create()->setData($modules);
        $modules = $dispatchResult->toArray();
        sort($modules);
        foreach ($modules as $moduleName) {
            if (strstr($moduleName, 'Logicrays_') === false
                || $moduleName === 'Logicrays_Base'
            ) {
                continue;
            }
            $result[] = $moduleName;
        }

       

        return $result;
    }

    /**
     * Get installed module info by composer.json.
     *
     * @param string $moduleCode
     * @return array
     */
    public function getModuleInfo($moduleCode)
    {
        try {
            $dir = $this->moduleReader->getModuleDir('', $moduleCode);
            $file = $dir . '/composer.json';
            $string = $this->filesystem->fileGetContents($file);
            $result = $this->json->unserialize($string);

            if (!is_array($result)
                || !array_key_exists('version', $result)
                || !array_key_exists('description', $result)
            ) {
                return '';
            }
            return $result;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * For getting information of release magento extension.
     *
     * @return object
     */
    public function getExtensionJson()
    {
        $this->curl->post(self::LR_STORE_MODULE_INFO_FILE, []);
        $this->curl->setOption(CURLOPT_RETURNTRANSFER, true);
        $result = $this->curl->getBody();
        if ($result) {
            $obj = json_decode($result);
            if ($obj) { return $obj; }
        }

        return null;
    }

    /**
     * Convert extension info json object to array
     *
     * @return array
     */
    public function getExtensionJsonConvertArray()
    {
        $jsonFinalArray = array();
        $jsonData = $this->getExtensionJson();
        foreach($jsonData as $jsonKey => $json){
            $jsonFinalArray[$jsonKey]['module_name'] = $json->module_name;
            $jsonFinalArray[$jsonKey]['module_url'] = $json->module_url;
            $jsonFinalArray[$jsonKey]['latest_version'] = $json->latest_version;
            $jsonFinalArray[$jsonKey]['change_log'] = $json->change_log;
            $jsonFinalArray[$jsonKey]['user_guide'] = $json->user_guide;
        }

        return $jsonFinalArray;

    }

    /**
     * Match installed modules and extension info file modules
     *
     * @return array
     */
    public function getModuleExistingInComposer(){
        $lrAllModules = $this->getAllModules();
        $jsonFinalArray = $this->getExtensionJsonConvertArray();
        $finalModules = array();

        foreach($lrAllModules as $module){
            $moduleSuffix = explode('_', $module);
            $moduleInfo = $this->getModuleInfo($module);
            $currentVersion = "Not Found";
            if(array_key_exists('version',$moduleInfo)){
                $currentVersion = $moduleInfo['version'];
            }
            if (array_key_exists($moduleSuffix[1],$jsonFinalArray)){
                $finalModules[$moduleSuffix[1]]['module_name'] = $jsonFinalArray[$moduleSuffix[1]]['module_name'];
                $finalModules[$moduleSuffix[1]]['module_url'] = $jsonFinalArray[$moduleSuffix[1]]['module_url'];
                $finalModules[$moduleSuffix[1]]['current_version'] = $currentVersion;
                $finalModules[$moduleSuffix[1]]['latest_version'] = $jsonFinalArray[$moduleSuffix[1]]['latest_version'];
                $finalModules[$moduleSuffix[1]]['change_log'] = $jsonFinalArray[$moduleSuffix[1]]['change_log'];
                $finalModules[$moduleSuffix[1]]['user_guide'] = $jsonFinalArray[$moduleSuffix[1]]['user_guide'];
            }
        }

        return $finalModules;
        
    }
}
