<?php

declare(strict_types=1);

namespace Freento\AuditFilesystem\Api;

use Magento\Framework\Exception\FileSystemException;

interface FilesystemInterface
{
    /**
     * Tells whether this item is a regular directory
     *
     * @throws FileSystemException
     */
    public function isDirectory(): bool;

    /**
     * Tells whether this item is a link
     *
     * @return bool
     */
    public function isLink(): bool;

    /**
     * Tells whether this item is readable
     *
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * Returns size of all files in this directory in megabytes
     *
     * @return float|null
     * @throws FileSystemException
     */
    public function getTotalSize(): ?float;

    /**
     * Returns count of files in directory.
     * NOTE! Method isn't used due to performance issues.
     * Large number of files sharply increases duration of report loading
     *
     * @return int|null
     * @throws FileSystemException
     */
    public function getFilesCount(): ?int;

    /**
     * Creates array of first-level subdirectories and files
     *
     * @return void
     * @throws FileSystemException
     */
    public function generateChildren(): void;

    /**
     * Returns file or folder name
     *
     * @return string
     */
    public function getFullPath(): string;

    /**
     * Returns base name
     *
     * @return string
     */
    public function getBaseName(): string;

    /**
     * Returns absolute path to symlink target or to current file if it isn't a link
     *
     * @return string|null
     */
    public function getLinkTarget(): ?string;

    /**
     * Returns children of this directory
     *
     * @return FilesystemInterface[]|null
     */
    public function getChildren(): ?array;
}
