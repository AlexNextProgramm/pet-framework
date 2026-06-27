<?php

namespace Pet\File;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Pet\File\Exception\FileException;
use Traversable;

class FileCollection implements Countable, IteratorAggregate
{
    private array $files = [];

    public function __construct(array $files = [])
    {
        foreach ($files as $file) {
            $this->add($file);
        }
    }

    public static function fromGlob(string $pattern, int $flags = 0): self
    {
        $paths = glob($pattern, $flags);

        if ($paths === false) {
            return new self();
        }

        return new self(array_map(fn(string $path) => new File($path), $paths));
    }

    public static function fromDirectory(string $directory, ?string $pattern = null): self
    {
        if (!is_dir($directory)) {
            return new self();
        }

        $files = [];
        $iterator = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);

        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isFile()) {
                if ($pattern === null || fnmatch($pattern, $fileInfo->getFilename())) {
                    $files[] = new File($fileInfo->getPathname());
                }
            }
        }

        return new self($files);
    }

    public static function fromUploadedFiles(array $files): self
    {
        $collection = new self();

        if (!isset($files['name'])) {
            return $collection;
        }

        if (is_string($files['name'])) {
            if (($files['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $collection->addUploadedFile($files);
            }

            return $collection;
        }

        $normalized = self::normalizeFilesArray($files);

        foreach ($normalized as $file) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
                $collection->addUploadedFile($file);
            }
        }

        return $collection;
    }

    public function add(File $file): self
    {
        $this->files[] = $file;

        return $this;
    }

    public function addUploadedFile(array $file): self
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw FileException::uploadError();
        }

        $this->files[] = File::fromUpload($file);

        return $this;
    }

    public function all(): array
    {
        return $this->files;
    }

    public function first(): ?File
    {
        return $this->files[0] ?? null;
    }

    public function last(): ?File
    {
        $count = count($this->files);

        return $count > 0 ? $this->files[$count - 1] : null;
    }

    public function get(int $index): ?File
    {
        return $this->files[$index] ?? null;
    }

    public function count(): int
    {
        return count($this->files);
    }

    public function isEmpty(): bool
    {
        return $this->files === [];
    }

    public function map(callable $callback): array
    {
        return array_map($callback, $this->files);
    }

    public function filter(callable $callback): self
    {
        return new self(array_filter($this->files, $callback));
    }

    public function each(callable $callback): self
    {
        foreach ($this->files as $file) {
            $callback($file);
        }

        return $this;
    }

    public function images(): self
    {
        return $this->filter(fn(File $file) => $file->isImage());
    }

    public function texts(): self
    {
        return $this->filter(fn(File $file) => $file->isText());
    }

    public function archives(): self
    {
        return $this->filter(fn(File $file) => $file->isArchive());
    }

    public function byExtension(string ...$extensions): self
    {
        $extensions = array_map(fn(string $ext) => strtolower(trim($ext, '. ')), $extensions);

        return $this->filter(fn(File $file) => in_array(strtolower($file->extension()), $extensions, true));
    }

    public function byMimeType(string ...$mimeTypes): self
    {
        return $this->filter(fn(File $file) => in_array($file->mimeType(), $mimeTypes, true));
    }

    public function largerThan(int $bytes): self
    {
        return $this->filter(fn(File $file) => $file->size() > $bytes);
    }

    public function smallerThan(int $bytes): self
    {
        return $this->filter(fn(File $file) => $file->size() < $bytes);
    }

    public function sortByName(bool $ascending = true): self
    {
        $files = $this->files;

        usort($files, function(File $a, File $b) use ($ascending) {
            $result = strnatcasecmp($a->name(), $b->name());

            return $ascending ? $result : -$result;
        });

        return new self($files);
    }

    public function sortBySize(bool $ascending = true): self
    {
        $files = $this->files;

        usort($files, function(File $a, File $b) use ($ascending) {
            $result = $a->size() <=> $b->size();

            return $ascending ? $result : -$result;
        });

        return new self($files);
    }

    public function sortByDate(bool $ascending = true): self
    {
        $files = $this->files;

        usort($files, function(File $a, File $b) use ($ascending) {
            $result = $a->lastModified() <=> $b->lastModified();

            return $ascending ? $result : -$result;
        });

        return new self($files);
    }

    public function sortByExtension(bool $ascending = true): self
    {
        $files = $this->files;

        usort($files, function(File $a, File $b) use ($ascending) {
            $result = strnatcasecmp($a->extension(), $b->extension());

            return $ascending ? $result : -$result;
        });

        return new self($files);
    }

    public function totalSize(): int
    {
        $total = 0;

        foreach ($this->files as $file) {
            $total += $file->size();
        }

        return $total;
    }

    public function totalSizeFormatted(): string
    {
        return FileManager::humanSize($this->totalSize());
    }

    public function names(): array
    {
        return array_map(fn(File $file) => $file->name(), $this->files);
    }

    public function paths(): array
    {
        return array_map(fn(File $file) => $file->path(), $this->files);
    }

    public function extensions(): array
    {
        return array_map(fn(File $file) => $file->extension(), $this->files);
    }

    public function toArray(): array
    {
        return array_map(fn(File $file) => $file->toArray(), $this->files);
    }

    public function copyTo(string $destinationDirectory): self
    {
        $copied = [];

        foreach ($this->files as $file) {
            $dest = $destinationDirectory . DIRECTORY_SEPARATOR . $file->name();
            $copied[] = $file->copy($dest);
        }

        return new self($copied);
    }

    public function moveTo(string $destinationDirectory): self
    {
        $moved = [];

        foreach ($this->files as $file) {
            $dest = $destinationDirectory . DIRECTORY_SEPARATOR . $file->name();
            $moved[] = $file->move($dest);
        }

        return new self($moved);
    }

    public function deleteAll(): bool
    {
        $success = true;

        foreach ($this->files as $file) {
            if (!$file->delete()) {
                $success = false;
            }
        }

        $this->files = [];

        return $success;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->files);
    }

    private static function normalizeFilesArray(array $files): array
    {
        $normalized = [];

        foreach ($files['name'] as $key => $name) {
            if (is_array($name)) {
                foreach ($name as $i => $nestedName) {
                    $normalized[] = [
                        'name' => $nestedName,
                        'type' => $files['type'][$key][$i] ?? '',
                        'tmp_name' => $files['tmp_name'][$key][$i] ?? '',
                        'error' => $files['error'][$key][$i] ?? UPLOAD_ERR_NO_FILE,
                        'size' => $files['size'][$key][$i] ?? 0,
                    ];
                }
            } else {
                $normalized[] = [
                    'name' => $name,
                    'type' => $files['type'][$key] ?? '',
                    'tmp_name' => $files['tmp_name'][$key] ?? '',
                    'error' => $files['error'][$key] ?? UPLOAD_ERR_NO_FILE,
                    'size' => $files['size'][$key] ?? 0,
                ];
            }
        }

        return $normalized;
    }
}
