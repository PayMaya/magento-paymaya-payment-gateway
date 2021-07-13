<?php

namespace PayMayaNexGen\Payment\Gateway\Validator;

class AvailabilityValidator extends \Magento\Payment\Gateway\Validator\AbstractValidator
{
    /**
     * @inheritdoc
     */
    public function validate(array $validationSubject)
    {
        return $this->createResult(true);
    }
}
