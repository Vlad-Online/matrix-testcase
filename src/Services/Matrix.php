<?php

namespace MatrixTest\Services;

use MatrixTest\Interfaces\MatrixInterface;

class Matrix implements MatrixInterface
{
    protected array $matrix;
    protected int $rows, $columns, $minSize;

    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
        $this->rows = self::getRows($matrix);
        $this->columns = self::getColumns($matrix);
        // выбираем минимум
        $this->minSize = min($this->rows, $this->columns);
    }

    protected static function getRows(array $matrix): int
    {
        return count($matrix);
    }

    protected static function getColumns(array $matrix): int
    {
        return self::getRows($matrix) > 0 ? count($matrix[0]) : 0;
    }

    protected static function swapRows(array $matrix, int $i1, int $i2): array
    {
        $rows = self::getRows($matrix);
        if (($i1 >= $rows) || ($i2 >= $rows) || ($i1 == $i2)) return $matrix;
        $row = $matrix[$i1];
        $matrix[$i1] = $matrix[$i2];
        $matrix[$i2] = $row;
        return $matrix;
    }

    protected static function swapColumns(array $matrix, int $x1, int $x2): array
    {
        $width = self::getColumns($matrix);
        $height = self::getRows($matrix);
        if (($x1 >= $width) || ($x2 >= $width) || ($x1 == $x2)) return $matrix;
        $x = 0;
        while ($x < $height) {
            $tmp = $matrix[$x][$x1];
            $matrix[$x][$x1] = $matrix[$x][$x2];
            $matrix[$x][$x2] = $tmp;
            $x++;
        }
        return $matrix;
    }

    protected static function simulateIncrementFor(int $i, callable $checkCond, callable $func)
    {
        while ($checkCond($i)) {
            $func($i);
            $i++;
        }
    }

    public function getRank(): int
    {
        $this->transformMatrix();
        return $this->countRankSimpledForm();
    }

    protected function countRankSimpledForm(): int
    {
        // считаем сколько единичек на главной диагонали
        $cnt = 0;
        $i = 0;
        while (true) {
            if (!($i < $this->minSize)) {
                break;
            }
            if ($this->matrix[$i][$i] == 0) {
                break;
            } else {
                $cnt++;
            }
            $i++;
        }
        return $cnt;
    }

    /**
     * Поиск элемента с указанным значением
     * @param $what - элемент для поиска
     * @param bool $match - искать равный элемент или отличный от указанного
     * @param int $uI координаты если он есть
     * @param int $uJ координаты если он есть
     * @param int $startI
     * @param int $startJ
     * @return int == 0 - не найден, <> 0 - найден
     */
    protected function searchElement($what, bool $match, int &$uI, int &$uJ, int $startI, int $startJ): int
    {
        if ((!$this->rows) || (!$this->columns)) return 0;
        if (($startI >= $this->rows) || ($startJ >= $this->columns)) return 0;
        $i = $startI;
        while ($i < $this->rows) {
            $j = $startJ;
            while ($j < $this->columns) {
                if ($match) {
                    if ($this->matrix[$i][$j] == $what) {
                        $uI = $i;
                        $uJ = $j;
                        return 1;
                    }
                } else {
                    if ($this->matrix[$i][$j] != $what) {
                        $uI = $i;
                        $uJ = $j;
                        return 1;
                    }
                }
                $j++;
            }
            $i++;
        }
        return 0;
    }

    protected function transformMatrix()
    {
        if (!$this->rows || !$this->columns) {
            // Ранг пустой матрицы равен 0
            return;
        }

        $x = $y = 0;
        // цикл по всей главной диагонали
        self::simulateIncrementFor(0, fn($i) => $i < $this->minSize, function ($i) use ($x, $y) {
            // если элемент на диагонали равен 0, то ищем не нулевой элемент в матрице
            if ($this->matrix[$i][$i] == 0) {
                // если все элементы нулевые, прерываем цикл
                if (!$this->searchElement(0, false, $y, $x, $i, $i)) return;
                // меняем i-ую строку с y-ой
                if ($i != $y) {
                    $this->matrix = self::swapRows($this->matrix, $i, $y);
                }
                // меняем i-ый столбец с x-ым
                if ($i != $x) {
                    $this->matrix = self::swapColumns($this->matrix, $i, $x);
                }
                // таким образом, в m[i][i], теперь ненулевой элемент.
            }
            // выносим элемент m[i][i]
            $tmp = $this->matrix[$i][$i];
            self::simulateIncrementFor($i, function ($i) {
                return $i < $this->columns;
            }, function ($x) use ($i, $tmp) {
                $this->matrix[$i][$x] = $this->matrix[$i][$x] / $tmp;
            });
            // таким образом m[i][i] теперь равен 1
            // обнуляем все элементы стоящие под (i, i)-ым и справа от него,
            // при помощи вычитания с опр. коэффициентом
            $y = $i + 1;
            self::simulateIncrementFor($y, fn($i) => $i < $this->rows, function ($y) use ($i, $x) {
                $tmp = $this->matrix[$y][$i];
                self::simulateIncrementFor($i, fn($x) => $x < $this->columns, function ($x) use ($y, $i, $tmp) {
                    $this->matrix[$y][$x] -= ($this->matrix[$i][$x] * $tmp);
                });
            });

            self::simulateIncrementFor($i + 1, fn($x) => $x < $this->columns, function ($x) use ($i, $tmp) {
                $tmp = $this->matrix[$i][$x];
                self::simulateIncrementFor($i, fn($y) => $y < $this->rows, function ($y) use ($x, $i, $tmp) {
                    $this->matrix[$y][$x] -= ($this->matrix[$y][$i] * $tmp);
                });
            });
        });
    }
}
