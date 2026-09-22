<?php
declare(strict_types=1);

final class ImageUploader
{
    public const MAX_IMAGES = 8;
    private const RELATIVE_DIRECTORY = 'uploads/menus/';
    private const TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    private const MAX_BYTES = 3145728;
    private const MAX_PIXELS = 6000;

    public static function files(string $key): array
    {
        $raw = $_FILES[$key] ?? null;
        if (!is_array($raw) || !is_array($raw['name'] ?? null)) {
            return [];
        }

        $files = [];
        foreach (array_keys($raw['name']) as $index) {
            $files[$index] = [
                'name' => (string) $raw['name'][$index],
                'tmp_name' => (string) $raw['tmp_name'][$index],
                'error' => (int) $raw['error'][$index],
                'size' => (int) $raw['size'][$index],
            ];
        }

        return $files;
    }

    public static function isEmpty(array $file): bool
    {
        return $file['error'] === UPLOAD_ERR_NO_FILE;
    }

    public static function validate(array $file): ?string
    {
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return 'L\'image est trop lourde (3 Mo maximum).';
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
            return 'Le téléversement de l\'image a échoué.';
        }
        if ($file['size'] > self::MAX_BYTES) {
            return 'L\'image est trop lourde (3 Mo maximum).';
        }

        $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $info = @getimagesize($file['tmp_name']);
        if (!isset(self::TYPES[$mime]) || $info === false || $info['mime'] !== $mime) {
            return 'Format non accepté : utilisez une image JPEG, PNG ou WebP.';
        }
        if ($info[0] > self::MAX_PIXELS || $info[1] > self::MAX_PIXELS) {
            return 'L\'image est trop grande (6000 pixels maximum par côté).';
        }

        return null;
    }

    public static function store(array $file): string
    {
        $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $name = bin2hex(random_bytes(8)) . '.' . self::TYPES[$mime];
        $directory = BASE_PATH . '/public/assets/' . self::RELATIVE_DIRECTORY;

        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new RuntimeException('Dossier de téléversement inaccessible.');
        }
        if (!move_uploaded_file($file['tmp_name'], $directory . $name)) {
            throw new RuntimeException('Impossible d\'enregistrer l\'image.');
        }

        return self::RELATIVE_DIRECTORY . $name;
    }

    public static function remove(string $relative): void
    {
        if (preg_match('#^' . preg_quote(self::RELATIVE_DIRECTORY, '#') . '[a-f0-9]{16}\.(jpg|png|webp)$#', $relative) !== 1) {
            return;
        }

        $path = BASE_PATH . '/public/assets/' . $relative;
        if (is_file($path)) {
            unlink($path);
        }
    }
}
