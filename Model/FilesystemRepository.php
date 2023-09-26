<?php

declare(strict_types=1);

namespace Freento\AuditFilesystem\Model;

use Freento\AuditFilesystem\Api\FilesystemInterface;
use Freento\AuditFilesystem\Api\FilesystemRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Retrieve information about filesystem
 */
class FilesystemRepository implements FilesystemRepositoryInterface
{
    /**
     * @var FilesystemFactory
     */
    private FilesystemFactory $filesystemFactory;

    /**
     * @var DirectoryList
     */
    private DirectoryList $directoryList;

    /**
     * FilesystemRepository constructor.
     *
     * @param FilesystemFactory $filesystemFactory
     * @param DirectoryList $directoryList
     */
    public function __construct(
        FilesystemFactory $filesystemFactory,
        DirectoryList $directoryList
    ) {
        $this->filesystemFactory = $filesystemFactory;
        $this->directoryList = $directoryList;
    }

    /**
     * @inheritdoc
     */
    public function getDirectoryList(string $dirFullPath, int $level = 1): array
    {
        $filesystem = $this->filesystemFactory->create()->setFullPath($dirFullPath);

        // when the lowest nesting level is reached return empty children array
        if ($level <= 0) {
            return [$filesystem];
        }

        // else generate next level children
        $filesystem->generateChildren();

        $count = count($filesystem->getChildren());
        for ($i = 0; $i < $count; $i++) {
            $child = $filesystem->getChildren()[$i];

            // if a child is a file do nothing
            if (!$child->isDirectory()) {
                continue;
            }

            // otherwise get its children recursively
            $child->setChildren($this->getDirectoryList($child->getFullPath(), $level - 1)[0]->getChildren());
        }

        return [$filesystem];
    }

    /**
     * Returns sorted directory list
     *
     * @param string $path
     * @return FilesystemInterface[]
     * @throws FileSystemException
     */
    private function getSortedDirList(string $path): array
    {
        $list = $this->getDirectoryList($path)[0] ?? null;
        if ($list === null) {
            return [];
        }

        $children = $list->getChildren();
        usort(
            $children,
            function (FilesystemInterface $a, FilesystemInterface $b) {
                if ($a->isDirectory() === $b->isDirectory()) {
                    return strcasecmp($a->getFullPath(), $b->getFullPath());
                } elseif ($a->isDirectory()) {
                    return -1;
                } else {
                    return 1;
                }
            }
        );

        return $children;
    }

    /**
     * @inheritdoc
     */
    public function getRootDirList(): array
    {
        return $this->getSortedDirList($this->directoryList->getRoot());
    }

    /**
     * @inheritdoc
     */
    public function getSubdirList(string $subdir): array
    {
        return $this->getSortedDirList($this->directoryList->getRoot() . DIRECTORY_SEPARATOR . $subdir);
    }
}
