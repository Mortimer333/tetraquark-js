<?php declare(strict_types=1);

namespace Tetraquark\Foundation;
use \Tetraquark\{Exception, Block, Log, Validate};

abstract class VariableBlockAbstract extends BlockAbstract
{
    public function recreate(): string
    {
        $script = $this->getSubType() . ' ';

        foreach ($this->getBlocks() as $i => $block) {
            if ($i == 0) {
                $script .= $block->recreate();
            } else {
                $script .= ',' . $block->recreate();
            }
        }

        $scriptLastLetter = $script[\mb_strlen($script) - 1];
        $addSemiColon = [
            ';' => false,
            ',' => false
        ];

        if ($addSemiColon[$scriptLastLetter] ?? true) {
            $script .= ';';
        }
        return $script;
    }

    protected function findVariableEnd(int $start): int
    {
        $end = null;
        $lastLetter = '';
        for ($i=$start + 1; $i < self::$content->getLength(); $i++) {
            $letter = self::$content->getLetter($i);
            if ($letter === ' ') {
                continue;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);
            list($lastLetter, $lastPos) = $this->getPreviousLetter($i - 1, self::$content);

            if ($letter === ';') {
                $end = $i;
                break;
            }

            if ($letter === "\n") {
                if (
                    Validate::isOperator($lastLetter)
                    && !Validate::isComment($lastPos, self::$content)
                ) {
                    continue;
                }

                list($nextLetter, $nextPos) = $this->getNextLetter($i, self::$content);
                if (Validate::isOperator($nextLetter) && !Validate::isComment($nextPos, self::$content)) {
                    continue;
                }

                list($previousWord) = $this->getPreviousWord($i, self::$content);
                if (Validate::isTakenKeyWord($previousWord)) {
                    continue;
                }

                $end = $i;
                break;
            }

        }

        return $end ?? self::$content->getLength();
    }

    protected  function seperateVariableItems(int $start, int $end): array
    {
        $itemStart = $start;
        $items = [];
        for ($i=$start; $i < self::$content->getLength(); $i++) {
            if ($i == $end) {
                break;
            }

            $letter = self::$content->getLetter($i);
            if (Validate::isWhitespace($letter)) {
                continue;
            }

            list($letter, $i) = $this->skipIfNeccessary(self::$content, $letter, $i);

            if ($i >= $end) {
                break;
            }

            if ($letter === ',') {
                $items[] = self::$content->iSubStr($itemStart, $i);
                $itemStart = $i + 1;
                continue;
            }
        }

        $lastItem = self::$content->iSubStr($itemStart, $end);
        if (\mb_strlen($lastItem) > 0) {
            $items[] = $lastItem;
        }

        return $items;
    }

    protected function addVariableItems(array $items): void
    {
        foreach ($items as $item) {
            $item = preg_replace('/[\n]/', ' ', $item);
            $item = preg_replace('/[ \t]+/', ' ', $item) . ';';
            self::$content->setContent($item);
            $this->blocks[] = new Block\VariableItemBlock();
            self::$content->removeContent();
        }
    }
}
