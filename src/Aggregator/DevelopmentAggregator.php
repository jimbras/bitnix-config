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

use Bitnix\Config\Aggregator,
    Bitnix\Config\IOException;

/**
 * @version 0.1.0
 */
final class DevelopmentAggregator implements Aggregator {

    use AggregatorSupport;

    private const METADATA = '%s%s.%s.metadata';

    /**
     * @param string $file
     * @return bool
     */
    public function fresh(string $file) : bool {
        if (!\is_file($file)) {
            return false;
        }

        $data = $this->metadata($file);

        if (!$data) {
            return false;
        }

        foreach ($data as $file => $stamp) {
            if (!\is_file($file) || \filemtime($file) !== $stamp) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $file
     * @return array
     */
    private function metadata(string $file) : array {
        $metafile = $this->metafile($file);

        if (\is_file($metafile)
            && \is_readable($metafile)
            && \is_array($metadata = include $metafile)) {
            return $metadata;
        }

        return [];
    }

    /**
     * @param string $file
     * @return string
     */
    private function metafile(string $file) : string {
        return \sprintf(
            self::METADATA,
                \dirname($file),
                \DIRECTORY_SEPARATOR,
                \basename($file)
        );
    }

    /**
     * @param string $file
     * @return string
     * @throws IOException
     */
    private function resolve(string $file) : string {
        if (\is_file($file) && \is_readable($file)) {
            return \realpath($file);
        }

        throw new IOException(\sprintf(
            'Unable to find or read aggregator tracking file "%s"', $file
        ));
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

        $metafile = $this->metafile(\realpath($file));

        $metadata = [];
        foreach ($sources as $file) {
            $file = $this->resolve($file);
            $metadata[$file] = \filemtime($file);
        }

        $content = '<?php return ' . \var_export($metadata, true) . ';';
        @$this->store($metafile, $content);
    }

    /**
     * @param string $file
     * @throws IOException
     */
    public function destroy(string $file) : void {
        @$this->unlink($file);
        @$this->unlink($this->metafile($file));
    }

    /**
     * @return string
     */
    public function __toString() : string {
        return self::CLASS;
    }
}
