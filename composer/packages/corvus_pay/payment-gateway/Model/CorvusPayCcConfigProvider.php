<?php

namespace CorvusPay\PaymentGateway\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Asset\Source;
use Magento\Payment\Model\CcConfigProvider;

/**
 * Class CorvusPayCcConfigProvider provides card icons.
 */
class CorvusPayCcConfigProvider
{
    /**
     * List of card types supported by CorvusPay.
     */
    const CARD_TYPES = ['amex', 'dina', 'diners', 'discover', 'maestro', 'master', 'visa'];

    /**
     * @var Repository
     */
    private $assetRepo;

    /**
     * @var Source
     */
    private $assetSource;

    /**
     * CorvusPayCcConfigProvider constructor.
     *
     * @param Repository $assetRepo
     * @param Source $assetSource
     */
    public function __construct(
        Repository $assetRepo,
        Source $assetSource
    ) {
        $this->assetRepo   = $assetRepo;
        $this->assetSource = $assetSource;
    }

    /**
     * Get icons for available payment methods
     *
     * @param CcConfigProvider $subject
     * @param array $icons
     *
     * @return array
     * @throws LocalizedException
     */
    public function afterGetIcons(CcConfigProvider $subject, $icons)
    {
        foreach (self::CARD_TYPES as $card) {
            if (!array_key_exists($card, $icons)) {
                $asset = $this->assetRepo->createAsset("CorvusPay_PaymentGateway::images/cards/{$card}.svg");
                $placeholder = $this->assetSource->findSource($asset);
                if ($placeholder) {
                    $icons[$card] = [
                        'url'    => $asset->getUrl(),
                        'width'  => 46,
                        'height' => 30
                    ];
                }
            }
        }

        return $icons;
    }
}
