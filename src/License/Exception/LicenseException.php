<?php declare(strict_types=1);

namespace SwPlayground\License\Exception;

use Shopware\Core\Framework\HttpException;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\HttpFoundation\Response;

/**
 * @internal
 */
#[Package('customer-order')]
class LicenseException extends HttpException
{
    public const MISSING_X_SHOPWARE_TOKEN = 'LICENSE__MISSING_X_SHOPWARE_TOKEN';

    public static function missingXShopwareToken(): self
    {
        return new self(
            Response::HTTP_BAD_REQUEST,
            self::MISSING_X_SHOPWARE_TOKEN,
            'X-Shopware-Token not provided!'
        );
    }
}
