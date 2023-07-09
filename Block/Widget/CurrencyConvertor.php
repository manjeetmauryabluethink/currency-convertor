<?php 

namespace Bluethinkinc\CurrencyConvertor\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Bluethinkinc\CurrencyConvertor\Service\ApiClient;

class CurrencyConvertor extends Template implements BlockInterface 
{
    protected $_template = "widget/currencyconvertor.phtml";
    
     /**
     * @var ApiClient
     */
    private $apiClient;

    public function __construct(
        Context $context,
        ApiClient $apiClient,
        array $data = []
    ) {
        $this->apiClient = $apiClient;
        parent::__construct($context, $data);
    }

    public function getRequestData()
    {
        return $this->apiClient->execute();
    }
}
