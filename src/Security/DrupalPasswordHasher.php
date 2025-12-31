<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

final class DrupalPasswordHasher implements PasswordHasherInterface
{
    const DRUPAL_MIN_HASH_COUNT = 7;
    const DRUPAL_MAX_HASH_COUNT = 30;
    const DRUPAL_HASH_LENGTH = 55;

    public function hash(#[\SensitiveParameter] string $plainPassword): string
    {
        throw new \LogicException('Cannot hash Drupal passwords, only verify existing hashes.');
    }

    public function verify(string $hashedPassword, #[\SensitiveParameter] string $plainPassword): bool
    {
        return $this->checkDrupalPassword($plainPassword, $hashedPassword);
    }

    public function needsRehash(string $hashedPassword): bool
    {
        return !str_starts_with($hashedPassword, '$argon2') && !str_starts_with($hashedPassword, '$2y$');
    }

    private function checkDrupalPassword(string $password, string $storedHash): bool
    {
        if (substr($storedHash, 0, 3) === '$S$') {
            $hash = $this->_password_crypt('sha512', $password, $storedHash);

            return $hash && $storedHash == $hash;
        }

        return md5($password) === $storedHash;
    }

    private function _password_crypt($algo, $password, $setting)
    {
        // Prevent DoS attacks by refusing to hash large passwords.
        if (strlen($password) > 512) {
            return FALSE;
        }
        // The first 12 characters of an existing hash are its setting string.
        $setting = substr($setting, 0, 12);
        if ($setting[0] != '$' || $setting[2] != '$') {
            return FALSE;
        }
        $count_log2 = $this->_password_get_count_log2($setting);
        // Hashes may be imported from elsewhere, so we allow != DRUPAL_HASH_COUNT
        if ($count_log2 < self::DRUPAL_MIN_HASH_COUNT || $count_log2 > self::DRUPAL_MAX_HASH_COUNT) {
            return FALSE;
        }
        $salt = substr($setting, 4, 8);
        // Hashes must have an 8 character salt.
        if (strlen($salt) != 8) {
            return FALSE;
        }
        // Convert the base 2 logarithm into an integer.
        $count = 1 << $count_log2;
        // We rely on the hash() function being available in PHP 5.2+.
        $hash = hash($algo, $salt . $password, TRUE);
        do {
            $hash = hash($algo, $hash . $password, TRUE);
        } while (--$count);
        $len = strlen($hash);
        $output = $setting . $this->_password_base64_encode($hash, $len);
        // _password_base64_encode() of a 16 byte MD5 will always be 22 characters.
        // _password_base64_encode() of a 64 byte sha512 will always be 86 characters.
        $expected = 12 + ceil(8 * $len / 6);
        return strlen($output) == $expected ? substr($output, 0, self::DRUPAL_HASH_LENGTH) : FALSE;
    }

    private function _password_get_count_log2(string $setting): int
    {
        $itoa64 = $this->_password_itoa64();
        return strpos($itoa64, $setting[3]);
    }

    private function _password_itoa64(): string
    {
        return './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    }

    private function _password_base64_encode($input, $count)
    {
        $output = '';
        $i = 0;
        $itoa64 = $this->_password_itoa64();
        do {
            $value = ord($input[$i++]);
            $output .= $itoa64[$value & 0x3f];
            if ($i < $count) {
                $value |= ord($input[$i]) << 8;
            }
            $output .= $itoa64[$value >> 6 & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            if ($i < $count) {
                $value |= ord($input[$i]) << 16;
            }
            $output .= $itoa64[$value >> 12 & 0x3f];
            if ($i++ >= $count) {
                break;
            }
            $output .= $itoa64[$value >> 18 & 0x3f];
        } while ($i < $count);
        return $output;
    }
}
