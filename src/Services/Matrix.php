<?php

namespace MatrixTest\Services;

use MatrixTest\Interfaces\MatrixInterface;

class Matrix implements MatrixInterface
{
    protected array $matrix;
    protected int $rows, $columns;

    public function __construct(array $matrix)
    {
        $this->matrix = $matrix;
        $this->rows = self::getRows($matrix);
        $this->columns = self::getColumns($matrix);

    }

    public function getRank(): int
    {
        if (!$this->rows || !$this->columns) {
            // Ранг пустой матрицы равен 0
            return 0;
        }
        // выбираем минимум
        $endi = min($this->rows, $this->columns);
        $x = $y = 0;
        // цикл по всей главной диагонали
        for ($i = 0; $i < $endi; $i++) {
            // если элемент на диагонали равен 0, то ищем не нулевой элемент в матрице
            if ($this->matrix[$i][$i] == 0) {
                // если все элементы нулевые, прерываем цикл
                if (!$this->searchElement(0, false, $y, $x, $i, $i)) break;
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
            for ($x = $i; $x < $this->columns; $x++) {
                $this->matrix[$i][$x] = $this->matrix[$i][$x] / $tmp;
            }
            // таким образом m[i][i] теперь равен 1
            // зануляем все элементы стоящие под (i, i)-ым и справа от него,
            // при помощи вычитания с опр. коеффициентом
            for ($y = $i + 1; $y < $this->rows; $y++) {
                $tmp = $this->matrix[$y][$i];
                for ($x = $i; $x < $this->columns; $x++)
                    $this->matrix[$y][$x] -= ($this->matrix[$i][$x] * $tmp);
            }
            for ($x = $i + 1; $x < $this->columns; $x++) {
                $tmp = $this->matrix[$i][$x];
                for ($y = $i; $y < $this->rows; $y++)
                    $this->matrix[$y][$x] -= ($this->matrix[$y][$i] * $tmp);
            }
        }
        // считаем сколько единичек на главной диагонали
        $cnt = 0;
        for ($i = 0; $i < $endi; $i++)
            if ($this->matrix[$i][$i] == 0) break;
            else $cnt++;
        return $cnt;
    }


    protected static function getRows(array $matrix): int
    {
        return count($matrix);
    }

    protected static function getColumns(array $matrix): int
    {
        return self::getRows($matrix) > 0 ? count($matrix[0]) : 0;
    }

    /**
     * @param $what
     * @param bool $match
     * @param int $uI
     * @param int $uJ
     * @param int $starti
     * @param int $startj
     * @return int
     *  поиск элемента с указанным значением
     * возвращаеются его координаты если он есть.
     * match - искать равный элемент или отличный от указанного
     * результат функции == 0 - не найнен, <> 0 - найден
     */
    protected function searchElement($what, bool $match, int &$uI, int &$uJ, int $starti, int $startj): int
    {
        if ((!$this->rows) || (!$this->columns)) return 0;
        if (($starti >= $this->rows) || ($startj >= $this->columns)) return 0;
        for ($i = $starti; $i < $this->rows; $i++) {
            for ($j = $startj; $j < $this->columns; $j++) {
                if ($match) {
                    if ($this->matrix[$i][$j] == $what) {
                        $uI = $i;
                        $uJ = $j;
                        return 1;
                    }
                } else
                    if ($this->matrix[$i][$j] != $what) {
                        $uI = $i;
                        $uJ = $j;
                        return 1;
                    }
            }
        }
        return 0;
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
        for ($x = 0; $x < $height; $x++) {
            $tmp = $matrix[$x][$x1];
            $matrix[$x][$x1] = $matrix[$x][$x2];
            $matrix[$x][$x2] = $tmp;
        }
        return $matrix;
    }

    protected static function simulateForeach(callable $startCond, callable $checkCond, callable $processCond,
                                              callable $func)
    {
        $startCond();
        while (true) {
            if ($checkCond) {
                $func();
            } else {
                return;
            }
            $processCond();
        }
    }

}
