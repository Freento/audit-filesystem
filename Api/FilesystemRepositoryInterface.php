<?php

declare(strict_types=1);

namespace Freento\AuditFilesystem\Api;

use Magento\Framework\Exception\FileSystemException;

interface FilesystemRepositoryInterface
{
    /**
     * Returns files and folders list
     *
     * @param string $dirFullPath
     * @param int $level
     * @return FilesystemInterface[]
     * @throws FileSystemException
     */
    public function getDirectoryList(string $dirFullPath, int $level): array;

    /**
     * Returns root directories and files
     *
     * @return FilesystemInterface[]
     * @throws FileSystemException
     */
    public function getRootDirList(): array;

    /**
     * Returns subdirectories list
     *
     * @param string $subdir
     * @return FilesystemInterface[]
     * @throws FileSystemException
     */
    public function getSubdirList(string $subdir): array;
}
