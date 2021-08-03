<?php


namespace PayMaya\Payment\Plugin;

class CsrfValidatorSkip
{
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getModuleName() == 'paymaya') {
            return; // Skip CSRF check
        }

        $proceed($request, $action);
    }
}
