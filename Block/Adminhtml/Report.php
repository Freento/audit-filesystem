<?php

declare(strict_types=1);

namespace Freento\AuditFilesystem\Block\Adminhtml;

use Freento\AuditFilesystem\Api\FilesystemInterface;
use Freento\AuditFilesystem\Api\FilesystemRepositoryInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Template;
use Magento\Framework\Exception\FileSystemException;

class Report extends Template
{
    private const SIZE_UNIT = ' MB';
    public const DECIMALS = 2;
    public const DECIMAL_SEPARATOR = '.';
    public const THOUSANDS_SEPARATOR = ' ';
    public const MIN_SIZE_DISPLAYED = 0.01;

    /**
     * @var FilesystemRepositoryInterface
     */
    private FilesystemRepositoryInterface $filesystemRepo;

    /**
     * @var string
     */
    protected $_template = 'Freento_AuditFilesystem::report.phtml';

    /**
     * @param Context $context
     * @param FilesystemRepositoryInterface $filesystemRepo
     */
    public function __construct(Context $context, FilesystemRepositoryInterface $filesystemRepo)
    {
        parent::__construct($context);
        $this->filesystemRepo = $filesystemRepo;
    }

    /**
     * Returns Filesystem repository
     *
     * @return FilesystemRepositoryInterface
     */
    public function getFilesystemRepository(): FilesystemRepositoryInterface
    {
        return $this->filesystemRepo;
    }

    /**
     * Formats size
     *
     * @param float|null $size
     * @return string
     */
    public function formatSize(?float $size): string
    {
        if ($size === null) {
            $sizeString = __('size unknown')->render();
        } else {
            $sizeString = ($size > self::MIN_SIZE_DISPLAYED
                ? number_format(
                    $size,
                    self::DECIMALS,
                    self::DECIMAL_SEPARATOR,
                    self::THOUSANDS_SEPARATOR
                )
                : number_format(
                    self::MIN_SIZE_DISPLAYED,
                    self::DECIMALS,
                    self::DECIMAL_SEPARATOR,
                    self::THOUSANDS_SEPARATOR
                ))
            . self::SIZE_UNIT;
        }

        return $sizeString;
    }

    /**
     * Renders list item with file/directory info
     *
     * @param FilesystemInterface $file
     * @return string
     * @throws FileSystemException
     */
    public function renderFile(FilesystemInterface $file): string
    {
        $html = '<li class="' . ($file->isDirectory() ? 'directory' : 'file')
            . (!$file->isReadable() ? ' incorrect' :  '') . '">'
            . '<span>' . $file->getBaseName() . '</span>';

        if ($file->isLink()) {
            $html .= '<span> -> ' . $file->getLinkTarget() . '</span>';
        }

        $html .= ' <span>' . '(' . $this->formatSize($file->getTotalSize()) . ')' . '</span>' . '</li>';

        return $html;
    }
}
