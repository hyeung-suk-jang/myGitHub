<?php

namespace Core\Utilities;

class FileUpload
{
    private $uploadDir;
    private $allowedTypes = [];
    private $maxSize = 5242880; // 5MB
    private $errors = [];

    public function __construct($uploadDir = 'public/uploads')
    {
        $this->uploadDir = rtrim($uploadDir, '/');

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    public function setAllowedTypes($types)
    {
        $this->allowedTypes = $types;
        return $this;
    }

    public function setMaxSize($size)
    {
        $this->maxSize = $size;
        return $this;
    }

    public function upload($file)
    {
        if (!isset($file['error']) || is_array($file['error'])) {
            $this->errors[] = 'Invalid file parameters';
            return false;
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->errors[] = 'File size exceeds limit';
                return false;
            case UPLOAD_ERR_NO_FILE:
                $this->errors[] = 'No file uploaded';
                return false;
            default:
                $this->errors[] = 'Unknown upload error';
                return false;
        }

        if ($file['size'] > $this->maxSize) {
            $this->errors[] = 'File size exceeds maximum allowed size';
            return false;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!empty($this->allowedTypes) && !in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = 'File type not allowed';
            return false;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $this->generateFilename($extension);
        $destination = $this->uploadDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $this->errors[] = 'Failed to move uploaded file';
            return false;
        }

        return [
            'filename' => $filename,
            'path' => $destination,
            'size' => $file['size'],
            'mime_type' => $mimeType
        ];
    }

    public function uploadMultiple($files)
    {
        $uploaded = [];

        foreach ($files['name'] as $key => $name) {
            $file = [
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
                'tmp_name' => $files['tmp_name'][$key],
                'error' => $files['error'][$key],
                'size' => $files['size'][$key]
            ];

            $result = $this->upload($file);
            if ($result) {
                $uploaded[] = $result;
            }
        }

        return $uploaded;
    }

    private function generateFilename($extension)
    {
        return uniqid() . '_' . time() . '.' . $extension;
    }

    public function delete($filename)
    {
        $path = $this->uploadDir . '/' . $filename;

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    public function errors()
    {
        return $this->errors;
    }
}
