<?php
namespace MatrixTest\Interfaces;

interface MatrixInterface
{
    public function __construct(array $matrix);

    public function getRank(): int;
}
