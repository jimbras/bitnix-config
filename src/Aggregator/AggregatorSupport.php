<?php declare(strict_types=1);

/**
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as
 *  published by the Free Software Foundation, either version 3 of the
 *  License, or (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program. If not, see <https://www.gnu.org/licenses/agpl-3.0.txt>.
 */

namespace Bitnix\Config\Aggregator;

use Bitnix\Config\IOException;

/**
 * @version 0.1.0
 */
trait AggregatorSupport {

    /**
     * @var bool
     */
    private static ?bool $opcache = null;

    /**
     * @param string $file
     * @param string $data
     * @throws IOException
     */
    private function store(string $file, string $data) : void {

        $dir = \dirname($file);
        if (!\is_dir($dir) && !\mkdir($dir, 0755, true)) {
            throw new IOException(\sprintf(
                'Failed to create aggregator directory "%s": %s',
                    $dir,
                    \error_get_last()['message'] ?? 'unknown error'
            ));
        }

        $tmp = \tempnam($dir, \basename($file));
        if (false !== \file_put_contents($tmp, $data, \LOCK_EX) && \rename($tmp, $file)) {
            \chmod($file, 0644 & ~\umask());
            self::invalidate($file);
            return;
        }

        throw new IOException(\sprintf(
            'Failed to store aggregator file "%s": %s',
                $file,
                \error_get_last()['message'] ?? 'unknown error'
        ));
    }

    /**
     * @param string $file
     * @throws IOException
     */
    private function unlink(string $file) : void {
        if (!\is_file($file)) {
            return;
        }

        if (!\unlink($file)) {
            throw new IOException(\sprintf(
                'Unable to delete aggregator file "%s": %s',
                    $file,
                    \error_get_last()['message'] ?? 'unknown error'
            ));
        }

        self::invalidate($file);
    }

    /**
     * @param string $file
     */
    private static function invalidate(string $file) : void {
        if (null === self::$opcache) {
            self::$opcache = \function_exists('opcache_invalidate')
                && (bool) \ini_get('opcache.enable');
        }
        if (self::$opcache) {
            \opcache_invalidate($file, true);
        }
    }

}
