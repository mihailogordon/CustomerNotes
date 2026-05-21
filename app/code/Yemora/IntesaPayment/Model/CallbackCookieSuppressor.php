<?php

declare(strict_types=1);

namespace Yemora\IntesaPayment\Model;

class CallbackCookieSuppressor
{
    public function suppressResponseCookies(): void
    {
        if (headers_sent()) {
            return;
        }

        header_remove('Set-Cookie');
    }
}
