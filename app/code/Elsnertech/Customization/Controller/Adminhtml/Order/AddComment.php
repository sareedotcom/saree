<?php

namespace Elsnertech\Customization\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface;
use Elsnertech\Customization\Helper\Email;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Order
{
    
    const ADMIN_RESOURCE = 'Magento_Sales::comment';

    protected $_coreRegistry = null;

    protected $_fileFactory;

    protected $_translateInline;

    protected $resultPageFactory;

    protected $resultJsonFactory;

    protected $resultLayoutFactory;

    protected $resultRawFactory;

    protected $orderManagement;

    protected $orderRepository;

    protected $logger;

    protected $authSession;

    public function __construct(
        Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        OrderManagementInterface $orderManagement,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger,
        \Magento\Backend\Model\Auth\Session $authSession,
        Email $helperEmail
    ) {
        $this->authSession = $authSession;
        $this->helperEmail = $helperEmail;
        parent::__construct($context, $coreRegistry,$fileFactory,$translateInline,$resultPageFactory,$resultJsonFactory,$resultLayoutFactory,$resultRawFactory,$orderManagement,$orderRepository,$logger);
    }

    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            try {
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info('Your hiiiiii');
                $data = $this->getRequest()->getPost('history');
                $adminUser = $this->getRequest()->getPost('adminUser');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $username = $this->authSession->getUser()->getUsername();
                $append = " <b>(By : ".$username.")</b>";

                $history = $order->addStatusHistoryComment($data['comment'].$append, $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));

                $order->save();
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

                $orderCommentSender->send($order, $notify, $comment);

                if(isset($adminUser)){
                    foreach ($adminUser AS $value) {
                        $this->helperEmail->sendEmail($data['comment'], $username, $order->getIncrementId(), $value);
                    }
                }

                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}