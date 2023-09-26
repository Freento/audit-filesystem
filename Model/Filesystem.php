<?php

declare(strict_types=1);

namespace Freento\AuditFilesystem\Model;

use FilesystemIterator;
use Freento\AuditFilesystem\Api\FilesystemInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Filesystem\Driver\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use SplFileInfoFactory;

class Filesystem extends DataObject implements FilesystemInterface
{
    /**
     * @var File
     */
    private File $file;

    /**
     * @var FilesystemFactory
     */
    private FilesystemFactory $factory;

    /**
     * @var SplFileInfoFactory
     */
    private SplFileInfoFactory $fileInfoFactory;

    /**
     * Please, do not call this property directly in class methods, use private getter instead
     *
     * @var SplFileInfo|null
     */
    private ?SplFileInfo $fileInfo;

    /**
     * Filesystem constructor.
     *
     * @param File $file
     * @param FilesystemFactory $factory
     * @param SplFileInfoFactory $fileInfoFactory
     */
    public function __construct(File $file, FilesystemFactory $factory, SplFileInfoFactory $fileInfoFactory)
    {
        parent::__construct();
        $this->file = $file;
        $this->factory = $factory;
        $this->fileInfoFactory = $fileInfoFactory;
    }

    /**
     * Returns SplFileInfo object
     *
     * @return SplFileInfo|null
     */
    private function getFileInfo(): ?SplFileInfo
    {
        if (!$this->hasFullPath()) {
            $this->fileInfo = null;
        } elseif (empty($this->fileInfo)) {
            // $filename is from PHP 8, $file_name is from PHP 7
            $this->fileInfo = $this->fileInfoFactory->create([
                'file_name' => $this->getFullPath(),
                'filename' => $this->getFullPath()
            ]);
        }

        return $this->fileInfo;
    }

    /**
     * @inheritdoc
     */
    public function isDirectory(): bool
    {
        $splFileInfo = $this->getFileInfo();
        return !empty($splFileInfo) && $splFileInfo->isDir();
    }

    /**
     * @inheritdoc
     */
    public function isLink(): bool
    {
        $splFileInfo = $this->getFileInfo();
        return !empty($splFileInfo) && $splFileInfo->isLink();
    }

    /**
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        $splFileInfo = $this->getFileInfo();
        return !empty($splFileInfo) && $splFileInfo->isReadable();
    }

    /**
     * @inheritdoc
     */
    public function getFilesCount(): ?int
    {
        if (!$this->hasFullPath() || !$this->isDirectory()) {
            return null;
        }

        if (!$this->hasFilesCount()) {
            $filesCount = 0;
            $recursiveDirectoryIterator = new RecursiveDirectoryIterator(
                $this->getFullPath(),
                FilesystemIterator::SKIP_DOTS
            );
            foreach (new RecursiveIteratorIterator($recursiveDirectoryIterator) as $ignored) {
                $filesCount++;
            }

            $this->setData('files_count', $filesCount);
        }

        return $this->getData('files_count');
    }

    /**
     * @inheritdoc
     */
    public function getTotalSize(): ?float
    {
        if (!$this->hasFullPath()) {
            return null;
        }

        if (!$this->hasTotalSize()) {
            if (!$this->getFileInfo()->isReadable()) {
                return null;
            }

            $sizeInBytes = $this->getFileInfo()->getSize();
            if ($this->isDirectory()) {
                $recursiveIteratorIterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                    $this->getFullPath(),
                    FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS
                ), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($recursiveIteratorIterator as $object) {
                    $sizeInBytes += $object->isReadable() ? $object->getSize() : 0;
                }
            }

            $this->setTotalSize($sizeInBytes / 1024 / 1024);
        }

        return $this->getData('total_size');
    }

    /**
     * @inheritdoc
     */
    public function generateChildren(): void
    {
        if (!$this->hasFullPath() || !$this->isDirectory()) {
            return;
        }

        $children = [];
        foreach ($this->file->readDirectory($this->getFullPath()) as $path) {
            $children[] = $this->factory->create()->setFullPath($path);
        }

        $this->setChildren($children);
    }

    /**
     * @inheritDoc
     */
    public function getFullPath(): string
    {
        return $this->getData('full_path');
    }

    /**
     * @inheritdoc
     */
    public function getBaseName(): string
    {
        $splFileInfo = $this->getFileInfo();
        return empty($splFileInfo) ? '' : $splFileInfo->getBasename();
    }

    /**
     * @inheritdoc
     */
    public function getLinkTarget(): ?string
    {
        $splFileInfo = $this->getFileInfo();
        $target = $splFileInfo->getLinkTarget();
        return empty($splFileInfo) || $target === false ? null : $target;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): ?array
    {
        return $this->getData('children');
    }
}
