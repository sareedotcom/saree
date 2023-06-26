<?php
namespace Elsnertech\Sms\Controller\Index;

use Magento\Framework\App\Action\Context;
use Elsnertech\Sms\Model\LoginotpmodelFactory;
use Magento\Framework\Controller\ResultFactory;
class AjaxSentOtpForLogin extends \Magento\Framework\App\Action\Action
{
	protected $_modelLoginOtpFactory;
	public $_helperdata;
	public function __construct(
		Context $context,
		LoginotpmodelFactory $modelLoginOtpFactory,
		\Elsnertech\Sms\Helper\Data $helperData

	){
		$this->_modelLoginOtpFactory = $modelLoginOtpFactory;
		$this->_helperdata = $helperData;
        parent::__construct($context);
    }
	public function execute()
    {
		$return = $this->_helperdata->sendLoginOTPCode($this->getRequest()->get('mobile'),$this->getRequest()->get('countrycode'));
		$resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
		$resultJson->setData($return);
		return $resultJson;

	}
}