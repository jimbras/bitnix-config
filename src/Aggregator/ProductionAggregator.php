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

use Bitnix\Config\Aggregator;

/**
 * @version 0.1.0
 */
final class ProductionAggregator implements Aggregator {

    use AggregatorSupport;

    /**
     * @param string $file
     * @return bool
     */
    public function fresh(string $file) : bool {
        return \is_file($file);
    }

    /**
     * @param string $file
     * @param string $content
     * @param string ...$sources
     * @throws IOException
     */
    public function aggregate(
        string $file, string $content, string ...$sources
    ) : void {
        @$this->store($file, $content);
    }

    /**
     * @param string $file
     * @throws IOException
     */
    public function destroy(string $file) : void {
        @$this->unlink($file);
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
