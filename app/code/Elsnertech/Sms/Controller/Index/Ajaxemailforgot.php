<?php
 
namespace Elsnertech\Sms\Controller\Index;

use Magento\Framework\App\Action\Context;
use Elsnertech\Sms\Model\ForgototpmodelFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;

class Ajaxemailforgot extends \Magento\Framework\App\Action\Action
{
   
    protected $_ForgototpmodelFactory;
	protected $_CustomerFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

	public function __construct(
		Context $context,
		ForgototpmodelFactory $ForgototpmodelFactory,		
		CustomerFactory $CustomerFactory,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper
	){
       $this->_ForgototpmodelFactory = $ForgototpmodelFactory;
	   $this->_CustomerFactory = $CustomerFactory;
       $this->session = $customerSession;
       $this->customerAccountManagement = $customerAccountManagement;
       $this->escaper = $escaper;
       parent::__construct($context);
    }
   public function execute()
    {
        $email = $this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                $response = false;
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $response = false;
            } catch (\Exception $exception) {
                $response = false;
                // $this->messageManager->addExceptionMessage(
                //     $exception,
                //     __('We\'re unable to send the password reset email.')
                // );
            }
            // $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            $response = true;

        } else {
            $response = false;
            // $this->messageManager->addErrorMessage(__('Please enter your email.'));
        }
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($email)
    {
        return __(
            'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            $this->escaper->escapeHtml($email)
        );
    }
}