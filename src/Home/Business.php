<?php

declare(strict_types=1);

namespace Couponetta\Home;

/** The party offering a deal. */
final class Business
{
    public function __construct(
        public readonly string $handle,
        public readonly string $name,
        public readonly string $town,
        public readonly string $category,
    ) {
        if (trim($handle) === '') {
            throw new \RuntimeException('a business carries a handle');
        }
        if (trim($name) === '') {
            throw new \RuntimeException('a business carries a name');
        }
        if (trim($category) === '') {
            throw new \RuntimeException('a business carries a category');
        }
    }
}
