<?php

namespace PayMaya\Payment\Logger;

use Magento\Framework\Filesystem\DriverInterface;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $filePath = BP . DIRECTORY_SEPARATOR . 'var/log/';
    protected $fileNamePrefix = 'maya-log';

    public function __construct(
        DriverInterface $filesystem
    ) {
        $this->filesystem = $filesystem;

        $fileName = $this->fileNamePrefix . '-' . date('Y-m-d') . '.log';

        parent::__construct(
            $filesystem,
            $this->filePath,
            $fileName,
        );
    }
}
