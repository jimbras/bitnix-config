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

namespace Bitnix\Config\Reader;

/**
 * @version 0.1.0
 */
final class JsonReader extends AbstractReader {

    /**
     * @param string $file
     * @return mixed
     * @throws \Throwable
     */
    protected function process(string $file) {
        return \json_decode(
            \file_get_contents($file),
            true,
            512,
            \JSON_THROW_ON_ERROR
        );
    }
}
