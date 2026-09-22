<?php
declare(strict_types=1);

final class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        $clean = static fn (string $value): string => trim((string) preg_replace('/[\r\n]+/', ' ', $value));

        error_log(sprintf("[MAIL simulé] à : %s | objet : %s\n%s", $clean($to), $clean($subject), $body));
    }
}
